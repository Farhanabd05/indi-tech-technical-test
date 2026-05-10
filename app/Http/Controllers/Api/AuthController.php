<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        // 1. Jalankan validasi & rate limiting dari Breeze
        $request->authenticate();

        // 2. Ambil data user
        $user = User::where('email', $request->email)->first();

        // 3. Buat token baru untuk sesi API ini
        $token = $user->createToken('api-auth-token')->plainTextToken;

        // 4. Kirim respon JSON berisi token dan data dasar user
        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'role' => $user->role?->slug,
            ]
        ]);
    }
}