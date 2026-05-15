<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Admin Dashboard</h2>
    </x-slot>

    <div class="max-w-7xl mx-auto space-y-6 p-6">

        <div class="grid grid-cols-1 gap-4 md:grid-cols-5">
            <x-card>
                <p class="text-sm text-gray-500">Total Tickets</p>
                <p class="mt-2 text-2xl font-semibold">{{ $totalTickets }}</p>
            </x-card>
            <x-card>
                <p class="text-sm text-gray-500">Overdue Tickets</p>
                <p class="mt-2 text-2xl font-semibold">{{ $overdueTickets }}</p>
            </x-card>
            <x-card>
                <p class="text-sm text-gray-500">Unassigned Tickets</p>
                <p class="mt-2 text-2xl font-semibold">{{ $unassignedTickets }}</p>
            </x-card>
            <x-card>
                <p class="text-sm text-gray-500">Created This Week</p>
                <p class="mt-2 text-2xl font-semibold">{{ $createdThisWeek }}</p>
            </x-card>
            <x-card>
                <p class="text-sm text-gray-500">Average Resolution Time: {{ round($averageResolution) }} minutes</p>
                <p class="mt-2 text-2xl font-semibold">{{ round($averageResolution) }} minutes</p>
            </x-card>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-card title="Top Performing Agents">
                <x-table>
                    <thead class="border-b bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 font-medium text-gray-600">Agent</th>
                            <th class="px-4 py-3 font-medium text-gray-600">Tickets Resolved</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach ($topAgents as $agent)
                            <tr>
                                <td class="px-4 py-3">{{ $agent->assignedAgent->name }}</td>
                                <td class="px-4 py-3">{{ $agent->total }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </x-table>
            </x-card>

            <x-card title="Distribusi Tiket">
                <div class="space-y-8">
                    <div class="h-64">
                        <canvas id="ticketsByStatusChart"></canvas>
                    </div>
                    <div class="h-64">
                        <canvas id="ticketsByPriorityChart"></canvas>
                    </div>
                    <div class="h-64">
                        <canvas id="ticketsByCategoryChart"></canvas>
                    </div>
                </div>
            </x-card>
        </div>
    </div>
    <!-- Load Chart.js from CDN (Dipindah ke dalam layout) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // Fungsi reusable untuk menggambar grafik (Prinsip DRY)
        function renderChart(canvasId, chartData, labelName, chartColor) {
            const ctx = document.getElementById(canvasId);
            if (!ctx || !chartData) return;

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: Object.keys(chartData),
                    datasets: [{
                        label: labelName,
                        data: Object.values(chartData),
                        backgroundColor: chartColor,
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                }
            });
        }

        // Eksekusi pelemparan data PHP ke JS dan pemanggilan fungsi
        renderChart('ticketsByStatusChart', @json($ticketsByStatus), 'Status Tiket', 'rgba(54, 162, 235, 0.6)');
        renderChart('ticketsByPriorityChart', @json($ticketsByPriority), 'Prioritas Tiket', 'rgba(255, 99, 132, 0.6)');
        renderChart('ticketsByCategoryChart', @json($ticketsByCategory), 'Kategori Tiket', 'rgba(75, 192, 192, 0.6)');
    </script>
</x-app-layout>
