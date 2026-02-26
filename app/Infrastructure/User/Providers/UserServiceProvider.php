<?php

declare(strict_types=1);

namespace App\Infrastructure\User\Providers;

use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\User\Services\EmailVerificationServiceInterface;
use App\Infrastructure\User\Repositories\EloquentUserRepository;
use App\Infrastructure\User\Services\EmailVerificationService;
use App\Infrastructure\User\Services\FakeEmailVerificationService;
use Illuminate\Support\ServiceProvider;

final class UserServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            UserRepositoryInterface::class,
            EloquentUserRepository::class,
        );

        $this->app->bind(
            EmailVerificationServiceInterface::class,
            FakeEmailVerificationService::class,
        );
    }
}

