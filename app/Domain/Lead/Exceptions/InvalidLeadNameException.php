<?php

declare(strict_types=1);

namespace App\Domain\Lead\Exceptions;

use InvalidArgumentException;

final class InvalidLeadNameException extends InvalidArgumentException
{
    public static function tooShort(int $minLength): self
    {
        return new self("Naam moet minimaal {$minLength} karakters bevatten.");
    }

    public static function tooLong(int $maxLength): self
    {
        return new self("Naam mag maximaal {$maxLength} karakters bevatten.");
    }
}
