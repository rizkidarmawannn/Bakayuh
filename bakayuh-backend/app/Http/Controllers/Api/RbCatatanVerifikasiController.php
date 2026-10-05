<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RbCatatanVerifikasi;
use App\Models\RbTargetPeriode;

class RbCatatanVerifikasiController extends Controller
{
    /**
     * List clarification messages for a target periode
     */
    public function index(Request $request)
    {
        $request->validate([
            'rb_target_periode_id' => 'required|exists:rb_target_periode,id',
        ]);

        $notes = RbCatatanVerifikasi::with('sender')
            ->where('rb_target_periode_id', $request->rb_target_periode_id)
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json(['data' => $notes]);
    }

    /**
     * Send clarification message
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'rb_target_periode_id' => 'required|exists:rb_target_periode,id',
            'pesan' => 'required|string',
        ]);

        $target = RbTargetPeriode::findOrFail($validated['rb_target_periode_id']);
        $user = $request->user();

        if ($user->isOperatorSatker() && $user->satker_id !== $target->satker_id) {
            abort(403, 'Akses ditolak.');
        }

        $note = RbCatatanVerifikasi::create([
            'rb_target_periode_id' => $target->id,
            'sender_id' => $user->id,
            'pesan' => $validated['pesan'],
        ]);

        return response()->json([
            'data' => $note->load('sender'),
            'message' => 'Catatan klarifikasi terkirim.',
        ], 201);
    }
}
