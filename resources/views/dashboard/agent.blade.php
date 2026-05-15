<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Agent Dashboard
        </h2>
    </x-slot>

    <div class="max-w-7xl mx-auto space-y-6 p-6">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
            <x-card>
                <p class="text-sm text-gray-500">Total Tickets</p>
                <p class="mt-2 text-2xl font-semibold">{{ $totalTickets }}</p>
            </x-card>
            <x-card>
                <p class="text-sm text-gray-500">In Progress</p>
                <p class="mt-2 text-2xl font-semibold">{{ $inProgressTickets }}</p>
            </x-card>
            <x-card>
                <p class="text-sm text-gray-500">Waiting for Customer</p>
                <p class="mt-2 text-2xl font-semibold">{{ $waitingForCustomerTickets }}</p>
            </x-card>
            <x-card>
                <p class="text-sm text-gray-500">Overdue</p>
                <p class="mt-2 text-2xl font-semibold">{{ $overdueTickets }}</p>
            </x-card>
        </div>

        <x-card title="Recent Updates">
            @if ($recentUpdates->isEmpty())
                <p class="text-sm text-gray-500">No recent updates available.</p>
            @else
                <div class="divide-y">
                    @foreach ($recentUpdates as $update)
                        <div class="py-4 first:pt-0 last:pb-0">
                            <a href="{{ route('tickets.show', $update->id) }}" class="font-medium text-blue-600 hover:underline">
                                {{ $update->title }}
                            </a>
                            <div class="mt-2 flex flex-wrap gap-3 text-sm text-gray-600">
                                <x-status-badge :status="$update->status" />
                                <span>Updated At: {{ $update->updated_at->format('d M Y H:i') }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-card>
    </div>
</x-app-layout>
