<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Dasbor Pelanggan
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Area Kartu Statistik -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white p-4 rounded shadow border-l-4 border-gray-500">
                    <h3 class="text-gray-500 text-sm font-bold uppercase">Total Tiket</h3>
                    <p class="text-2xl font-bold">{{ $totalTickets }}</p>
                </div>
                <div class="bg-white p-4 rounded shadow border-l-4 border-blue-500">
                    <h3 class="text-blue-500 text-sm font-bold uppercase">Tiket Terbuka</h3>
                    <p class="text-2xl font-bold">{{ $openTickets }}</p>
                </div>
                <div class="bg-white p-4 rounded shadow border-l-4 border-green-500">
                    <h3 class="text-green-500 text-sm font-bold uppercase">Tiket Selesai</h3>
                    <p class="text-2xl font-bold">{{ $resolvedTickets }}</p>
                </div>
                <div class="bg-white p-4 rounded shadow border-l-4 border-red-500">
                    <h3 class="text-red-500 text-sm font-bold uppercase">Melewati Batas Waktu</h3>
                    <p class="text-2xl font-bold">{{ $overdueTickets }}</p>
                </div>
            </div>

            <!-- Area Daftar Pembaruan Terakhir -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="font-bold text-lg mb-4 border-b pb-2">5 Pembaruan Tiket Terakhir</h3>
                    
                    @if($recentUpdates->isEmpty())
                        <p class="text-gray-500 italic">Belum ada tiket yang diperbarui.</p>
                    @else
                        <ul class="divide-y divide-gray-200">
                            @foreach ($recentUpdates as $ticket)
                                <li class="py-3 flex justify-between items-center">
                                    <div>
                                        <!-- Pastikan route 'tickets.show' sudah tersedia di web.php -->
                                        <a href="{{ route('tickets.show', $ticket->id) }}" class="font-medium text-blue-600 hover:underline">
                                            {{ $ticket->title }}
                                        </a>
                                        <div class="text-sm text-gray-400 mt-1">
                                            Diperbarui: {{ $ticket->updated_at->diffForHumans() }}
                                        </div>
                                    </div>
                                    <div>
                                        <span class="px-3 py-1 text-xs rounded-full bg-gray-100 text-gray-800">
                                            {{ $ticket->status }}
                                        </span>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif

                </div>
            </div>

        </div>
    </div>
</x-app-layout>