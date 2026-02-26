<?php

declare(strict_types=1);

namespace App\Infrastructure\Lead\Repositories;

use App\Application\Lead\DTOs\LeadFilterDTO;
use App\Application\Lead\Pipelines\Filters\FilterByStatus;
use App\Application\Lead\Pipelines\Filters\SearchByNameOrEmail;
use App\Application\Lead\Pipelines\Filters\SortByDate;
use App\Domain\Lead\Exceptions\LeadNotFoundException;
use App\Domain\Lead\Lead;
use App\Domain\Lead\Repositories\LeadRepositoryInterface;
use App\Infrastructure\Lead\Models\LeadEloquentModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pipeline\Pipeline;

final readonly class EloquentLeadRepository implements LeadRepositoryInterface
{
    public function __construct(
        private Pipeline $pipeline,
    ) {}

    public function all(array $filters): LengthAwarePaginator
    {
        $filterDTO = LeadFilterDTO::fromArray($filters);

        $query = LeadEloquentModel::query();

        $filteredQuery = $this->pipeline
            ->send($query)
            ->through([
                new SearchByNameOrEmail($filterDTO->search),
                new FilterByStatus($filterDTO->status),
                new SortByDate($filterDTO->sortDirection),
            ])
            ->thenReturn();

        $paginator = $filteredQuery->paginate($filterDTO->perPage);

        $paginator->through(fn (LeadEloquentModel $model) => $this->toDomain($model));

        return $paginator;
    }

    public function findById(int $id): Lead
    {
        $model = LeadEloquentModel::find($id);

        if ($model === null) {
            throw LeadNotFoundException::withId($id);
        }

        return $this->toDomain($model);
    }

    public function findByNameOrEmail(string $query): ?Lead
    {
        $model = LeadEloquentModel::where('name', $query)
            ->orWhere('email', $query)
            ->first();

        if ($model === null) {
            return null;
        }

        return $this->toDomain($model);
    }

    public function create(Lead $lead): Lead
    {
        $model = LeadEloquentModel::create($lead->toArray());

        return $this->toDomain($model);
    }

    public function update(Lead $lead): Lead
    {
        $model = LeadEloquentModel::find($lead->id);

        if ($model === null) {
            throw LeadNotFoundException::withId($lead->id);
        }

        $model->update($lead->toArray());

        return $this->toDomain($model->refresh());
    }

    public function delete(int $id): bool
    {
        $model = LeadEloquentModel::find($id);

        if ($model === null) {
            throw LeadNotFoundException::withId($id);
        }

        return (bool) $model->delete();
    }



    private function toDomain(LeadEloquentModel $model): Lead
    {
        return Lead::fromPersistence(
            id: $model->id,
            name: $model->name,
            email: $model->email,
            source: $model->source->value,
            status: $model->status->value,
            createdAt: new \DateTimeImmutable($model->created_at->toDateTimeString()),
            updatedAt: new \DateTimeImmutable($model->updated_at->toDateTimeString()),
        );
    }
}
