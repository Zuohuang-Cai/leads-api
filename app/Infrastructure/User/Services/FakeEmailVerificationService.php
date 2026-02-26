<?php

declare(strict_types=1);

namespace App\Infrastructure\User\Services;

use App\Domain\User\Services\EmailVerificationServiceInterface;
use App\Infrastructure\User\Models\UserEloquentModel;
use Illuminate\Support\Str;

final class FakeEmailVerificationService implements EmailVerificationServiceInterface
{

    public function sendVerificationEmail(int $userId): void
    {
        echo "email send verification: $userId\n";
    }

    public function verify(int $userId, string $token): void
    {
        echo "email verify: $userId, token: $token\n";
    }


}

