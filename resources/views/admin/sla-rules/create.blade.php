<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Master Data Aturan SLA') }}
            </h2>
            <a href="{{ route('admin.sla-rules.index') }}" class="bg-blue-500 hover:bg-blue-700 text-black font-bold py-2 px-4 rounded">
                Kembali
            </a>
        </div>
    </x-slot>


    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{ route('admin.sla-rules.store') }}" method="POST">
                        @csrf
                        @method('POST')
                        <div class="mb-6">
                            <label for="name" class="block text-gray-700 text-sm font-bold mb-2">
                                Nama Aturan SLA
                            </label>
                            <!-- select prioritas dengan dropdown dinamis -->
                            <select name="priority_id" id="priority_id" class="form-control" required>
                                <option value="">-- Pilih Prioritas --</option>
                                @foreach ($priorities as $priority)
                                    <option value="{{ $priority->id }}">{{ $priority->name }}</option>
                                @endforeach
                            </select>
                            @error('priority_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="level" class="block text-gray-700 text-sm font-bold mb-2">
                                Waktu Respon (jam)
                            </label>
                            <input type="number" name="response_hours" id="response_hours" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                            @error('response_hours')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="level" class="block text-gray-700 text-sm font-bold mb-2">
                                Waktu Penyelesaian (jam)
                            </label>
                            <input type="number" name="resolution_hours" id="resolution_hours" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                            @error('resolution_hours')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="flex items-center justify-between">
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                                Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>