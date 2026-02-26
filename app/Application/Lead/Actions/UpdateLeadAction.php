<?php

declare(strict_types=1);

namespace App\Application\Lead\Actions;

use App\Application\Lead\DTOs\UpdateLeadDTO;
use App\Domain\Lead\Events\LeadUpdated;
use App\Domain\Lead\Lead;
use App\Domain\Lead\Repositories\LeadRepositoryInterface;

final readonly class UpdateLeadAction
{
    public function __construct(
        private LeadRepositoryInterface $repository,
    ) {}

    public function execute(int $id, UpdateLeadDTO $dto): Lead
    {
        $existing = $this->repository->findById($id);

        $updated = $existing->update($dto->name, $dto->email, $dto->source, $dto->status);

        $persisted = $this->repository->update($updated);
        return $persisted;
    }
}
