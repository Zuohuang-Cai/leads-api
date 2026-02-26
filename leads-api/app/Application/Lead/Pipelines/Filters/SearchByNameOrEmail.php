<?php

declare(strict_types=1);

namespace App\Application\Lead\Pipelines\Filters;

use Closure;
use Illuminate\Database\Eloquent\Builder;

final readonly class SearchByNameOrEmail
{
    public function __construct(
        private ?string $search,
    ) {}

    public function handle(Builder $query, Closure $next): Builder
    {
        if ($this->search !== null && $this->search !== '') {
            $term = '%' . $this->search . '%';

            $query->where(function (Builder $q) use ($term): void {
                $q->where('name', 'LIKE', $term)
                  ->orWhere('email', 'LIKE', $term);
            });
        }

        return $next($query);
    }
}
