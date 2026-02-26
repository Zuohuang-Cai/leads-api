<?php

declare(strict_types=1);

namespace App\Application\Lead\DTOs;

final readonly class CreateLeadDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public string $source,
        public string $status,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            email: $data['email'],
            source: $data['source'],
            status: $data['status'],
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'source' => $this->source,
            'status' => $this->status,
        ];
    }
}
