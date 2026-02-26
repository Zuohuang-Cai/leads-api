<?php

declare(strict_types=1);

namespace App\Domain\User\ValueObjects;

use InvalidArgumentException;

final readonly class HashedPassword
{
    public string $value;

    private function __construct(string $hashedValue)
    {
        $this->value = $hashedValue;
    }

    public static function fromPlainText(string $plainPassword): self
    {
        if (mb_strlen($plainPassword) < 8) {
            throw new InvalidArgumentException('Password must be at least 8 characters.');
        }

        return new self(password_hash($plainPassword, PASSWORD_DEFAULT));
    }

    public static function fromHash(string $hashedPassword): self
    {
        return new self($hashedPassword);
    }

    public function verify(string $plainPassword): bool
    {
        return password_verify($plainPassword, $this->value);
    }

    public function __toString(): string
    {
        return $this->value;
    }
}

