<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Priority;
use App\Http\Requests\Priority\StorePriorityRequest;
use App\Http\Requests\Priority\UpdatePriorityRequest;

class PriorityController extends Controller
{
    public function index()
    {   
        // sort by level
        $priorities = Priority::orderBy('level', 'desc')->paginate(10);
        return view('admin.priorities.index', compact('priorities'));
    }

    // destroy
    public function destroy(Priority $priority)
    {
        $priority->delete();
        return redirect()->route('admin.priorities.index');
    }

    // create
    public function create()
    {
        return view('admin.priorities.create');
    }

    public function store(StorePriorityRequest $request)
    {
        // validasi data menggunakan StorePriorityRequest

        // Data sudah tervalidasi oleh StorePriorityRequest
        $validated = $request->validated();

        // Buat priority baru menggunakan data yang sudah tervalidasi
        Priority::create($validated);

        // Redirect kembali ke halaman index priority dengan pesan sukses
        return redirect()->route('admin.priorities.index')
                         ->with('success', 'Prioritas berhasil dibuat.');
    }

    public function edit(Priority $priority)
    {
        return view('admin.priorities.edit', compact('priority'));
    }

    public function update(UpdatePriorityRequest $request, Priority $priority)
    {
        // validasi data menggunakan UpdatePriorityRequest

        // Data sudah tervalidasi oleh UpdatePriorityRequest
        $validated = $request->validated();

        // Update priority menggunakan data yang sudah tervalidasi
        $priority->update($validated);

        // Redirect kembali ke halaman index priority dengan pesan sukses
        return redirect()->route('admin.priorities.index')
                         ->with('success', 'Prioritas berhasil diperbarui.');
    }
}
