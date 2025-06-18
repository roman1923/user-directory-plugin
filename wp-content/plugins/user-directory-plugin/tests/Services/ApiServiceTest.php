<?php

declare(strict_types=1);

namespace UsersPlugin\UserDirectory\Tests\Services;

use PHPUnit\Framework\TestCase;
use Mockery;
use UsersPlugin\UserDirectory\Services\ApiService;
use UsersPlugin\UserDirectory\Interfaces\CacheInterface;
use Psr\Log\LoggerInterface;

class ApiServiceTest extends TestCase
{
    private CacheInterface $cache;
    private LoggerInterface $logger;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cache = Mockery::mock(CacheInterface::class);
        $this->logger = Mockery::mock(LoggerInterface::class);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testUsersFetchesAndCachesData(): void
    {
        $expectedUsers = [['id' => 1, 'name' => 'Test User']];

        $this->cache->shouldReceive('get')
            ->once()
            ->with('user_directory_users')
            ->andReturn(null);

        $this->cache->shouldReceive('set')
            ->once()
            ->with('user_directory_users', $expectedUsers, 3600);

        $this->logger->shouldIgnoreMissing();

        \Brain\Monkey\Functions\when('wp_remote_get')
            ->justReturn(['body' => json_encode($expectedUsers)]);

        \Brain\Monkey\Functions\when('wp_remote_retrieve_body')
            ->alias(static fn(array $responseBody): string => $responseBody['body']);

        \Brain\Monkey\Functions\when('is_wp_error')
            ->justReturn(false);

        $service = new ApiService($this->cache, $this->logger);
        $result = $service->users();

        $this->assertEquals($expectedUsers, $result);
    }

    public function testUsersDoesNotCacheEmptyArray(): void
    {
        $this->cache->shouldReceive('get')
            ->once()
            ->with('user_directory_users')
            ->andReturn(null);

        $this->cache->shouldReceive('delete')
            ->once()
            ->with('user_directory_users');

        $this->logger->shouldIgnoreMissing();

        \Brain\Monkey\Functions\when('wp_remote_get')
            ->justReturn(['body' => '[]']);

        \Brain\Monkey\Functions\when('wp_remote_retrieve_body')
            ->alias(static fn(array $responseBody): string => $responseBody['body']);

        \Brain\Monkey\Functions\when('is_wp_error')
            ->justReturn(false);

        $service = new ApiService($this->cache, $this->logger);
        $result = $service->users();

        $this->assertEquals([], $result);
    }

    public function testUserReturnsCachedDataIfExists(): void
    {
        $user = ['id' => 1, 'name' => 'Cached User'];

        $this->cache->shouldReceive('get')
            ->with('user_directory_users')
            ->andReturn([['id' => 1]]);

        $this->cache->shouldReceive('get')
            ->with('user_directory_user_1')
            ->andReturn($user);

        $this->logger->shouldIgnoreMissing();

        $service = new ApiService($this->cache, $this->logger);
        $result = $service->user(1);

        $this->assertEquals($user, $result);
    }

    public function testUserFetchesAndCachesWhenNotInCache(): void
    {
        $user = ['id' => 1, 'email' => 'test@example.com'];

        $this->cache->shouldReceive('get')
            ->with('user_directory_users')
            ->andReturn([['id' => 1]]);

        $this->cache->shouldReceive('get')
            ->with('user_directory_user_1')
            ->andReturn(null);

        $this->cache->shouldReceive('set')
            ->once()
            ->with('user_directory_user_1', $user, 3600);

        $this->logger->shouldIgnoreMissing();

        \Brain\Monkey\Functions\when('wp_remote_get')
            ->alias(static function (string $url): array {
                return ['body' => json_encode(['id' => 1, 'email' => 'test@example.com'])];
            });

        \Brain\Monkey\Functions\when('wp_remote_retrieve_body')
            ->alias(static fn(array $responseBody): string => $responseBody['body']);

        \Brain\Monkey\Functions\when('is_wp_error')
            ->justReturn(false);

        $service = new ApiService($this->cache, $this->logger);
        $result = $service->user(1);

        $this->assertEquals($user, $result);
    }
}
