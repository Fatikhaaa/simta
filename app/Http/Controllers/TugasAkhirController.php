<?php

namespace App\Http\Controllers;

use App\Models\tugas_akhir;
use App\Models\TugasAkhir;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class TugasAkhirController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = TugasAkhir::with([
                'mahasiswa:id_mahasiswa,nama,nim',
                'dosenPembimbing:id_pembimbing,nama,nidn',
                'dosenPenguji:id,nama,nidn',
                'penilaian'
            ])
            ->when($request->has('mahasiswa_id'), function ($q) use ($request) {
                $q->where('id_mahasiswa', $request->mahasiswa_id);
            })
            ->when($request->has('pembimbing_id'), function ($q) use ($request) {
                $q->where('id_pembimbing', $request->pembimbing_id);
            })
            ->when($request->has('status'), function ($q) use ($request) {
                $q->where('status', $request->status);
            })
            ->when($request->has('search'), function ($q) use ($request) {
                $q->where('judul', 'like', '%'.$request->search.'%')
                  ->orWhereHas('mahasiswa', function($query) use ($request) {
                      $query->where('nama', 'like', '%'.$request->search.'%')
                            ->orWhere('nim', 'like', '%'.$request->search.'%');
                  });
            })
            ->orderByDesc('created_at');

        return response()->json([
            'data' => $query->paginate($request->per_page ?? 15),
            'message' => 'Data tugas akhir berhasil diambil'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_mahasiswa' => [
                'required',
                'exists:mahasiswa,id_mahasiswa',
                Rule::unique('tugas_akhir')->where(function ($query) {
                    return $query->whereNull('deleted_at');
                })
            ],
            'id_pembimbing' => 'required|exists:dosen_pembimbing,id_pembimbing',
            'id_penguji' => 'required|exists:dosen_penguji,id',
            'judul' => 'required|string|max:255',
            'abstrak' => 'required|string|min:100|max:2000',
            'file_laporan' => 'nullable|file|mimes:pdf,doc,docx|max:10240', // 10MB
            'status' => 'nullable|in:proposal,seminar,ujian,lulus,revisi'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
                'message' => 'Validasi gagal'
            ], 422);
        }

        $data = $validator->validated();

        // Handle file upload
        if ($request->hasFile('file_laporan')) {
            $path = $request->file('file_laporan')->store('laporan_ta');
            $data['file_laporan'] = $path;
        }

        $ta = TugasAkhir::create($data);

        return response()->json([
            'data' => $ta->load(['mahasiswa', 'dosenPembimbing', 'dosenPenguji']),
            'message' => 'Tugas akhir berhasil dibuat'
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(TugasAkhir $tugasAkhir)
    {
        return response()->json([
            'data' => $tugasAkhir->load([
                'mahasiswa',
                'dosenPembimbing',
                'dosenPenguji',
                'penilaian',
                'bimbingan'
            ]),
            'message' => 'Data tugas akhir berhasil diambil'
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TugasAkhir $tugasAkhir)
    {
        $validator = Validator::make($request->all(), [
            'id_pembimbing' => 'sometimes|exists:dosen_pembimbing,id_pembimbing',
            'id_penguji' => 'sometimes|exists:dosen_penguji,id',
            'judul' => 'sometimes|string|max:255',
            'abstrak' => 'sometimes|string|min:100|max:2000',
            'file_laporan' => 'sometimes|file|mimes:pdf,doc,docx|max:10240',
            'status' => 'sometimes|in:proposal,seminar,ujian,lulus,revisi'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
                'message' => 'Validasi gagal'
            ], 422);
        }

        $data = $validator->validated();

        // Handle file update
        if ($request->hasFile('file_laporan')) {
            // Delete old file if exists
            if ($tugasAkhir->file_laporan) {
                Storage::delete($tugasAkhir->file_laporan);
            }
            $path = $request->file('file_laporan')->store('laporan_ta');
            $data['file_laporan'] = $path;
        }

        $tugasAkhir->update($data);

        return response()->json([
            'data' => $tugasAkhir->fresh(),
            'message' => 'Tugas akhir berhasil diperbarui'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TugasAkhir $tugasAkhir)
    {
        // Delete associated file
        if ($tugasAkhir->file_laporan) {
            Storage::delete($tugasAkhir->file_laporan);
        }

        $tugasAkhir->delete();

        return response()->json([
            'message' => 'Tugas akhir berhasil dihapus'
        ]);
    }

    /**
     * Download laporan tugas akhir
     */
    public function downloadLaporan(TugasAkhir $tugasAkhir)
    {
        if (!$tugasAkhir->file_laporan || !Storage::exists($tugasAkhir->file_laporan)) {
            return response()->json([
                'message' => 'File laporan tidak ditemukan'
            ], 404);
        }

        return Storage::download($tugasAkhir->file_laporan, 'laporan_ta_'.$tugasAkhir->mahasiswa->nim.'.pdf');
    }
}