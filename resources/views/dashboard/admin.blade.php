<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Admin Dashboard</h2>
    </x-slot>

    <div class="container mx-auto py-8">

        <!-- WADAH 1: Khusus 5 Metrik Tunggal -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-8">
            <div class="bg-white p-4 rounded shadow">
                <h2 class="text-xl font-bold mb-2">Total Tickets: {{ $totalTickets }}</h2>
            </div>
            <div class="bg-white p-4 rounded shadow">
                <h2 class="text-xl font-bold mb-2">Overdue Tickets: {{ $overdueTickets }}</h2>
            </div>
            <div class="bg-white p-4 rounded shadow">
                <h2 class="text-xl font-bold mb-2">Unassigned Tickets: {{ $unassignedTickets }}</h2>
            </div>
            <div class="bg-white p-4 rounded shadow">
                <h2 class="text-xl font-bold mb-2">Created This Week: {{ $createdThisWeek }}</h2>
            </div>
            <div class="bg-white p-4 rounded shadow">
                <h2 class="text-xl font-bold mb-2">Average Resolution Time: {{ round($averageResolution) }} minutes</h2>
            </div>
        </div>

        <!-- WADAH 2: Khusus Analitik Komparatif -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-white p-4 rounded shadow">
                <h2 class="text-xl font-bold mb-4">Top Performing Agents</h2>
                <table class="table-auto w-full text-left">
                    <thead class="border-b">
                        <tr>
                            <th class="px-4 py-2">Agent</th>
                            <th class="px-4 py-2">Tickets Resolved</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach ($topAgents as $agent)
                            <tr>
                                <td class="px-4 py-2">{{ $agent->assignedAgent->name }}</td>
                                <td class="px-4 py-2">{{ $agent->total }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <!-- Perbaikan Wadah Kanan -->
            <div class="bg-white p-4 rounded shadow">
                <h2 class="text-xl font-bold mb-6">Distribusi Tiket</h2>
                <div class="flex flex-col space-y-8">
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
            </div>
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