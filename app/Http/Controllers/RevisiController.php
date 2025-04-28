<?php

namespace App\Http\Controllers;

use App\Models\Revisi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class RevisiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Revisi::with([
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
            ->orderByDesc('created_at');

        return response()->json([
            'data' => $query->paginate($request->per_page ?? 15),
            'message' => 'Data revisi berhasil diambil'
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
            'catatan_revisi' => 'required|string|max:1000',
            'status' => [
                'nullable', 
                Rule::in(['Menunggu Verifikasi', 'Disetujui', 'Ditolak', 'Selesai'])
            ],
            'deadline' => 'required|date|after_or_equal:today',
            'file_revisi' => 'sometimes|file|mimes:pdf,doc,docx|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
                'message' => 'Validasi gagal'
            ], 422);
        }

        $data = $validator->validated();
        $data['status'] = $data['status'] ?? 'Menunggu Verifikasi';

        // Handle file upload jika ada
        if ($request->hasFile('file_revisi')) {
            $data['file_path'] = $request->file('file_revisi')->store('revisi_files');
        }

        $revisi = Revisi::create($data);

        return response()->json([
            'data' => $revisi->load(['tugasAkhir', 'dosen']),
            'message' => 'Revisi berhasil disimpan'
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Revisi $revisi)
    {
        return response()->json([
            'data' => $revisi->load([
                'tugasAkhir',
                'dosen',
                'tugasAkhir.mahasiswa',
                'tugasAkhir.pembimbing'
            ]),
            'message' => 'Data revisi berhasil diambil'
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Revisi $revisi)
    {
        $validator = Validator::make($request->all(), [
            'catatan_revisi' => 'sometimes|string|max:1000',
            'status' => [
                'sometimes', 
                Rule::in(['Menunggu Verifikasi', 'Disetujui', 'Ditolak', 'Selesai'])
            ],
            'deadline' => 'sometimes|date|after_or_equal:today',
            'file_revisi' => 'sometimes|file|mimes:pdf,doc,docx|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
                'message' => 'Validasi gagal'
            ], 422);
        }

        $data = $validator->validated();

        // Handle file upload jika ada
        if ($request->hasFile('file_revisi')) {
            // Hapus file lama jika ada
            if ($revisi->file_path) {
                Storage::delete($revisi->file_path);
            }
            $data['file_path'] = $request->file('file_revisi')->store('revisi_files');
        }

        $revisi->update($data);

        return response()->json([
            'data' => $revisi->fresh(),
            'message' => 'Revisi berhasil diperbarui'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Revisi $revisi)
    {
        // Hapus file terkait jika ada
        if ($revisi->file_path) {
            Storage::delete($revisi->file_path);
        }

        $revisi->delete();

        return response()->json([
            'message' => 'Revisi berhasil dihapus'
        ]);
    }
}