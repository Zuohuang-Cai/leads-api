<?php

declare(strict_types=1);

namespace App\Domain\Lead\Exceptions;

use InvalidArgumentException;

final class InvalidEmailException extends InvalidArgumentException
{
    public static function invalid(string $email): self
    {
        return new self("'{$email}' is geen geldig e-mailadres.");
    }
}
