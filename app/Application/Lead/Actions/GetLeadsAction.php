<?php

declare(strict_types=1);

namespace App\Application\Lead\Actions;

use App\Domain\Lead\Repositories\LeadRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final readonly class GetLeadsAction
{
    public function __construct(
        private LeadRepositoryInterface $repository,
    ) {}

    public function execute(array $filters): LengthAwarePaginator
    {
        return $this->repository->all($filters);
    }
}
