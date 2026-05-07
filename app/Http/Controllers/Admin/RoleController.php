<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Http\Requests\Role\StoreRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::all(); // Ambil semua data role dari database
        return view('admin.roles.index', compact('roles')); // Lempar data ke view menggunakan compact
    }


    public function store(StoreRoleRequest $request)
    {
        // Data sudah tervalidasi oleh StoreRoleRequest
        $validated = $request->validated();

        // Buat role baru menggunakan data yang sudah tervalidasi
        Role::create($validated);

        // Redirect kembali ke halaman index role dengan pesan sukses
        return redirect()->route('admin.roles.index')
                         ->with('success', 'Role berhasil dibuat.');
    }

    public function update(UpdateRoleRequest $request, Role $role)
    {
        // Data sudah tervalidasi oleh UpdateRoleRequest
        $validated = $request->validated();

        // Perbarui role yang sudah ada menggunakan data yang sudah tervalidasi
        $role->update($validated);

        // Redirect kembali ke halaman index role dengan pesan sukses
        return redirect()->route('admin.roles.index')
                         ->with('success', 'Role berhasil diperbarui.');
    }

    public function destroy(Role $role)
    {
        // Periksa apakah ada pengguna yang terkait dengan peran ini
        if (\App\Models\User::where('role_id', $role->id)->exists()) {
            // Jika ada pengguna yang terkait, tolak penghapusan dan arahkan kembali dengan pesan galat
            return redirect()->route('admin.roles.index')
                             ->with('error', 'Tidak dapat menghapus role ini karena masih ada pengguna yang terkait.');
        }

        // Jika tidak ada pengguna yang terkait, hapus role tersebut
        $role->delete();

        // Arahkan kembali ke halaman indeks dengan pesan sukses
        return redirect()->route('admin.roles.index')
                         ->with('success', 'Role berhasil dihapus.');
    }

    public function create()
    {
        return view('admin.roles.create'); // Tampilkan formulir untuk membuat role baru
    }

    public function edit(Role $role)
    {
        return view('admin.roles.edit', compact('role')); // Tampilkan formulir untuk mengedit role yang sudah ada
    }
}
