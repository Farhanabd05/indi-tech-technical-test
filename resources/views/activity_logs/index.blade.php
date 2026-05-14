<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Aktivitas Tiket</h2>
    </x-slot>
    <div class="container mx-auto">
        <table class="table-auto w-full">
            <thead>
                <tr>
                    <th class="px-4 py-2">No Tiket</th>
                    <th class="px-4 py-2">Judul Tiket</th>
                    <th class="px-4 py-2">Aktor</th>
                    <th class="px-4 py-2">Tanggal</th>
                    <th class="px-4 py-2">Detail Aktivitas</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($logs as $log)
                    <tr>
                        <td class="border px-4 py-2 text-blue-600 hover:text-blue-800 underline">
                            @if($log->ticket)
                                <a href="{{ route('tickets.show', $log->ticket) }}">
                                    {{ $log->ticket->ticket_number }}
                                </a>
                            @else
                                Tiket sudah dihapus
                            @endif
                        </td>
                        <td class="border px-4 py-2">{{ $log->ticket?->title ?? 'Tiket sudah dihapus' }}</td>
                        <td class="border px-4 py-2">{{ $log->user ? $log->user->name : 'System' }}</td>
                        <td class="border px-4 py-2">{{ $log->created_at }}</td>
                        <td class="border px-4 py-2">
                            @switch($log->action)
                                @case('create_ticket')
                                    Ticket telah dibuat
                                @break

                                @case('update_ticket')
                                    Ticket telah diperbarui
                                @break

                                @case('update_status')
                                    Status tiket diperbarui dari {{ $log->old_value }} ke {{ $log->new_value }}.
                                @break
                                
                                @case('add_comment')
                                    Komentar telah ditambahkan
                                @break

                                @case('assign_ticket')
                                    Tiket di-assign kepada {{ $log->assigned_agent_name }}
                                @break

                                @case('reassign_ticket')
                                    Tiket di-reassign kepada {{ $log->assigned_agent_name }}
                                @break

                                @case('delete_ticket')
                                    Tiket telah dihapus
                                @break

                                @case('upload_attachment')
                                    Lampiran {{ $log->new_value }} telah diunggah
                                @break

                                @case('delete_attachment')
                                    Lampiran {{ $log->old_value }} telah dihapus
                                @break

                                @case('sla_overdue')
                                    SLA tiket telah melewati batas waktu
                                @break

                                @default
                                    Aktivitas lainnya.
                                @endswitch
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{ $logs->links() }}
    </div>
</x-app-layout>
