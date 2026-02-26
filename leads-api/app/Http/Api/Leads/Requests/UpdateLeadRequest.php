<?php

declare(strict_types=1);

namespace App\Http\Api\Leads\Requests;

use App\Domain\Lead\ValueObjects\LeadSource;
use App\Domain\Lead\ValueObjects\LeadStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'min:2', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255'],
            'source' => ['sometimes', 'string', Rule::in(LeadSource::values())],
            'status' => ['sometimes', 'string', Rule::in(LeadStatus::values())],
        ];
    }

    public function messages(): array
    {
        return [
            'name.min' => 'Naam moet minimaal :min karakters bevatten.',
            'email.email' => 'Voer een geldig e-mailadres in.',
            'source.in' => 'Ongeldige bron. Kies uit: ' . implode(', ', LeadSource::values()),
            'status.in' => 'Ongeldige status. Kies uit: ' . implode(', ', LeadStatus::values()),
        ];
    }
}
