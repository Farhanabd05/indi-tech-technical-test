<x-app-layout>
    <x-slot name="header">
        <h1 class="text-3xl font-bold underline">Daftar Tiket</h1>
    </x-slot>
    <div class="container mx-auto">
        @can('create', App\Models\Ticket::class)
            <a href="{{ route('tickets.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Buat Tiket Baru</a>
        @endcan
        @if (Auth::user()->role->slug === 'administrator' || Auth::user()->role->slug === 'supervisor')
            <a href="{{ route('tickets.export', request()->query()) }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">Ekspor CSV</a>
        @endif
    </div>

    <div class="container mx-auto mt-4">
        <form action="{{ route('tickets.index') }}" method="GET">
            <div class="flex flex-wrap items-center gap-4 mt-4">
                <select name="status" id="status" class="mr-2">
                    <option value="" {{ request('status') == '' ? 'selected' : '' }}>Semua Status</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" {{ request('status') == $status->value ? 'selected' : '' }}>{{ $status->label() }}</option>
                    @endforeach
                </select>
                <!--  -->
                <select name="priority_id" id="priority_id" class="mr-2">
                    <option value="" {{ request('priority_id') == '' ? 'selected' : '' }}>Semua Prioritas</option>
                    @foreach ($priorities as $priority)
                        <option value="{{ $priority->id }}" {{ request('priority_id') == $priority->id ? 'selected' : '' }}>{{ $priority->name }}</option>
                    @endforeach
                </select>
                <select name="category_id" id="category_id" class="mr-2">
                    <option value="" {{ request('category_id') == '' ? 'selected' : '' }}>Semua Kategori</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select>
                <select name="label_id" id="label_id" class="mr-2">
                    <option value="" {{ request('label_id') == '' ? 'selected' : '' }}>Semua Label</option>
                    @foreach ($labels as $label)
                        <option value="{{ $label->id }}" {{ request('label_id') == $label->id ? 'selected' : '' }}>{{ $label->name }}</option>
                    @endforeach
                </select>
                <input type="text" name="search" id="search" class="mr-2" value="{{ request('search') }}" placeholder="Cari kata kunci">
                <div class="flex items-center">
                    <span class="mr-2 text-sm text-gray-600">Dari:</span>
                    <input type="date" name="created_from" id="created_from" class="border-gray-300 rounded" value="{{ request('created_from') }}">
                </div>
        
                <div class="flex items-center">
                    <span class="mr-2 text-sm text-gray-600">Hingga:</span>
                    <input type="date" name="created_to" id="created_to" class="border-gray-300 rounded" value="{{ request('created_to') }}">
                </div>

                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Terapkan Filter</button>
            </div>
        </form>
        @foreach ($tickets ?? [] as $ticket)
            <article class="p-6 bg-white border border-gray-200 rounded shadow">
                <h2 class="text-xl font-bold mb-2">{{ $ticket->title }}</h2>
                <p class="text-gray-700">{{ $ticket->description }}</p>
                <div class="flex justify-between items-center mt-4">
                    <span class="text-gray-500">Dibuat pada: {{ $ticket->created_at->format('d/m/Y') }}</span>
                    <span class="text-gray-500">Status: {{ $ticket->status->label() }}</span>
                </div>
                <a href="{{ route('tickets.show', $ticket) }}" class="text-blue-500 hover:text-blue-700">Lihat Detail</a>
            </article>
        @endforeach
        {{ $tickets->appends(request()->query())->links() }}
    </div>
</x-app-layout>