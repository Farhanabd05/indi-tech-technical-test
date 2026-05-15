<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-800">{{ $ticket->title }}</h2>
            <a href="{{ route('tickets.index') }}" class="rounded bg-blue-600 px-4 py-2 text-sm font-medium text-white">Kembali</a>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto space-y-6 p-6">
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <x-card title="Detail Tiket">
                    <div class="space-y-4">
                        <div class="flex flex-wrap items-center gap-3">
                            <x-status-badge :status="$ticket->status" />
                            <span class="text-sm text-gray-600">Prioritas: {{ $ticket->priority->name }}</span>
                            <span class="text-sm text-gray-600">Kategori: {{ $ticket->category->name }}</span>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-500">Deskripsi</p>
                            <p class="mt-1 text-gray-800">{{ $ticket->description }}</p>
                        </div>

                        <div class="grid grid-cols-1 gap-4 text-sm text-gray-600 md:grid-cols-2">
                            <p>Dibuat oleh: {{ $ticket->creator->name }}</p>
                            <p>Dibuat pada: {{ $ticket->created_at }}</p>
                        </div>
                    </div>
                </x-card>

                <x-card title="Komentar">
                    <form action="{{ route('tickets.comments.store', $ticket) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <div>
                            <textarea name="body" rows="3" class="w-full rounded border-gray-300" placeholder="Tulis komentar..."></textarea>
                            @error('body')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        @if(!auth()->user()->hasRole('customer'))
                            <label class="inline-flex items-center text-sm text-gray-700">
                                <input type="checkbox" name="is_internal" value="1" class="rounded border-gray-300 text-blue-600">
                                <span class="ml-2">Tandai sebagai Catatan Internal</span>
                            </label>
                        @endif

                        <div>
                            <label for="attachments" class="block text-sm font-medium text-gray-700">Lampiran</label>
                            <input type="file" name="attachments[]" id="attachments" class="mt-1 w-full rounded border border-gray-300 p-2 text-sm" multiple>
                            @error('attachments')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                            @foreach ($errors->get('attachments.*') as $messages)
                                @foreach ($messages as $message)
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @endforeach
                            @endforeach
                        </div>

                        <button type="submit" class="rounded bg-blue-600 px-4 py-2 text-sm font-medium text-white">Tambahkan Komentar</button>
                    </form>

                    <div class="mt-6 divide-y">
                        @foreach ($ticket->comments as $comment)
                            @if (! $comment->is_internal || auth()->user()->can('viewInternal', $comment))
                                <div class="py-4 first:pt-0 last:pb-0">
                                    <p class="text-gray-800">{{ $comment->body }}</p>

                                    @if($comment->attachments->isNotEmpty())
                                        <div class="mt-3">
                                            <p class="text-sm font-medium text-gray-500">Lampiran</p>
                                            <ul class="mt-1 space-y-1">
                                                @foreach ($comment->attachments as $attachment)
                                                    <li>
                                                        <a href="{{ route('attachments.show', $attachment) }}" class="text-blue-600 hover:underline">
                                                            {{ $attachment->original_name }}
                                                        </a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif

                                    <p class="mt-3 text-sm text-gray-600">
                                        Dibuat oleh: {{ $comment->user->name }}
                                        @if($comment->is_internal)
                                            <span class="ml-1 font-medium text-red-600">(Internal)</span>
                                        @endif
                                    </p>
                                    <p class="text-xs text-gray-500">Dibuat pada: {{ $comment->created_at }}</p>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </x-card>
            </div>

            <div class="space-y-6">
                @can('changeStatus', $ticket)
                    <x-card title="Ubah Status">
                        <form action="{{ route('tickets.status.update', $ticket) }}" method="POST" class="space-y-3">
                            @csrf
                            @method('PATCH')
                            <select name="status" id="status" class="w-full rounded border-gray-300">
                                @foreach ($statuses as $status)
                                    <option value="{{ $status }}" {{ $ticket->status->value === $status ? 'selected' : '' }}>
                                        {{ \App\Enums\TicketStatus::from($status)->label() }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status')
                                <p class="text-xs text-red-600">{{ $message }}</p>
                            @enderror
                            <button type="submit" class="rounded bg-blue-600 px-4 py-2 text-sm font-medium text-white">Simpan</button>
                        </form>
                    </x-card>
                @endcan

                <x-card title="Agen">
                    <p class="text-sm text-gray-600">
                        {{ $ticket->assignedAgent?->name ?? 'Tidak ada agen yang didelegasikan.' }}
                    </p>

                    @if(Gate::allows('assign', $ticket) || Gate::allows('reassign', $ticket))
                        <form action="{{ route('tickets.assign', $ticket) }}" method="POST" class="mt-4 space-y-3">
                            @csrf
                            <label for="agent_id" class="block text-sm font-medium text-gray-700">Pendelegasikan ke Agen</label>
                            <select id="agent_id" name="assigned_agent_id" class="w-full rounded border-gray-300">
                                @foreach ($agents as $agent)
                                    <option value="{{ $agent->id }}" @if($ticket->assignedAgent && $ticket->assignedAgent->id == $agent->id) selected @endif>{{ $agent->name }}</option>
                                @endforeach
                            </select>
                            @error('assigned_agent_id')
                                <p class="text-xs text-red-600">{{ $message }}</p>
                            @enderror
                            <button type="submit" class="rounded bg-blue-600 px-4 py-2 text-sm font-medium text-white">Pendelegasikan</button>
                        </form>
                    @endif
                </x-card>

                <x-card title="Label">
                    @if($ticket->labels->isEmpty())
                        <p class="text-sm text-gray-500">Tidak ada label.</p>
                    @else
                        <div class="flex flex-wrap gap-2">
                            @foreach ($ticket->labels as $label)
                                <span class="rounded bg-gray-100 px-2.5 py-1 text-xs text-gray-700">{{ $label->name }}</span>
                            @endforeach
                        </div>
                    @endif
                </x-card>

                <x-card title="Lampiran">
                    @if($ticket->attachments->isEmpty())
                        <p class="text-sm text-gray-500">Tidak ada lampiran.</p>
                    @else
                        <ul class="space-y-2">
                            @foreach ($ticket->attachments as $attachment)
                                <li>
                                    <a href="{{ route('attachments.show', $attachment) }}" class="text-blue-600 hover:underline">
                                        {{ $attachment->original_name }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </x-card>

                <x-card title="Upload Lampiran">
                    <form action="{{ route('tickets.attachments.store', $ticket->id) }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                        @csrf
                        <input type="file" name="attachments[]" class="w-full rounded border border-gray-300 p-2 text-sm" multiple required>
                        @error('attachments')
                            <p class="text-xs text-red-600">{{ $message }}</p>
                        @enderror
                        @foreach ($errors->get('attachments.*') as $messages)
                            @foreach ($messages as $message)
                                <p class="text-xs text-red-600">{{ $message }}</p>
                            @endforeach
                        @endforeach
                        <button type="submit" class="rounded bg-blue-600 px-4 py-2 text-sm font-medium text-white">Uji Upload Lampiran Baru</button>
                    </form>
                </x-card>
            </div>
        </div>
    </div>
</x-app-layout>
