<?php

declare(strict_types=1);

namespace UsersPlugin\UserDirectory\Services;

use Psr\Log\LoggerInterface;
use UsersPlugin\UserDirectory\Interfaces\ApiServiceInterface;
use UsersPlugin\UserDirectory\Interfaces\CacheInterface;

final class ApiService implements ApiServiceInterface
{
    public function __construct(
        private CacheInterface $cache,
        private LoggerInterface $logger
    ) {
    }

    /**
     * Fetches and caches all users.
     *
     * @return array<int, array<string, mixed>> List of user data.
     */
    public function users(): array
    {
        $cacheKey = 'user_directory_users';

        $users = $this->cache->get($cacheKey);
        if (is_array($users)) {
            return apply_filters('user_directory_users', $users);
        }

        $response = wp_remote_get('https://jsonplaceholder.typicode.com/users');

        if (is_wp_error($response)) {
            $this->logger->warning(
                '[UserDirectory] Failed to fetch users: ' . $response->get_error_message()
            );
            return [];
        }

        $users = json_decode(wp_remote_retrieve_body($response), true);

        if (!is_array($users) || $users === []) {
            $this->logger->info('[UserDirectory] Empty users list, deleting cache.');
            $this->cache->delete($cacheKey);
            return [];
        }

        $this->cache->set($cacheKey, $users, 3600);

        return apply_filters('user_directory_users', $users);
    }

    /**
     * Fetches and caches a single user by ID.
     *
     * @param int $id User ID.
     * @return array<string, mixed> User data or empty array if not found.
     */
    public function user(int $id): array
    {
        $ids = array_column($this->users(), 'id');

        if (!in_array($id, $ids, true)) {
            $this->logger->notice(
                "[UserDirectory] Invalid user ID: {$id}"
            );
            return [];
        }

        $cacheKey = "user_directory_user_{$id}";

        $user = $this->cache->get($cacheKey);
        if (is_array($user)) {
            return $user;
        }

        $response = wp_remote_get(
            "https://jsonplaceholder.typicode.com/users/{$id}"
        );

        if (is_wp_error($response)) {
            $this->logger->error(
                "[UserDirectory] Failed to fetch user {$id}: " .
                $response->get_error_message()
            );
            return [];
        }

        $user = json_decode(
            wp_remote_retrieve_body($response),
            true
        );

        if (!is_array($user)) {
            $this->logger->warning(
                "[UserDirectory] Invalid data for user {$id}"
            );
            return [];
        }

        $this->cache->set($cacheKey, $user, 3600);
        return $user;
    }
}
