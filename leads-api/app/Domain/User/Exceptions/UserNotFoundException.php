<?php

declare(strict_types=1);

namespace App\Domain\User\Exceptions;

use Exception;

final class UserNotFoundException extends Exception
{
    public static function withId(int $id): self
    {
        return new self("User with ID {$id} not found.");
    }

    public static function withEmail(string $email): self
    {
        return new self("User with email {$email} not found.");
    }
}

