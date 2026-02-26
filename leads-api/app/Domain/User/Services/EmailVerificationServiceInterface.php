<?php

declare(strict_types=1);

namespace App\Domain\User\Services;

interface EmailVerificationServiceInterface
{
    public function sendVerificationEmail(int $userId): void;

    public function verify(int $userId, string $token): void;
}

