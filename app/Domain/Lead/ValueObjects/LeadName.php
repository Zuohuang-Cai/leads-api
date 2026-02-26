<?php

declare(strict_types=1);

namespace App\Domain\Lead\ValueObjects;

use App\Domain\Lead\Exceptions\InvalidLeadNameException;

final readonly class LeadName
{
    private const int MIN_LENGTH = 2;
    private const int MAX_LENGTH = 255;

    public string $value;

    public function __construct(string $value)
    {
        $trimmed = trim($value);

        if (mb_strlen($trimmed) < self::MIN_LENGTH) {
            throw InvalidLeadNameException::tooShort(self::MIN_LENGTH);
        }

        if (mb_strlen($trimmed) > self::MAX_LENGTH) {
            throw InvalidLeadNameException::tooLong(self::MAX_LENGTH);
        }

        $this->value = $trimmed;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
