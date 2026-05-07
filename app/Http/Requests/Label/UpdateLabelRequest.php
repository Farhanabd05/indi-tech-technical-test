<?php

namespace App\Http\Requests\Label;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class UpdateLabelRequest extends FormRequest
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
            /*
            Sebagai pendamping belajar Anda, mari kita perbaiki ini selangkah demi selangkah.

Untuk perbaikan pertama: Coba Anda ingat kembali bagaimana Anda menyelesaikan masalah validasi unique pada pembaruan pengguna di berkas UpdateUserRequest.php sebelumnya. Bagaimana Anda akan memodifikasi baris unique di dalam UpdateLabelRequest.php ini menggunakan metode Rule::unique('labels')->ignore(...) agar peladen tidak membentrokkan data dengan dirinya sendiri? Silakan Anda terapkan, lalu sesuaikan juga atribut type dan name di kedua berkas Blade Anda.
            */
            'name' => ['required', 'string', 'max:255', Rule::unique('labels')->ignore($this->route('label'))],
            'color' => ['required', 'string', 'max:255', Rule::unique('labels')->ignore($this->route('label'))],
        ];
    }
}
