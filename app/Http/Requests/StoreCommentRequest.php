<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;   

/*
*/
class StoreCommentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool 
    {
        // Logika otorisasi bisa ditambahkanx di sini jika diperlukan
        return true; // Sementara, izinkan semua pengguna untuk membuat komentar
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'ticket_id' => ['required', 'exists:tickets,id'],
            'content' => ['required', 'string'],
            'is_internal' => Auth::user()->role->slug === 'customer' ? ['prohibited'] : ['sometimes', 'boolean'],
        ];
    }
}
