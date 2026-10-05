<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TahunAnggaran;
use Illuminate\Support\Facades\DB;

class TahunAnggaranController extends Controller
{
    public function index()
    {
        $tahuns = TahunAnggaran::orderBy('tahun', 'desc')->get();
        return response()->json(['data' => $tahuns]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tahun' => 'required|integer|digits:4|unique:tahun_anggaran,tahun',
            'is_aktif' => 'boolean',
        ]);

        if (!empty($validated['is_aktif'])) {
            TahunAnggaran::where('is_aktif', true)->update(['is_aktif' => false]);
        }

        $tahun = TahunAnggaran::create($validated);

        return response()->json(['data' => $tahun, 'message' => 'Tahun anggaran berhasil ditambahkan.'], 201);
    }

    public function setAktif(TahunAnggaran $tahun)
    {
        DB::transaction(function () use ($tahun) {
            TahunAnggaran::where('is_aktif', true)->update(['is_aktif' => false]);
            $tahun->update(['is_aktif' => true]);
        });

        return response()->json(['data' => $tahun, 'message' => 'Tahun anggaran aktif berhasil diubah.']);
    }
}