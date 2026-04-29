<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AssignTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Pintu Masuk 1: Batasan Otorisasi
        // Pastikan user yang login adalah admin atau penyelia
        // Bagaimana Anda mengubah logika pada authorize() agar membandingkan nilai Auth::user()->role->slug dengan array ['administrator', 'supervisor'] menggunakan fungsi in_array()?
        return Auth::check() && 
            in_array(Auth::user()->role->slug, ['administrator', 'supervisor']);
    }

    public function rules(): array
    {
        // Pintu Masuk 2: Penyaringan Data
        return [
            'agent_id' => [
                'required',
                'integer',
                // Memastikan agen eksis di tabel users
                'exists:users,id', 
                // Memastikan user tersebut memiliki peran 'agent'
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->where('role_id', function ($subQuery) {
                        $subQuery->select('id')
                                 ->from('roles')
                                 ->where('slug', 'agent');
                    });
                }),
            ],
        ];
    }
}
