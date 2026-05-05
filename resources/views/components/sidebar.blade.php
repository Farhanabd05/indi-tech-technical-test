<aside class="w-full min-h-screen bg-white border-r border-gray-200 flex flex-col shadow-sm">
    {{-- Header --}}
    <div class="px-6 py-6 border-b border-gray-100">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-teal-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
            <h3 class="text-xs font-bold tracking-widest uppercase text-gray-400">Menu Utama</h3>
        </div>
    </div>

    {{-- Nav --}}
    <nav class="flex-1 px-4 py-5 overflow-y-auto">
        <ul class="space-y-1">
            {{-- Tautan ini valid untuk semua peran berkat DashboardController Anda --}}
            <li>
                <a href="{{ route('dashboard') }}"
                   class="flex items-center gap-3.5 px-4 py-3 rounded-xl text-gray-600 hover:text-teal-700 hover:bg-teal-50 transition-all duration-150 group">
                    <svg class="w-5 h-5 text-gray-400 group-hover:text-teal-500 transition-colors shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span class="text-sm font-semibold">Dashboard</span>
                </a>
            </li>

            {{-- Tautan Khusus PELANGGAN --}}
            @if(auth()->user()->hasRole('customer'))
                <li>
                    <a href="{{ route('tickets.index') }}"
                       class="flex items-center gap-3.5 px-4 py-3 rounded-xl text-gray-600 hover:text-teal-700 hover:bg-teal-50 transition-all duration-150 group">
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-teal-500 transition-colors shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                        </svg>
                        <span class="text-sm font-semibold">Tiket Saya</span>
                    </a>
                </li>
            @endif

            {{-- Tautan Khusus AGEN --}}
            @if(auth()->user()->hasRole('agent'))
                <li>
                    <a href="{{ route('tickets.index') }}"
                       class="flex items-center gap-3.5 px-4 py-3 rounded-xl text-gray-600 hover:text-teal-700 hover:bg-teal-50 transition-all duration-150 group">
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-teal-500 transition-colors shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        <span class="text-sm font-semibold">Tiket Ditugaskan</span>
                    </a>
                </li>
            @endif

            {{-- Tautan Khusus PENYELIA --}}
            @if(auth()->user()->hasRole('supervisor'))
                <li>
                    <a href="{{ route('tickets.index') }}"
                       class="flex items-center gap-3.5 px-4 py-3 rounded-xl text-gray-600 hover:text-teal-700 hover:bg-teal-50 transition-all duration-150 group">
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-teal-500 transition-colors shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span class="text-sm font-semibold">Tiket Tim</span>
                    </a>
                </li>

                {{-- Section: Sistem --}}
                <li class="pt-6 pb-2 px-4">
                    <span class="text-[10px] font-bold tracking-widest uppercase text-gray-400">Sistem</span>
                </li>

                <li>
                    <a href="{{ route('activity_logs.index') }}"
                       class="flex items-center gap-3.5 px-4 py-3 rounded-xl text-gray-600 hover:text-teal-700 hover:bg-teal-50 transition-all duration-150 group">
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-teal-500 transition-colors shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                        </svg>
                        <span class="text-sm font-semibold">Log Aktivitas</span>
                    </a>
                </li>
            @endif

            {{-- Tautan Khusus ADMINISTRATOR --}}
            @if(auth()->user()->hasRole('administrator'))
                <li>
                    <a href="{{ route('tickets.index') }}"
                       class="flex items-center gap-3.5 px-4 py-3 rounded-xl text-gray-600 hover:text-teal-700 hover:bg-teal-50 transition-all duration-150 group">
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-teal-500 transition-colors shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                        <span class="text-sm font-semibold">Semua Tiket</span>
                    </a>
                </li>

                {{-- Section: Master Data --}}
                <li class="pt-6 pb-2 px-4">
                    <span class="text-[10px] font-bold tracking-widest uppercase text-gray-400">Master Data</span>
                </li>

                <li>
                    <a href="{{ route('admin.users.index') }}"
                       class="flex items-center gap-3.5 px-4 py-3 rounded-xl text-gray-600 hover:text-teal-700 hover:bg-teal-50 transition-all duration-150 group">
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-teal-500 transition-colors shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        <span class="text-sm font-semibold">Pengguna</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.roles.index') }}"
                       class="flex items-center gap-3.5 px-4 py-3 rounded-xl text-gray-600 hover:text-teal-700 hover:bg-teal-50 transition-all duration-150 group">
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-teal-500 transition-colors shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        <span class="text-sm font-semibold">Hak Akses</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.categories.index') }}"
                       class="flex items-center gap-3.5 px-4 py-3 rounded-xl text-gray-600 hover:text-teal-700 hover:bg-teal-50 transition-all duration-150 group">
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-teal-500 transition-colors shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-5 5a2 2 0 01-2.828 0l-7-7A2 2 0 013 10V5a2 2 0 012-2h2z"/>
                        </svg>
                        <span class="text-sm font-semibold">Kategori</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.labels.index') }}"
                       class="flex items-center gap-3.5 px-4 py-3 rounded-xl text-gray-600 hover:text-teal-700 hover:bg-teal-50 transition-all duration-150 group">
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-teal-500 transition-colors shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-5 5a2 2 0 01-2.828 0l-7-7A2 2 0 013 10V5a2 2 0 012-2h2z"/>
                        </svg>
                        <span class="text-sm font-semibold">Label</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.priorities.index') }}"
                       class="flex items-center gap-3.5 px-4 py-3 rounded-xl text-gray-600 hover:text-teal-700 hover:bg-teal-50 transition-all duration-150 group">
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-teal-500 transition-colors shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"/>
                        </svg>
                        <span class="text-sm font-semibold">Prioritas</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.sla-rules.index') }}"
                       class="flex items-center gap-3.5 px-4 py-3 rounded-xl text-gray-600 hover:text-teal-700 hover:bg-teal-50 transition-all duration-150 group">
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-teal-500 transition-colors shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="text-sm font-semibold">Aturan SLA</span>
                    </a>
                </li>

                {{-- Section: Sistem --}}
                <li class="pt-6 pb-2 px-4">
                    <span class="text-[10px] font-bold tracking-widest uppercase text-gray-400">Sistem</span>
                </li>

                <li>
                    <a href="{{ route('activity_logs.index') }}"
                       class="flex items-center gap-3.5 px-4 py-3 rounded-xl text-gray-600 hover:text-teal-700 hover:bg-teal-50 transition-all duration-150 group">
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-teal-500 transition-colors shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                        </svg>
                        <span class="text-sm font-semibold">Log Aktivitas</span>
                    </a>
                </li>
            @endif
        </ul>
    </nav>
</aside>