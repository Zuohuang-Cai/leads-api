<?php

declare(strict_types=1);

namespace App\Domain\Lead\Repositories;

use App\Domain\Lead\Lead;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface LeadRepositoryInterface
{
    public function all(array $filters): LengthAwarePaginator;

    public function findById(int $id): Lead;

    public function findByNameOrEmail(string $query): ?Lead;

    public function create(Lead $lead): Lead;

    public function update(Lead $lead): Lead;

    public function delete(int $id): bool;
}
