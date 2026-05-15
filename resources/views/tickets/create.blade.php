<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-semibold text-gray-800">Buat Tiket</h1>
            <a href="{{ route('tickets.index') }}" class="rounded bg-blue-600 px-4 py-2 text-sm font-medium text-white">
                Kembali
            </a>
        </div>
    </x-slot>

    <div class="max-w-3xl mx-auto p-6">
        <x-card title="Informasi Tiket">
            <form action="{{ route('tickets.store') }}" method="post" enctype="multipart/form-data" class="space-y-5">
                @csrf
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700">Judul</label>
                    <input type="text" name="title" id="title" value="{{ old('title') }}" class="mt-1 w-full rounded border-gray-300">
                    @error('title')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Deskripsi</label>
                    <textarea name="description" id="description" rows="4" class="mt-1 w-full rounded border-gray-300">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label for="category_id" class="block text-sm font-medium text-gray-700">Kategori</label>
                        <select name="category_id" id="category_id" class="mt-1 w-full rounded border-gray-300">
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="priority_id" class="block text-sm font-medium text-gray-700">Prioritas</label>
                        <select name="priority_id" id="priority_id" class="mt-1 w-full rounded border-gray-300">
                            @foreach ($priorities as $priority)
                                <option value="{{ $priority->id }}" {{ old('priority_id') == $priority->id ? 'selected' : '' }}>{{ $priority->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Label</label>
                    <select id="label-dropdown" class="mt-1 w-full rounded border-gray-300">
                        <option value="">-- Pilih Label --</option>
                        @foreach ($labels as $label)
                            <option value="{{ $label->id }}" data-name="{{ $label->name }}">{{ $label->name }}</option>
                        @endforeach
                    </select>
                    <div id="label-tags" class="mt-2 flex flex-wrap gap-2"></div>
                </div>

                <script>
                    const dropdown = document.getElementById('label-dropdown');
                    const tagsContainer = document.getElementById('label-tags');

                    dropdown.addEventListener('change', function () {
                        const val = this.value;
                        const name = this.options[this.selectedIndex].dataset.name;
                        if (!val) return;

                        const tag = document.createElement('span');
                        tag.className = 'inline-flex items-center gap-2 rounded bg-green-50 px-2.5 py-1 text-sm text-green-700';
                        tag.innerHTML = `${name} <button type="button" data-val="${val}">x</button>`;

                        const hidden = document.createElement('input');
                        hidden.type = 'hidden';
                        hidden.name = 'label_ids[]';
                        hidden.value = val;
                        hidden.id = `hidden-label-${val}`;
                        tag.appendChild(hidden);

                        tag.querySelector('button').addEventListener('click', function () {
                            const removedVal = this.dataset.val;
                            const opt = document.createElement('option');
                            opt.value = removedVal;
                            opt.dataset.name = name;
                            opt.textContent = name;
                            dropdown.appendChild(opt);
                            tag.remove();
                        });

                        tagsContainer.appendChild(tag);

                        this.querySelector(`option[value="${val}"]`).remove();
                        this.value = '';
                    });
                </script>

                <div>
                    <label for="attachments" class="block text-sm font-medium text-gray-700">Lampiran</label>
                    <input type="file" name="attachments[]" id="attachments" class="mt-1 w-full rounded border border-gray-300 p-2 text-sm" multiple>
                    @error('attachments')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                    @foreach ($errors->get('attachments.*') as $messages)
                        @foreach ($messages as $message)
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @endforeach
                    @endforeach
                </div>

                <button type="submit" class="rounded bg-blue-600 px-4 py-2 text-sm font-medium text-white">
                    Simpan
                </button>
            </form>
        </x-card>
    </div>
</x-app-layout>
