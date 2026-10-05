<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SatuanKerja;
use App\Enums\TipeSatker;
use Illuminate\Validation\Rules\Enum;

class SatuanKerjaController extends Controller
{
    public function index(Request $request)
    {
        $query = SatuanKerja::query();

        if ($request->filled('tipe')) {
            $query->where('tipe', $request->tipe);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('kode', 'like', "%{$search}%");
            });
        }

        $satkers = $query->orderBy('tipe')->orderBy('nama')->get();

        return response()->json(['data' => $satkers]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode' => 'required|string|max:30|unique:satuan_kerja,kode',
            'nama' => 'required|string|max:255',
            'tipe' => ['required', new Enum(TipeSatker::class)],
            'is_active' => 'boolean',
        ]);

        $satker = SatuanKerja::create($validated);

        return response()->json(['data' => $satker, 'message' => 'Satuan kerja berhasil ditambahkan.'], 201);
    }

    public function show(SatuanKerja $satker)
    {
        return response()->json(['data' => $satker]);
    }

    public function update(Request $request, SatuanKerja $satker)
    {
        $validated = $request->validate([
            'kode' => 'required|string|max:30|unique:satuan_kerja,kode,' . $satker->id,
            'nama' => 'required|string|max:255',
            'tipe' => ['required', new Enum(TipeSatker::class)],
            'is_active' => 'boolean',
        ]);

        $satker->update($validated);

        return response()->json(['data' => $satker, 'message' => 'Satuan kerja berhasil diperbarui.']);
    }

    public function destroy(SatuanKerja $satker)
    {
        $satker->delete();

        return response()->json(['message' => 'Satuan kerja berhasil dihapus.']);
    }
}