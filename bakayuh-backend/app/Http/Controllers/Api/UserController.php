<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Enum;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('satker');

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('satker_id')) {
            $query->where('satker_id', $request->satker_id);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%");
            });
        }

        $users = $query->orderBy('name')->paginate(15);

        return response()->json($users);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => ['required', new Enum(UserRole::class)],
            'satker_id' => 'nullable|exists:satuan_kerja,id',
            'is_active' => 'boolean',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $user = User::create($validated);

        return response()->json([
            'data' => $user->load('satker'),
            'message' => 'Pengguna berhasil dibuat.',
        ], 201);
    }

    public function show(User $user)
    {
        return response()->json(['data' => $user->load('satker')]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => ['required', new Enum(UserRole::class)],
            'satker_id' => 'nullable|exists:satuan_kerja,id',
            'is_active' => 'boolean',
        ]);

        $user->update($validated);

        return response()->json([
            'data' => $user->load('satker'),
            'message' => 'Pengguna berhasil diperbarui.',
        ]);
    }

    public function toggleActive(User $user)
    {
        $user->update(['is_active' => !$user->is_active]);

        return response()->json([
            'data' => $user,
            'message' => 'Status aktif pengguna berhasil diubah.',
        ]);
    }

    public function resetPassword(Request $request, User $user)
    {
        $validated = $request->validate([
            'password' => 'required|string|min:8',
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json(['message' => 'Password pengguna berhasil direset.']);
    }

    public function destroy(User $user)
    {
        $user->delete();

        return response()->json(['message' => 'Pengguna berhasil dihapus.']);
    }
}