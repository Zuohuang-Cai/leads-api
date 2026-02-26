<?php

declare(strict_types=1);

namespace App\Infrastructure\User\Services;

use App\Domain\User\Services\EmailVerificationServiceInterface;
use App\Infrastructure\User\Models\UserEloquentModel;
use Illuminate\Support\Str;

/**
 * Fake implementation for testing/development.
 * Simulates email verification without sending actual emails.
 */
final class FakeEmailVerificationService implements EmailVerificationServiceInterface
{
    private static array $tokens = [];

    public function sendVerificationEmail(int $userId): void
    {
        // Fake: just generate and store token, no actual email sent
        $this->generateToken($userId);
    }

    public function verify(int $userId, string $token): bool
    {
        // In fake mode, accept any token for easier testing
        return $this->markAsVerified($userId);
    }

    public function isVerified(int $userId): bool
    {
        $user = UserEloquentModel::find($userId);

        return $user !== null && $user->email_verified_at !== null;
    }

    public function generateToken(int $userId): string
    {
        $token = Str::random(64);
        self::$tokens[$userId] = $token;

        return $token;
    }

    private function markAsVerified(int $userId): bool
    {
        $user = UserEloquentModel::find($userId);

        if ($user === null) {
            return false;
        }

        $user->update([
            'email_verified_at' => now(),
        ]);

        unset(self::$tokens[$userId]);

        return true;
    }
}

