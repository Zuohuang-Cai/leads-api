<?php

declare(strict_types=1);

namespace App\Application\Lead\Actions;

use App\Application\Lead\DTOs\CreateLeadDTO;
use App\Domain\Lead\Events\LeadCreated;
use App\Domain\Lead\Lead;
use App\Domain\Lead\Repositories\LeadRepositoryInterface;

final readonly class CreateLeadAction
{
    public function __construct(
        private LeadRepositoryInterface $repository,
    ) {}

    public function execute(CreateLeadDTO $dto): Lead
    {
        $lead = Lead::create($dto->name, $dto->email, $dto->source, $dto->status);

        $persisted = $this->repository->create($lead);
        return $persisted;

    }
}
