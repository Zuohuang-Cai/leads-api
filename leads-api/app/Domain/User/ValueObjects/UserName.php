<?php

declare(strict_types=1);

namespace App\Domain\User\ValueObjects;

use InvalidArgumentException;

final readonly class UserName
{
    public string $value;

    public function __construct(string $value)
    {
        $trimmed = trim($value);

        if (mb_strlen($trimmed) < 2) {
            throw new InvalidArgumentException('User name must be at least 2 characters.');
        }

        if (mb_strlen($trimmed) > 255) {
            throw new InvalidArgumentException('User name must not exceed 255 characters.');
        }

        $this->value = $trimmed;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}

