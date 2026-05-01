<?php

namespace App\Http\Requests\Ticket;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
class UpdateTicketRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    /*
    Silakan buat kelas permintaan khusus melalui Artisan, misalnya UpdateTicketRequest. Bagaimana Anda menyetel fungsi authorize() di dalamnya agar memanggil Gate::allows('update', $ticket) dari TicketPolicy yang telah Anda siapkan sebelumnya?
    */ 
    public function authorize(): bool
    {
        $ticket = $this->route('ticket'); // Ambil tiket dari route parameter
        return Gate::allows('update', $ticket); // Periksa izin menggunakan Gate
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */

    /*
    Dalam berkas UpdateTicketRequest yang sama, bagaimana Anda menyusun susunan (array) rules() untuk kolom title, description, category_id, dan priority_id? (Petunjuk: Anda bisa menggunakan aturan yang kurang lebih sama dengan StoreTicketRequest, mungkin dengan menambahkan sometimes jika Anda ingin mengizinkan permohonan pengubahan parsial).
    */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'required', 'string'],
            'category_id' => ['sometimes', 'required', 'integer', 'exists:categories,id'],
            'priority_id' => ['sometimes', 'required', 'integer', 'exists:priorities,id'],
        ];
    }
}
