<?php

declare(strict_types=1);

namespace App\Http\Api\Leads\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class SearchLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'q' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'q.required' => 'The search query is required.',
        ];
    }
}

