<?php

declare(strict_types=1);

namespace App\Domain\User;

use App\Domain\Shared\ValueObjects\Email;
use App\Domain\User\ValueObjects\HashedPassword;
use App\Domain\User\ValueObjects\UserName;
use DateTimeImmutable;

final readonly class User
{
    public function __construct(
        public UserName $name,
        public Email $email,
        public HashedPassword $password,
        public ?int $id = null,
        public ?DateTimeImmutable $emailVerifiedAt = null,
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $updatedAt = null,
    ) {}

    /**
     * Factory for creating a brand-new User.
     */
    public static function create(
        string $name,
        string $email,
        string $password,
    ): self {
        return new self(
            name: new UserName($name),
            email: new Email($email),
            password: HashedPassword::fromPlainText($password),
        );
    }

    /**
     * Reconstitute a User from persistence.
     */
    public static function fromPersistence(
        int $id,
        string $name,
        string $email,
        string $hashedPassword,
        ?DateTimeImmutable $emailVerifiedAt,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt,
    ): self {
        return new self(
            name: new UserName($name),
            email: new Email($email),
            password: HashedPassword::fromHash($hashedPassword),
            id: $id,
            emailVerifiedAt: $emailVerifiedAt,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
        );
    }

    /**
     * Convert to array for persistence.
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name->value,
            'email' => $this->email->value,
            'password' => $this->password->value,
        ];
    }

    /**
     * Verify password.
     */
    public function verifyPassword(string $plainPassword): bool
    {
        return $this->password->verify($plainPassword);
    }

    /**
     * Check if email is verified.
     */
    public function isEmailVerified(): bool
    {
        return $this->emailVerifiedAt !== null;
    }
}

