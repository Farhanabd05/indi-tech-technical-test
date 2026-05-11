<?php

namespace App\Http\Requests\SlaRule;

use App\Models\SlaRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreSlaRuleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('manage', SlaRule::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'priority_id' => ['required', 'exists:priorities,id'],
            'response_hours' => ['required', 'integer', 'min:1'],
            'resolution_hours' => ['required', 'integer', 'min:1'],
        ];
    }
}
