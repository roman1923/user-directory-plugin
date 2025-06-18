<?php

declare(strict_types=1);

namespace UsersPlugin\UserDirectory\Services;

use Stash\Pool;
use Stash\Driver\Redis;
use Stash\Driver\FileSystem;
use UsersPlugin\UserDirectory\Interfaces\CacheInterface;

final class CacheService implements CacheInterface
{
    /**
     * @var Pool
     */
    private $pool;

    public function __construct()
    {
        try {
            $driver = new Redis([
                'servers' => [['server' => '127.0.0.1', 'port' => 6379]],
                'ttl'     => 3600,
            ]);
        } catch (\Throwable $exception) {
			$driver = new FileSystem([
				'path' => WP_CONTENT_DIR . '/cache/user-directory',
			]);
		}

        $this->pool = new Pool($driver);
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    public function get(string $key): mixed
    {
        $item = $this->pool->getItem($key);
        return $item->isHit() ? $item->get() : null;
    }

    /**
     * @param string $key
     * @param mixed  $value
     * @param int    $ttl
     * @return void
     */
    public function set(string $key, mixed $value, int $ttl = 3600): void
    {
        $item = $this->pool->getItem($key);
        $item->set($value)->expiresAfter($ttl);
        $this->pool->save($item);
    }

    /**
     * @param string $key
     * @return void
     */
    public function delete(string $key): void
    {
        $this->pool->deleteItem($key);
    }
}
