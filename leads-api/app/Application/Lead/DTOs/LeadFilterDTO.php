<?php

declare(strict_types=1);

namespace App\Application\Lead\DTOs;

final readonly class LeadFilterDTO
{
    public function __construct(
        public ?string $search = null,
        public ?string $status = null,
        public string $sortDirection = 'desc',
        public int $perPage = 10,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            search: $data['search'] ?? null,
            status: $data['status'] ?? null,
            sortDirection: $data['sort'] ?? 'desc',
            perPage: (int) ($data['per_page'] ?? 10),
        );
    }
}
