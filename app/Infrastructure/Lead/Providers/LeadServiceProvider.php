<?php

declare(strict_types=1);

namespace App\Infrastructure\Lead\Providers;

use App\Domain\Lead\Repositories\LeadRepositoryInterface;
use App\Infrastructure\Lead\Repositories\EloquentLeadRepository;
use Illuminate\Support\ServiceProvider;

final class LeadServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            LeadRepositoryInterface::class,
            EloquentLeadRepository::class,
        );
    }
}
