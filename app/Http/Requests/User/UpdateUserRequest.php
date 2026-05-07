<?php

namespace App\Http\Requests\User;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class UpdateUserRequest extends FormRequest
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
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($this->route('user'))],
            'password' => ['nullable', 'string', 'min:8'],
            'role_id' => ['required', 'exists:roles,id'],
            'team_id' => ['nullable', 'exists:teams,id', 'required_if:role_id,2,3'],
        ];
    }

    public function attributes(): array
    {
        return [
            'team_id' => 'team',
        ];
    }

    public function messages(): array
    {
        return [
            'team_id.required_if' => 'Kolom :attribute wajib diisi jika Anda memilih peran Agent atau Supervisor.',
        ];
    }
}
