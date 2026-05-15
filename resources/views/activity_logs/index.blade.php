<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Aktivitas Tiket</h2>
    </x-slot>

    <div class="max-w-7xl mx-auto space-y-6 p-6">
        <x-table>
            <thead class="border-b bg-gray-50">
                <tr>
                    <th class="px-4 py-3 font-medium text-gray-600">No Tiket</th>
                    <th class="px-4 py-3 font-medium text-gray-600">Judul Tiket</th>
                    <th class="px-4 py-3 font-medium text-gray-600">Aktor</th>
                    <th class="px-4 py-3 font-medium text-gray-600">Tanggal</th>
                    <th class="px-4 py-3 font-medium text-gray-600">Detail Aktivitas</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @foreach ($logs as $log)
                    <tr>
                        <td class="px-4 py-3">
                            @if($log->ticket)
                                <a href="{{ route('tickets.show', $log->ticket) }}" class="text-blue-600 hover:underline">
                                    {{ $log->ticket->ticket_number }}
                                </a>
                            @else
                                Tiket sudah dihapus
                            @endif
                        </td>
                        <td class="px-4 py-3">{{ $log->ticket?->title ?? 'Tiket sudah dihapus' }}</td>
                        <td class="px-4 py-3">{{ $log->user ? $log->user->name : 'System' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $log->created_at }}</td>
                        <td class="px-4 py-3">
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
                                    Tiket di-assign kepada {{ $log->targetUser?->name ?? 'Unknown' }}
                                @break

                                @case('reassign_ticket')
                                    Tiket di-reassign kepada {{ $log->targetUser?->name ?? 'Unknown' }}
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
        </x-table>

        {{ $logs->links() }}
    </div>
</x-app-layout>
