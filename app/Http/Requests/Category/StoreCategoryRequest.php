<?php

namespace App\Http\Requests\Category;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

//Silakan wujudkan kelas StoreCategoryRequest dan UpdateCategoryRequest melalui Artisan untuk menyaring kewajiban pengisian atribut nama kategori
class StoreCategoryRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255', 'unique:categories,name'],
        ];
    }
}
