<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'nullable|string',
            'email' => 'nullable|string',
            'login' => 'nullable|string',
            'password' => 'required|string',
        ]);

        $loginInput = $request->input('username') ?? $request->input('login') ?? $request->input('email');

        if (empty($loginInput)) {
            throw ValidationException::withMessages([
                'username' => ['Username atau email wajib diisi.'],
            ]);
        }

        $user = User::with('satker')
            ->where(function ($query) use ($loginInput) {
                $query->where('username', $loginInput)
                      ->orWhere('email', $loginInput);
            })
            ->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'username' => ['Kombinasi username/email dan password tidak sesuai.'],
            ]);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'username' => ['Akun Anda sedang dinonaktifkan. Silakan hubungi Super Admin.'],
            ]);
        }

        // Revoke existing tokens for single session if desired, or create new
        $token = $user->createToken('bakayuh_auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil.',
            'data' => [
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username,
                    'email' => $user->email,
                    'role' => $user->role->value,
                    'role_label' => $user->role->label(),
                    'satker_id' => $user->satker_id,
                    'satker' => $user->satker,
                    'is_active' => $user->is_active,
                ],
            ],
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user()->load('satker');

        return response()->json([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->role->value,
                'role_label' => $user->role->label(),
                'satker_id' => $user->satker_id,
                'satker' => $user->satker,
                'is_active' => $user->is_active,
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Berhasil logout.',
        ]);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        if (! Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Password saat ini tidak sesuai.'],
            ]);
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return response()->json([
            'message' => 'Password berhasil diperbarui.',
        ]);
    }
}