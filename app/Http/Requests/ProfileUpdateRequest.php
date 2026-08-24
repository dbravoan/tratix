<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'tax_id' => ['nullable', 'string', 'max:32'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'party_type' => ['nullable', 'string', 'in:particular,empresa'],
            'phone' => ['nullable', 'string', 'max:32'],
            'address' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:16'],
            'city' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:8'],
            'notify_comments' => ['nullable', 'boolean'],
            'notify_proposals' => ['nullable', 'boolean'],
            'notify_signatures' => ['nullable', 'boolean'],
            'notify_summary' => ['nullable', 'boolean'],
        ];
    }
}
