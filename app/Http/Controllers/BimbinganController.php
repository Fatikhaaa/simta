<?php

namespace App\Http\Controllers;

use App\Models\Bimbingan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class BimbinganController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Bimbingan::with([
                'tugasAkhir:id,judul,mahasiswa_id',
                'dosen:id,nama,nidn',
                'tugasAkhir.mahasiswa:id,nama,nim'
            ])
            ->when($request->has('ta_id'), function ($q) use ($request) {
                $q->where('id_ta', $request->ta_id);
            })
            ->when($request->has('dosen_id'), function ($q) use ($request) {
                $q->where('id_dosen', $request->dosen_id);
            })
            ->when($request->has('status'), function ($q) use ($request) {
                $q->where('status', $request->status);
            })
            ->when($request->has('search'), function ($q) use ($request) {
                $q->where('catatan', 'like', '%'.$request->search.'%');
            })
            ->orderByDesc('tanggal');

        return response()->json([
            'data' => $query->paginate($request->per_page ?? 15),
            'message' => 'Data bimbingan berhasil diambil'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_ta' => 'required|exists:tugas_akhir,id',
            'id_dosen' => 'required|exists:dosen_pembimbing,id_pembimbing',
            'tanggal' => 'required|date|before_or_equal:today',
            'catatan' => 'required|string|max:1000',
            'status' => [
                'required', 
                Rule::in(['Menunggu Verifikasi', 'Disetujui', 'Ditolak'])
            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
                'message' => 'Validasi gagal'
            ], 422);
        }

        $bimbingan = Bimbingan::create($validator->validated());

        return response()->json([
            'data' => $bimbingan->load(['tugasAkhir', 'dosen']),
            'message' => 'Bimbingan berhasil disimpan'
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Bimbingan $bimbingan)
    {
        return response()->json([
            'data' => $bimbingan->load([
                'tugasAkhir',
                'dosen',
                'tugasAkhir.mahasiswa',
                'tugasAkhir.pembimbing'
            ]),
            'message' => 'Data bimbingan berhasil diambil'
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Bimbingan $bimbingan)
    {
        $validator = Validator::make($request->all(), [
            'tanggal' => 'sometimes|date|before_or_equal:today',
            'catatan' => 'sometimes|string|max:1000',
            'status' => [
                'sometimes', 
                Rule::in(['Menunggu Verifikasi', 'Disetujui', 'Ditolak'])
            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
                'message' => 'Validasi gagal'
            ], 422);
        }

        $bimbingan->update($validator->validated());

        return response()->json([
            'data' => $bimbingan->fresh(),
            'message' => 'Bimbingan berhasil diperbarui'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Bimbingan $bimbingan)
    {
        $bimbingan->delete();

        return response()->json([
            'message' => 'Bimbingan berhasil dihapus'
        ]);
    }
}