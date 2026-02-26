<?php

declare(strict_types=1);

namespace App\Domain\User\Repositories;

use App\Domain\User\User;

interface UserRepositoryInterface
{
    public function findById(int $id): User;

    public function findByEmail(string $email): ?User;

    public function create(User $user): User;

}

