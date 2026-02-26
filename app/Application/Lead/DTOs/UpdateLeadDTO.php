<?php

declare(strict_types=1);

namespace App\Application\Lead\DTOs;

final readonly class UpdateLeadDTO
{
    public function __construct(
        public ?string $name = null,
        public ?string $email = null,
        public ?string $source = null,
        public ?string $status = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
            email: $data['email'] ?? null,
            source: $data['source'] ?? null,
            status: $data['status'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'email' => $this->email,
            'source' => $this->source,
            'status' => $this->status,
        ], fn ($value) => $value !== null);
    }
}
