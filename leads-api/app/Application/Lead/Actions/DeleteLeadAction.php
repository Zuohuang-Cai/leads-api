<?php

declare(strict_types=1);

namespace App\Application\Lead\Actions;

use App\Domain\Lead\Events\LeadDeleted;
use App\Domain\Lead\Repositories\LeadRepositoryInterface;

final readonly class DeleteLeadAction
{
    public function __construct(
        private LeadRepositoryInterface $repository,
    ) {}

    public function execute(int $id): bool
    {
        // Ensure the lead exists (throws LeadNotFoundException if not)
        $this->repository->findById($id);

        $deleted = $this->repository->delete($id);

        LeadDeleted::dispatch($id);

        return $deleted;
    }
}
