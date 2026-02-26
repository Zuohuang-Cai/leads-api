<?php

declare(strict_types=1);

namespace App\Infrastructure\User\Repositories;

use App\Domain\User\Exceptions\UserNotFoundException;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\User\User;
use App\Infrastructure\User\Models\UserEloquentModel;
use DateTimeImmutable;

final readonly class EloquentUserRepository implements UserRepositoryInterface
{
    public function findById(int $id): User
    {
        $model = UserEloquentModel::find($id);

        if ($model === null) {
            throw UserNotFoundException::withId($id);
        }

        return $this->toDomain($model);
    }

    public function findByEmail(string $email): ?User
    {
        $model = UserEloquentModel::where('email', $email)->first();

        if ($model === null) {
            return null;
        }

        return $this->toDomain($model);
    }

    public function create(User $user): User
    {
        $model = UserEloquentModel::create($user->toArray());

        return $this->toDomain($model);
    }


    /**
     * Get the Eloquent model by email (for Sanctum token creation).
     */
    public function findEloquentByEmail(string $email): ?UserEloquentModel
    {
        return UserEloquentModel::where('email', $email)->first();
    }

    /**
     * Get the Eloquent model by ID (for Sanctum token creation).
     */
    public function findEloquentById(int $id): ?UserEloquentModel
    {
        return UserEloquentModel::find($id);
    }

    private function toDomain(UserEloquentModel $model): User
    {
        return User::fromPersistence(
            id: $model->id,
            name: $model->name,
            email: $model->email,
            hashedPassword: $model->password,
            emailVerifiedAt: $model->email_verified_at
                ? new DateTimeImmutable($model->email_verified_at->toDateTimeString())
                : null,
            createdAt: new DateTimeImmutable($model->created_at->toDateTimeString()),
            updatedAt: new DateTimeImmutable($model->updated_at->toDateTimeString()),
        );
    }
}

