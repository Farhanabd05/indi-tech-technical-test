<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use App\Models\Ticket;

class StoreTicketRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Pintu Masuk 1: Batasan Otorisasi
        // Bagaimana Anda mengubah logika pada authorize() agar memanggil fungsi evaluasi dari Gate menggunakan metode allows untuk tindakan penciptaan tiket?
        return Gate::allows('create', Ticket::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Pintu Masuk 2: Penyaringan Data
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'priority_id' => ['required', 'integer', 'exists:priorities,id'],
            'label_ids' => ['nullable', 'array'],
            'label_ids.*' => ['integer', 'exists:labels,id'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'max:2048', 'mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx'],
        ];
    }
}
