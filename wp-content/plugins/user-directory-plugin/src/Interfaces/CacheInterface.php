<?php

declare(strict_types=1);

namespace UsersPlugin\UserDirectory\Interfaces;

interface CacheInterface
{
    public function get(string $key): mixed;

    public function set(string $key, mixed $value, int $ttl): void;

    public function delete(string $key): void;
}
