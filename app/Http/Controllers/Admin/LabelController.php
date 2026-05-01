<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Label\StoreLabelRequest;
use App\Http\Requests\Label\UpdateLabelRequest;
use App\Models\Label;

class LabelController extends Controller
{
    public function index()
    {
        $labels = Label::all(); // Ambil semua data label dari database
        return view('admin.labels.index', compact('labels')); // Lempar data ke view menggunakan compact
    }

    public function create()
    {
        return view('admin.labels.create'); // Tampilkan formulir untuk membuat label baru
    }

    public function store(StoreLabelRequest $request)
    {
        // Data sudah tervalidasi oleh StoreLabelRequest
        $validated = $request->validated();

        // Buat label baru menggunakan data yang sudah tervalidasi
        Label::create($validated);

        // Redirect kembali ke halaman index label dengan pesan sukses
        return redirect()->route('admin.labels.index')
                         ->with('success', 'Label berhasil dibuat.');
    }

    public function edit(Label $label)
    {
        return view('admin.labels.edit', compact('label')); // Tampilkan formulir untuk mengedit label yang sudah ada
    }

    public function update(UpdateLabelRequest $request, Label $label)
    {
        // Data sudah tervalidasi oleh UpdateLabelRequest
        $validated = $request->validated();

        // Perbarui label yang sudah ada menggunakan data yang sudah tervalidasi
        $label->update($validated);

        // Redirect kembali ke halaman index label dengan pesan sukses
        return redirect()->route('admin.labels.index')
                         ->with('success', 'Label berhasil diperbarui.');
    }

    public function destroy(Label $label)
    {
        // Hapus label tersebut tanpa pengecekan terkait tiket
        $label->delete();

        // Arahkan kembali ke halaman indeks dengan pesan sukses
        return redirect()->route('admin.labels.index')
                         ->with('success', 'Label berhasil dihapus.');
    }
}
