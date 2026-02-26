<?php

declare(strict_types=1);

namespace App\Http\Api\Leads\Requests;

use App\Domain\Lead\ValueObjects\LeadSource;
use App\Domain\Lead\ValueObjects\LeadStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'source' => ['required', 'string', Rule::in(LeadSource::values())],
            'status' => ['required', 'string', Rule::in(LeadStatus::values())],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Naam is verplicht.',
            'name.min' => 'Naam moet minimaal :min karakters bevatten.',
            'email.required' => 'E-mailadres is verplicht.',
            'email.email' => 'Voer een geldig e-mailadres in.',
            'source.required' => 'Bron is verplicht.',
            'source.in' => 'Ongeldige bron. Kies uit: ' . implode(', ', LeadSource::values()),
            'status.required' => 'Status is verplicht.',
            'status.in' => 'Ongeldige status. Kies uit: ' . implode(', ', LeadStatus::values()),
        ];
    }
}
