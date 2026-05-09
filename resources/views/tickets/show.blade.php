<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $ticket->title }}</h2>
        <!-- tambahkan tombol kembali -->
        <a href="{{ route('tickets.index') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Kembali</a>
    </x-slot>
    <div class="container mx-auto">
        <div class="mb-4">
            <p>Status: {{ $ticket->status }}</p>
            @can('changeStatus', $ticket)
                <form action="{{ route('tickets.status.update', $ticket) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <label for="status">Ubah Status:</label>
                    <select name="status" id="status">
                        @foreach ($statuses as $status)
                            <option value="{{ $status }}" {{ $ticket->status->value === $status ? 'selected' : '' }}>
                                {{ \App\Enums\TicketStatus::from($status)->label() }}
                            </option>
                        @endforeach
                    </select>
                    @error('status')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                    <button type="submit">Simpan</button>
                </form>
            @endcan
            <p>Prioritas: {{ $ticket->priority->name }}</p>
            <p>Kategori: {{ $ticket->category->name }}</p>
        </div>
        <div class="mb-4">
            <p>Deskripsi:</p>
            <p>{{ $ticket->description }}</p>
        </div>
        <div class="mb-4">
            <p>Dibuat oleh: {{ $ticket->creator->name }}</p>
            <p>Dibuat pada: {{ $ticket->created_at }}</p>
        </div>
        <div class="mb-4">
            <p>Label:</p>
            <ul>
                @foreach ($ticket->labels as $label)
                    <li>{{ $label->name }}</li>
                @endforeach
            </ul>
        </div>
        <div class="mb-4">
            <p>Lampiran:</p>
            <ul>
                @foreach ($ticket->attachments as $attachment)
                    <li><a href="{{ route('attachments.show', $attachment) }}">{{ $attachment->original_name }}</a></li>
                @endforeach
            </ul>
        </div>
        <!-- current agent yang didelegasikan -->
        <div class="mb-4">
            <p>Agen yang didelegasikan:</p>
            @if($ticket->assignedAgent)
                <p>{{ $ticket->assignedAgent->name }}</p>
            @else
                <p>Tidak ada agen yang didelegasikan.</p>
            @endif
        </div>
        <div class="mb-8">
            @can('assign', $ticket)
                <form action="{{ route('tickets.assign', $ticket) }}" method="POST" class="inline-block">
                    @csrf
                    <label for="agent_id" class="text-sm font-medium text-gray-700">Pendelegasikan ke Agen:</label>
                    <select id="agent_id" name="assigned_agent_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @foreach ($agents as $agent)
                            <option value="{{ $agent->id }}" @if($ticket->assignedAgent && $ticket->assignedAgent->id == $agent->id) selected @endif>{{ $agent->name }}</option>
                        @endforeach
                    </select>
                    @error('assigned_agent_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Pendelegasikan</button>
                </form>
            @endcan
        </div>
        <!-- Formulir Tambah Komentar -->
        <div class="mb-8">
            <form action="{{ route('tickets.comments.store', $ticket) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-4">
                    <textarea name="body" rows="3" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="Tulis komentar..."></textarea>
                    @error('body')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                @if(!auth()->user()->hasRole('customer'))
                    <div class="mb-4">
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="is_internal" value="1" class="form-checkbox text-blue-600">
                            <span class="ml-2 text-sm text-gray-700">Tandai sebagai Catatan Internal</span>
                        </label>
                    </div>
                @endif
                <div class="mb-4">
                    <label for="attachments" class="block text-gray-700 text-sm font-bold mb-2">Lampiran</label>
                    <input type="file" name="attachments[]" id="attachments" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" multiple>
                    @error('attachments')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Tambahkan Komentar</button>
            </form>
        </div>

        <!-- Daftar Komentar -->
        <div class="flex flex-col gap-4">
            @foreach ($ticket->comments as $comment)
                <!-- Logika saringan: Tampilkan jika publik, ATAU jika agen/admin berhak melihat catatan internal -->
                @if (! $comment->is_internal || auth()->user()->can('viewInternal', $comment))
                    <div class="bg-gray-200 p-4 rounded {{ $comment->is_internal ? 'border-l-4 border-red-500' : 'border-l-4 border-blue-500' }}">
                        <p class="text-gray-800">{{ $comment->body }}</p>
                        @if($comment->attachments->isNotEmpty())
                            <div class="mt-4">
                                <p class="text-sm text-gray-600">Lampiran:</p>
                                <ul>
                                    @foreach ($comment->attachments as $attachment)
                                        <li><a href="{{ route('attachments.show', $attachment) }}">{{ $attachment->original_name }}</a></li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <p class="text-sm text-gray-600 mt-2">Dibuat oleh: {{ $comment->user->name }} 
                            @if($comment->is_internal)
                                <span class="text-red-600 font-bold ml-1">(Internal)</span>
                            @endif
                        </p>
                        <p class="text-xs text-gray-500">Dibuat pada: {{ $comment->created_at }}</p>
                    </div>
                @endif
            @endforeach
        </div>
        <form action="{{ route('tickets.attachments.store', $ticket->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="file" name="attachments[]" multiple required>
            <button type="submit">Uji Upload Lampiran Baru</button>
        </form>
    </div>
</x-app-layout>