<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Validation\Rule;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use Illuminate\Support\Facades\Hash;
use App\Models\Role;
use App\Models\Ticket;
class UserController extends Controller
{
    public function destroy(User $user)
    {
        // Periksa apakah pengguna memiliki tiket yang dibuat atau ditugaskan
        if (Ticket::where('created_by', $user->id)
            ->orWhere('assigned_agent_id', $user->id)
            ->exists()) {
            // Jika ada, tolak penghapusan dan arahkan kembali dengan pesan galat
            return redirect()->route('admin.users.index')
                             ->with('error', 'Tidak dapat menghapus pengguna ini karena masih terkait dengan tiket.');
        }

        // Jika tidak ada tiket yang terkait, hapus pengguna tersebut
        $user->delete();

        // Arahkan kembali ke halaman indeks dengan pesan sukses
        return redirect()->route('admin.users.index')
                         ->with('success', 'Pengguna berhasil dihapus.');
    }

    public function index()
    {
        $users = User::with('role')->get(); // Ambil semua pengguna beserta relasi perannya
        return view('admin.users.index', compact('users')); // Lempar data ke view menggunakan compact
    }

    public function create()
    {
        $roles = Role::all(); // Ambil semua data peran dari database
        return view('admin.users.create', compact('roles')); // Lempar data ke view menggunakan compact
    }

    public function store(StoreUserRequest $request)
    {
        // Validasi data menggunakan StoreUserRequest
        $validated = $request->validated();

        // Enkripsi kata sandi sebelum menyimpan
        $validated['password'] = Hash::make($validated['password']);

        // Buat pengguna baru dengan data yang sudah tervalidasi
        User::create($validated);

        // Redirect kembali ke halaman indeks dengan pesan sukses
        return redirect()->route('admin.users.index')
                         ->with('success', 'Pengguna berhasil dibuat.');
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        // Validasi data menggunakan UpdateUserRequest
        $validated = $request->validated();

        // Periksa apakah kata sandi diisi, jika ya maka enkripsi
        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']); // Hapus kunci password jika tidak diisi agar tidak menimpa nilai lama
        }

        // Perbarui pengguna dengan data yang sudah tervalidasi
        $user->update($validated);

        // Redirect kembali ke halaman indeks dengan pesan sukses
        return redirect()->route('admin.users.index')
                         ->with('success', 'Pengguna berhasil diperbarui.');
    }

    public function edit(User $user)
    {
        $roles = Role::all(); // Ambil semua data peran dari database
        return view('admin.users.edit', compact('user', 'roles')); // Lempar data pengguna dan peran ke view menggunakan compact
    }
}
