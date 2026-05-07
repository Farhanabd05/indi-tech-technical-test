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
use Illuminate\Support\Facades\Auth;

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

        if (Auth::id() === $user->id) {
            return redirect()->route('admin.users.index')
                            ->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        // Jika tidak ada tiket yang terkait, hapus pengguna tersebut
        $user->delete();

        // Arahkan kembali ke halaman indeks dengan pesan sukses
        return redirect()->route('admin.users.index')
                         ->with('success', 'Pengguna berhasil dihapus.');
    }

    public function index()
    {
        $users = \App\Models\User::with(['role', 'team'])->paginate(10);
        return view('admin.users.index', compact('users'));
    }


    public function create()
    {
        $roles = \App\Models\Role::all();
        $teams = \App\Models\Team::all();
        return view('admin.users.create', compact('roles', 'teams'));
    }

    public function store(StoreUserRequest $request)
    {
        // Validasi data menggunakan StoreUserRequest
        $validated = $request->validated();

        // Pembersihan: Jika Role Admin (1) atau Customer (4), pastikan team_id NULL
        if (in_array($validated['role_id'], [1, 4])) {
            $validated['team_id'] = null;
        }

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

        // Proteksi: Mencegah perubahan Role/Team milik diri sendiri agar tidak kehilangan akses admin
        if (Auth::id() === $user->id) {
            unset($validated['role_id'], $validated['team_id']);
        } else {
            // Pembersihan: Jika Role Admin (1) atau Customer (4), paksa team_id NULL
            if (isset($validated['role_id']) && in_array($validated['role_id'], [1, 4])) {
                $validated['team_id'] = null;
            }
        }

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

    public function edit(\App\Models\User $user)
    {
        $roles = \App\Models\Role::all();
        $teams = \App\Models\Team::all();
        return view('admin.users.edit', compact('user', 'roles', 'teams'));
    }
}
