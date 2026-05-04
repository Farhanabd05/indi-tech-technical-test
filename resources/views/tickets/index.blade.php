<x-app-layout>
    <x-slot name="header">
        <h1 class="text-3xl font-bold underline">Daftar Tiket</h1>
    </x-slot>
    <div class="container mx-auto">
        <a href="{{ route('tickets.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Buat Tiket Baru</a>
    </div>

    <div class="container mx-auto mt-4">
        @foreach ($tickets ?? [] as $ticket)
            <article class="p-6 bg-white border border-gray-200 rounded shadow">
                <h2 class="text-xl font-bold mb-2">{{ $ticket->title }}</h2>
                <p class="text-gray-700">{{ $ticket->description }}</p>
                <div class="flex justify-between items-center mt-4">
                    <span class="text-gray-500">Dibuat pada: {{ $ticket->created_at->format('d/m/Y') }}</span>
                    <span class="text-gray-500">Status: {{ $ticket->status }}</span>
                </div>
                <a href="{{ route('tickets.show', $ticket) }}" class="text-blue-500 hover:text-blue-700">Lihat Detail</a>
            </article>
        @endforeach
    </div>
</x-app-layout>