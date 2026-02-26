<?php

declare(strict_types=1);

namespace App\Http\Api\Leads\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

final class LeadCollection extends ResourceCollection
{
    public $collects = LeadResource::class;

    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
        ];
    }
}
