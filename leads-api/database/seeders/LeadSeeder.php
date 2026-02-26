<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Lead\ValueObjects\LeadSource;
use App\Domain\Lead\ValueObjects\LeadStatus;
use App\Infrastructure\Lead\Models\LeadEloquentModel;
use Illuminate\Database\Seeder;

final class LeadSeeder extends Seeder
{
    public function run(int $count = 100): void
    {
        LeadEloquentModel::factory()
            ->count($count)
            ->create();
    }
}
