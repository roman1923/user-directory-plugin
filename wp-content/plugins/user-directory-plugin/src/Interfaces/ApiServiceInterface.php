<?php

declare(strict_types=1);

namespace UsersPlugin\UserDirectory\Interfaces;

interface ApiServiceInterface
{
    public function users(): array;

    public function user(int $id): array;
}
