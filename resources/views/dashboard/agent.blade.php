<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Agent Dashboard
        </h2>
    </x-slot>
    <div class="container mx-auto">
        <div class="grid grid-cols-4 gap-4">
            <div class="bg-white p-4 rounded shadow">
                <h2 class="text-xl font-bold mb-2">Total Tickets: {{ $totalTickets }}</h2>
            </div>
            <div class="bg-white p-4 rounded shadow">
                <h2 class="text-xl font-bold mb-2">In Progress Tickets: {{ $inProgressTickets }}</h2>
            </div>
            <div class="bg-white p-4 rounded shadow">
                <h2 class="text-xl font-bold mb-2">Waiting for Customer Tickets: {{ $waitingForCustomerTickets }}</h2>
            </div>
            <div class="bg-white p-4 rounded shadow">
                <h2 class="text-xl font-bold mb-2">Overdue Tickets: {{ $overdueTickets }}</h2>
            </div>
        </div>
        <h2 class="text-xl font-bold mb-4">Recent Updates</h2>
        <ul>
            @if ($recentUpdates->isEmpty())
                <li>No recent updates available.</li>
            @else
                @foreach ($recentUpdates as $update)
                    <li>
                        <h3 class="text-lg font-bold mb-2">
                            <a href=" {{ route('tickets.show', $update->id) }}" class="text-blue-600 hover:underline">{{ $update->title }}</a>
                        </h3>
                        <p class="text-gray-700 mb-2">Status: {{ $update->status }}</p>
                        <p class="text-gray-700 mb-2">Updated At: {{ $update->updated_at->format('d M Y H:i') }}</p>
                    </li>
                @endforeach
            @endif
        </ul>
    </div>
</x-app-layout>