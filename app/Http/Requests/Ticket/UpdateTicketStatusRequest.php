<?php

namespace App\Http\Requests\Ticket;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\TicketStatus;
use App\Services\TicketStatusService;
use App\Models\Ticket;
use Illuminate\Validation\Rules\Enum;

class UpdateTicketStatusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true; // Atur ini sesuai kebutuhan otorisasi Anda
    }


    public function rules(): array
    {
        return [
            'status' => ['required', 'string', new Enum(TicketStatus::class)],
        ];
    }

    public function withValidator($validator)
    {
        //Kedua, pada bagian withValidator(), Anda menuliskan perintah kueri Ticket::find($this->route('ticket')). Kerangka kerja Laravel umumnya menggunakan fitur Route Model Binding. Jika fitur ini aktif pada rute Anda, variabel $this->route('ticket') sebenarnya sudah berisi wujud objek model Ticket yang utuh, bukan lagi sekadar angka ID. Melakukan fungsi pencarian (find) pada sebuah wujud objek yang sudah ada akan memicu kueri ganda ke pangkalan data secara sia-sia.
        $validator->after(function ($validator) {
            $ticket = $this->route('ticket'); // pake Route Model Binding 

            if (!$ticket) {
                $validator->errors()->add('ticket', 'Ticket not found.');
                return;
            }

            $statusService = new TicketStatusService();
            if (!$statusService->isValidTransition($ticket->status, TicketStatus::from($this->input('status')))) {
                $validator->errors()->add('status', 'Invalid status transition.');
            }
        });
    }
}

?>