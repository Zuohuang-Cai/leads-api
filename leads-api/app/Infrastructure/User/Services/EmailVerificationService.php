<?php

declare(strict_types=1);

namespace App\Infrastructure\User\Services;

use App\Domain\User\Services\EmailVerificationServiceInterface;
use App\Infrastructure\User\Models\UserEloquentModel;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Real implementation that sends actual verification emails.
 */
final class EmailVerificationService implements EmailVerificationServiceInterface
{
    private const TOKEN_TTL_MINUTES = 60;

    public function sendVerificationEmail(int $userId): void
    {
        $user = UserEloquentModel::findOrFail($userId);
        $token = $this->generateToken($userId);

        // TODO: Create a Mailable class for better email formatting
        Mail::raw(
            "Click here to verify your email: " . url("/api/auth/verify-email?token={$token}&user_id={$userId}"),
            fn ($message) => $message
                ->to($user->email)
                ->subject('Verify your email address')
        );
    }

    public function verify(int $userId, string $token): bool
    {
        $storedToken = Cache::get($this->getCacheKey($userId));

        if ($storedToken === null || $storedToken !== $token) {
            return false;
        }

        $user = UserEloquentModel::find($userId);

        if ($user === null) {
            return false;
        }

        $user->update([
            'email_verified_at' => now(),
        ]);

        Cache::forget($this->getCacheKey($userId));

        return true;
    }

    public function isVerified(int $userId): bool
    {
        $user = UserEloquentModel::find($userId);

        return $user !== null && $user->email_verified_at !== null;
    }

    public function generateToken(int $userId): string
    {
        $token = Str::random(64);

        Cache::put(
            $this->getCacheKey($userId),
            $token,
            now()->addMinutes(self::TOKEN_TTL_MINUTES)
        );

        return $token;
    }

    private function getCacheKey(int $userId): string
    {
        return "email_verification_token:{$userId}";
    }
}

