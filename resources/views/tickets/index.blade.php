<x-app-layout>
    <x-slot name="header">
        <h1 class="text-xl font-semibold text-gray-800">Daftar Tiket</h1>
    </x-slot>

    <div class="max-w-7xl mx-auto space-y-6 p-6">
        <div class="flex flex-wrap gap-3">
            @can('create', App\Models\Ticket::class)
                <a href="{{ route('tickets.create') }}" class="rounded bg-blue-600 px-4 py-2 text-sm font-medium text-white">Buat Tiket Baru</a>
            @endcan
            @if (Auth::user()->hasRole(['administrator', 'supervisor']))
                <a href="{{ route('tickets.export', request()->query()) }}" class="rounded bg-green-600 px-4 py-2 text-sm font-medium text-white">Ekspor CSV</a>
            @endif
        </div>

        <x-card>
            <form action="{{ route('tickets.index') }}" method="GET" class="space-y-4">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                    <select name="status" id="status" class="rounded border-gray-300">
                        <option value="" {{ request('status') == '' ? 'selected' : '' }}>Semua Status</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status->value }}" {{ request('status') == $status->value ? 'selected' : '' }}>{{ $status->label() }}</option>
                        @endforeach
                    </select>

                    <select name="priority_id" id="priority_id" class="rounded border-gray-300">
                        <option value="" {{ request('priority_id') == '' ? 'selected' : '' }}>Semua Prioritas</option>
                        @foreach ($priorities as $priority)
                            <option value="{{ $priority->id }}" {{ request('priority_id') == $priority->id ? 'selected' : '' }}>{{ $priority->name }}</option>
                        @endforeach
                    </select>

                    <select name="category_id" id="category_id" class="rounded border-gray-300">
                        <option value="" {{ request('category_id') == '' ? 'selected' : '' }}>Semua Kategori</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>

                    <select name="label_id" id="label_id" class="rounded border-gray-300">
                        <option value="" {{ request('label_id') == '' ? 'selected' : '' }}>Semua Label</option>
                        @foreach ($labels as $label)
                            <option value="{{ $label->id }}" {{ request('label_id') == $label->id ? 'selected' : '' }}>{{ $label->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                    <input type="text" name="search" id="search" class="rounded border-gray-300" value="{{ request('search') }}" placeholder="Cari kata kunci">
                    <input type="date" name="created_from" id="created_from" class="rounded border-gray-300" value="{{ request('created_from') }}">
                    <input type="date" name="created_to" id="created_to" class="rounded border-gray-300" value="{{ request('created_to') }}">
                    <button type="submit" class="rounded bg-blue-600 px-4 py-2 text-sm font-medium text-white">Terapkan Filter</button>
                </div>
            </form>
        </x-card>

        <x-table>
            <thead class="border-b bg-gray-50">
                <tr>
                    <th class="px-4 py-3 font-medium text-gray-600">Tiket</th>
                    <th class="px-4 py-3 font-medium text-gray-600">Status</th>
                    <th class="px-4 py-3 font-medium text-gray-600">Dibuat</th>
                    <th class="px-4 py-3 font-medium text-gray-600"></th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @foreach ($tickets ?? [] as $ticket)
                    <tr>
                        <td class="px-4 py-4">
                            <p class="font-medium text-gray-900">{{ $ticket->title }}</p>
                            <p class="mt-1 text-sm text-gray-600">{{ $ticket->description }}</p>
                        </td>
                        <td class="px-4 py-4">
                            <x-status-badge :status="$ticket->status" />
                        </td>
                        <td class="px-4 py-4 text-gray-600">{{ $ticket->created_at->format('d/m/Y') }}</td>
                        <td class="px-4 py-4 text-right">
                            <a href="{{ route('tickets.show', $ticket) }}" class="text-blue-600 hover:underline">Lihat Detail</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-table>

        {{ $tickets->appends(request()->query())->links() }}
    </div>
</x-app-layout>
