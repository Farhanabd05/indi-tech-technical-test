<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Supervisor Dashboard
        </h2>
    </x-slot>

    <div class="max-w-7xl mx-auto space-y-6 p-6">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
            <x-card>
                <p class="text-sm text-gray-500">Total Tickets</p>
                <p class="mt-2 text-2xl font-semibold">{{ $totalTickets }}</p>
            </x-card>
            <x-card>
                <p class="text-sm text-gray-500">Open Tickets</p>
                <p class="mt-2 text-2xl font-semibold">{{ $openTickets }}</p>
            </x-card>
            <x-card>
                <p class="text-sm text-gray-500">Overdue Tickets</p>
                <p class="mt-2 text-2xl font-semibold">{{ $overdueTickets }}</p>
            </x-card>
            <x-card>
                <p class="text-sm text-gray-500">Escalated Tickets</p>
                <p class="mt-2 text-2xl font-semibold">{{ $escalatedTickets }}</p>
            </x-card>
        </div>

        <x-card title="Agent Performance">
            <x-table>
                <thead class="border-b bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 font-medium text-gray-600">Agent</th>
                        <th class="px-4 py-3 font-medium text-gray-600">Active Tickets</th>
                        <th class="px-4 py-3 font-medium text-gray-600">Average Resolution</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach ($agentWorkload as $agent)
                        <tr>
                            <td class="px-4 py-3">{{ $agent->assignedAgent->name }}</td>
                            <td class="px-4 py-3">{{ $agent->total }}</td>
                            <td class="px-4 py-3">{{ $averageResolutionTime[$agent->assigned_agent_id] ?? 'N/A' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </x-table>
        </x-card>
    </div>
</x-app-layout>
