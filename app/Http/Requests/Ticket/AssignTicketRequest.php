<?php

namespace App\Http\Requests\Ticket;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Validation\Validator;

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
            'assigned_agent_id' => [
                'nullable',
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

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $user = Auth::user();
            $agentId = $this->input('assigned_agent_id');

            if (! $user || ! $user->hasRole('supervisor') || $agentId === null) {
                return;
            }

            $agent = User::find($agentId);

            if (! $agent || $user->team_id === null || $agent->team_id !== $user->team_id) {
                abort(403);
            }
        });
    }
}
