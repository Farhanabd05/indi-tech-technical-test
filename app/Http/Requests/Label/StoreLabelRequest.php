<?php

namespace App\Http\Requests\Label;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use App\Models\Label;
class StoreLabelRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Contoh untuk Label. Ganti class model sesuai peruntukannya.
        return $this->user()->can('manage', Label::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:labels,name'],
            'color' => ['required', 'string', 'max:255', 'unique:labels,color'],
        ];
    }
}
