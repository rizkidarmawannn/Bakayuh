<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\IndikatorKinerja;
use App\Enums\PolaritasIku;
use App\Enums\LevelIku;
use Illuminate\Validation\Rules\Enum;

class IndikatorKinerjaController extends Controller
{
    public function index(Request $request)
    {
        $query = IndikatorKinerja::query();

        if ($request->filled('level')) {
            $query->where('level', $request->level);
        }

        $data = $query->orderBy('kode')->get();

        return response()->json(['data' => $data]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode' => 'required|string|max:30|unique:indikator_kinerja,kode',
            'nama' => 'required|string|max:255',
            'satuan' => 'required|string|max:50',
            'polaritas' => ['required', new Enum(PolaritasIku::class)],
            'level' => ['required', new Enum(LevelIku::class)],
        ]);

        $iku = IndikatorKinerja::create($validated);

        return response()->json(['data' => $iku, 'message' => 'Indikator kinerja berhasil ditambahkan.'], 201);
    }

    public function show(IndikatorKinerja $indikatorKinerja)
    {
        return response()->json(['data' => $indikatorKinerja]);
    }

    public function update(Request $request, IndikatorKinerja $indikatorKinerja)
    {
        $validated = $request->validate([
            'kode' => 'required|string|max:30|unique:indikator_kinerja,kode,' . $indikatorKinerja->id,
            'nama' => 'required|string|max:255',
            'satuan' => 'required|string|max:50',
            'polaritas' => ['required', new Enum(PolaritasIku::class)],
            'level' => ['required', new Enum(LevelIku::class)],
        ]);

        $indikatorKinerja->update($validated);

        return response()->json(['data' => $indikatorKinerja, 'message' => 'Indikator kinerja berhasil diperbarui.']);
    }

    public function destroy(IndikatorKinerja $indikatorKinerja)
    {
        $indikatorKinerja->delete();

        return response()->json(['message' => 'Indikator kinerja berhasil dihapus.']);
    }
}