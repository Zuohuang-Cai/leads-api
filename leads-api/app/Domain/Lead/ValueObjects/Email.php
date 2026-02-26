<?php

declare(strict_types=1);

namespace App\Domain\Lead\ValueObjects;

use App\Domain\Lead\Exceptions\InvalidEmailException;

final readonly class Email
{
    public string $value;

    public function __construct(string $value)
    {
        $normalized = mb_strtolower(trim($value));

        if (!filter_var($normalized, FILTER_VALIDATE_EMAIL)) {
            throw InvalidEmailException::invalid($value);
        }

        $this->value = $normalized;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
