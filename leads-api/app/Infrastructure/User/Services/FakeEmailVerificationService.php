<?php

declare(strict_types=1);

namespace App\Infrastructure\User\Services;

use App\Domain\User\Services\EmailVerificationServiceInterface;

final class FakeEmailVerificationService implements EmailVerificationServiceInterface
{

    public function sendVerificationEmail(int $userId): void
    {
        echo "// Sending verification email to user ID: $userId\n";
    }

    public function verify(int $userId, string $token): void
    {
        echo "// Verifying user ID: $userId with token: $token\n";
    }
}

