<x-app-layout>
    <x-slot name="header">
        <h1 class="text-3xl font-bold underline">Daftar Tiket</h1>
        <!-- tombol kembali -->
        <a href="{{ route('tickets.index') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            Kembali
        </a>
    </x-slot>
    <!-- ini buat form gitu -->
    <div class="container mx-auto">
        <form action="{{ route('tickets.store') }}" method="post" enctype="multipart/form-data">
            <div class="mb-4">
                <label for="title" class="block text-gray-700 text-sm font-bold mb-2">Judul</label>
                <input type="text" name="title" id="title" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                @error('title')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="mb-4">
                <label for="description" class="block text-gray-700 text-sm font-bold mb-2">Deskripsi</label>
                <textarea name="description" id="description" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"></textarea>
                @error('description')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="mb-4">
                <label for="category_id" class="block text-gray-700 text-sm font-bold mb-2">Kategori</label>
                <select name="category_id" id="category_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-4">
                <label for="priority_id" class="block text-gray-700 text-sm font-bold mb-2">Prioritas</label>
                <select name="priority_id" id="priority_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    @foreach ($priorities as $priority)
                        <option value="{{ $priority->id }}">{{ $priority->name }}</option>
                    @endforeach
                </select>
            </div>
            <!-- 1 ticket bisa punya banyak label -->
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Label</label>
                <select id="label-dropdown" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    <option value="">-- Pilih Label --</option>
                    @foreach ($labels as $label)
                        <option value="{{ $label->id }}" data-name="{{ $label->name }}">{{ $label->name }}</option>
                    @endforeach
                </select>
                <div id="label-tags" class="flex flex-wrap gap-2 mt-2"></div>
            </div>

            <script>
                const dropdown = document.getElementById('label-dropdown');
                const tagsContainer = document.getElementById('label-tags');

                dropdown.addEventListener('change', function () {
                    const val = this.value;
                    const name = this.options[this.selectedIndex].dataset.name;
                    if (!val) return;

                    // buat tag
                    const tag = document.createElement('span');
                    tag.className = 'flex items-center gap-1 px-2 py-1 border rounded bg-green-50 text-sm';
                    tag.innerHTML = `${name} <button type="button" data-val="${val}">x</button>`;

                    // hidden input buat form submit
                    const hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = 'label_ids[]';
                    hidden.value = val;
                    hidden.id = `hidden-label-${val}`;
                    tag.appendChild(hidden);

                    // tombol X: hapus tag  kembalikan opsi ke dropdown
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

                    // hapus opsi dari dropdown supaya tidak dobel
                    this.querySelector(`option[value="${val}"]`).remove();
                    this.value = '';
                });
            </script>

            <!-- attachment itu kayak input file gitu -->
            <div class="mb-4">
                <label for="attachments" class="block text-gray-700 text-sm font-bold mb-2">Lampiran</label>
                <input type="file" name="attachments[]" id="attachments" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" multiple>
            </div>
            <div class="flex items-center justify-between">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</x-app-layout>