<?php

namespace App\Http\Controllers;

use App\Models\Penilaian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PenilaianController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Penilaian::with([
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
            ->when($request->has('min_nilai'), function ($q) use ($request) {
                $q->where('nilai', '>=', $request->min_nilai);
            })
            ->when($request->has('max_nilai'), function ($q) use ($request) {
                $q->where('nilai', '<=', $request->max_nilai);
            })
            ->orderByDesc('created_at');

        return response()->json([
            'data' => $query->paginate($request->per_page ?? 15),
            'message' => 'Data penilaian berhasil diambil'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_ta' => [
                'required',
                'exists:tugas_akhir,id',
                Rule::unique('penilaian')->where(function ($query) use ($request) {
                    return $query->where('id_dosen', $request->id_dosen);
                })
            ],
            'id_dosen' => 'required|exists:dosen_pembimbing,id_pembimbing',
            'nilai' => 'required|numeric|min:0|max:100',
            'komentar' => 'nullable|string|max:500',
            'tanggal_penilaian' => 'sometimes|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
                'message' => 'Validasi gagal'
            ], 422);
        }

        $data = $validator->validated();
        $data['tanggal_penilaian'] = $data['tanggal_penilaian'] ?? now();

        $penilaian = Penilaian::create($data);

        return response()->json([
            'data' => $penilaian->load(['tugasAkhir', 'dosen']),
            'message' => 'Penilaian berhasil disimpan'
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Penilaian $penilaian)
    {
        return response()->json([
            'data' => $penilaian->load([
                'tugasAkhir',
                'dosen',
                'tugasAkhir.mahasiswa',
                'tugasAkhir.pembimbing'
            ]),
            'message' => 'Data penilaian berhasil diambil'
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Penilaian $penilaian)
    {
        $validator = Validator::make($request->all(), [
            'nilai' => 'sometimes|numeric|min:0|max:100',
            'komentar' => 'nullable|string|max:500',
            'tanggal_penilaian' => 'sometimes|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
                'message' => 'Validasi gagal'
            ], 422);
        }

        $penilaian->update($validator->validated());

        return response()->json([
            'data' => $penilaian->fresh(),
            'message' => 'Penilaian berhasil diperbarui'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Penilaian $penilaian)
    {
        $penilaian->delete();

        return response()->json([
            'message' => 'Penilaian berhasil dihapus'
        ]);
    }
}