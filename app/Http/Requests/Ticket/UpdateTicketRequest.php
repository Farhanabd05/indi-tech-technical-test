<?php

namespace App\Http\Requests\Ticket;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

class UpdateTicketRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
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

    public function rules(): array
    {
        $rules = [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'required', 'string'],
            'category_id' => ['sometimes', 'required', 'integer', 'exists:categories,id'],
            'priority_id' => ['sometimes', 'required', 'integer', 'exists:priorities,id'],
            'assigned_agent_id' => ['sometimes', 'nullable', 'integer', 'exists:users,id'],
        ];

        if (Auth::user()->role->slug !== 'admin' && Auth::user()->role->slug !== 'supervisor') {
            unset($rules['assigned_agent_id']);
        }

        return $rules;
    }
}
