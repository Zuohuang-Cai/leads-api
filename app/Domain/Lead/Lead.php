<?php

declare(strict_types=1);

namespace App\Domain\Lead;

use App\Domain\Lead\ValueObjects\Email;
use App\Domain\Lead\ValueObjects\LeadName;
use App\Domain\Lead\ValueObjects\LeadSource;
use App\Domain\Lead\ValueObjects\LeadStatus;
use DateTimeImmutable;

final readonly class Lead
{
    public function __construct(
        public LeadName $name,
        public Email $email,
        public LeadSource $source,
        public LeadStatus $status,
        public ?int $id = null,
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $updatedAt = null,
    ) {}

    /**
     * Factory for creating a brand-new Lead (all fields required).
     */
    public static function create(
        string $name,
        string $email,
        string $source,
        string $status,
    ): self {
        return new self(
            name: new LeadName($name),
            email: new Email($email),
            source: LeadSource::from($source),
            status: LeadStatus::from($status),
        );
    }

    /**
     * Reconstitute a Lead from persistence (no validation, data is already trusted).
     */
    public static function fromPersistence(
        int $id,
        string $name,
        string $email,
        string $source,
        string $status,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt,
    ): self {
        return new self(
            name: new LeadName($name),
            email: new Email($email),
            source: LeadSource::from($source),
            status: LeadStatus::from($status),
            id: $id,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
        );
    }

    /**
     * Apply partial updates. Only non-null fields are overwritten.
     * Returns a new Lead instance with validated, merged values.
     */
    public function update(
        ?string $name = null,
        ?string $email = null,
        ?string $source = null,
        ?string $status = null,
    ): self {
        return new self(
            name: $name !== null ? new LeadName($name) : $this->name,
            email: $email !== null ? new Email($email) : $this->email,
            source: $source !== null ? LeadSource::from($source) : $this->source,
            status: $status !== null ? LeadStatus::from($status) : $this->status,
            id: $this->id,
            createdAt: $this->createdAt,
            updatedAt: $this->updatedAt,
        );
    }

    /**
     * Convert the aggregate to a plain array (useful for persistence).
     *
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name->value,
            'email' => $this->email->value,
            'source' => $this->source->value,
            'status' => $this->status->value,
        ];
    }
}

