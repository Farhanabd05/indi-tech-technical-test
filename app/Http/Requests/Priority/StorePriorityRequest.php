<?php

namespace App\Http\Requests\Priority;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

//Silakan wujudkan kelas StoreCategoryRequest dan UpdateCategoryRequest melalui Artisan untuk menyaring kewajiban pengisian atribut nama kategori
class StorePriorityRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Mengizinkan semua pengguna untuk melakukan permintaan ini, pembatasan akses akan ditangani oleh middleware atau kebijakan (policy) di lapisan lain.
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'level' => ['required', 'integer', 'min:1'] 
        ];
    }
}
