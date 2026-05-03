<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Supervisor Dashboard
        </h2>
    </x-slot>
    <div class="container mx-auto">
        <div class="grid grid-cols-4 gap-4 mb-6">
            <div class="bg-white p-4 rounded shadow">
                <h2 class="text-xl font-bold mb-2">Total Tickets: {{ $totalTickets }}</h2>
            </div>
            <div class="bg-white p-4 rounded shadow">
                <h2 class="text-xl font-bold mb-2">Unassigned Tickets: {{ $unassignedTickets }}</h2>
            </div>
            <div class="bg-white p-4 rounded shadow">
                <h2 class="text-xl font-bold mb-2">Overdue Tickets: {{ $overdueTickets }}</h2>
            </div>
        </div>
        <div class="bg-white rounded shadow p-4">
            <h2 class="text-xl font-bold mb-4">Agent Performance</h2>
            <table class="table-auto w-full text-left">
                <thead class="border-b">
                    <tr>
                        <th class="px-4 py-2">Agent</th>
                        <th class="px-4 py-2">Active Tickets</th>
                        <th class="px-4 py-2">Completion Rate</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                @foreach ($agentPerformance as $agent)
                    <tr>
                        <td class="px-4 py-2">{{ $agent->name }}</td>
                        <td class="px-4 py-2">{{ $agent->activeTicketsCount }}</td>
                        <td class="px-4 py-2">{{ $agent->completionRate }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-app-layout>
