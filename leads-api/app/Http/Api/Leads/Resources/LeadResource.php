<?php

declare(strict_types=1);

namespace App\Http\Api\Leads\Resources;

use App\Domain\Lead\Lead;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Lead
 */
final class LeadResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name->value,
            'email' => $this->email->value,
            'source' => $this->source->value,
            'status' => $this->status->value,
            'created_at' => $this->createdAt?->format('c'),
            'updated_at' => $this->updatedAt?->format('c'),
        ];
    }
}
