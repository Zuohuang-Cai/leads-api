<?php

declare(strict_types=1);

namespace App\Domain\User\Listeners;

use App\Domain\User\Events\UserCreated;
use App\Domain\User\Services\EmailVerificationServiceInterface;

final readonly class SendVerificationEmailListener
{
    public function __construct(
        private EmailVerificationServiceInterface $emailVerificationService,
    ) {}

    public function handle(UserCreated $event): void
    {
        $this->emailVerificationService->sendVerificationEmail($event->userId);
    }
}

