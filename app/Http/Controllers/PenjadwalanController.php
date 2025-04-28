<?php

namespace App\Http\Controllers;

use App\Models\Penjadwalan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class PenjadwalanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Penjadwalan::with([
                'tugasAkhir:id,judul,mahasiswa_id',
                'dosenPembimbing:id,nama,nidn',
                'dosenPenguji:id,nama,nidn',
                'tugasAkhir.mahasiswa:id,nama,nim'
            ])
            ->when($request->has('ta_id'), function ($q) use ($request) {
                $q->where('id_ta', $request->ta_id);
            })
            ->when($request->has('dosen_id'), function ($q) use ($request) {
                $q->where(function($query) use ($request) {
                    $query->where('id_dospem', $request->dosen_id)
                          ->orWhere('id_dospeng', $request->dosen_id);
                });
            })
            ->when($request->has('tanggal'), function ($q) use ($request) {
                $q->whereDate('tanggal', $request->tanggal);
            })
            ->when($request->has('status'), function ($q) use ($request) {
                $q->where('status', $request->status);
            })
            ->orderBy('tanggal')
            ->orderBy('jam');

        return response()->json([
            'data' => $query->paginate($request->per_page ?? 15),
            'message' => 'Data penjadwalan berhasil diambil'
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
                Rule::unique('penjadwalan')->where(function ($query) {
                    return $query->whereNull('deleted_at');
                })
            ],
            'id_dospem' => 'required|exists:dosen_pembimbing,id_pembimbing',
            'id_dospeng' => [
                'required',
                'exists:dosen_penguji,id_penguji',
                'different:id_dospem'
            ],
            'tanggal' => [
                'required',
                'date',
                'after_or_equal:today'
            ],
            'jam' => [
                'required',
                'date_format:H:i',
                function ($attribute, $value, $fail) use ($request) {
                    $waktuSidang = Carbon::parse($request->tanggal.' '.$value);
                    if ($waktuSidang->isWeekend()) {
                        $fail('Tidak bisa menjadwalkan di hari weekend');
                    }
                    if ($waktuSidang->hour < 8 || $waktuSidang->hour > 17) {
                        $fail('Jam sidang hanya bisa antara 08:00 - 17:00');
                    }
                }
            ],
            'ruangan' => 'required|string|max:255',
            'status' => 'sometimes|in:terjadwal,berlangsung,selesai,dibatalkan'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
                'message' => 'Validasi gagal'
            ], 422);
        }

        $data = $validator->validated();
        $data['status'] = $data['status'] ?? 'terjadwal';

        $penjadwalan = Penjadwalan::create($data);

        return response()->json([
            'data' => $penjadwalan->load(['tugasAkhir', 'dosenPembimbing', 'dosenPenguji']),
            'message' => 'Jadwal berhasil dibuat'
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Penjadwalan $penjadwalan)
    {
        return response()->json([
            'data' => $penjadwalan->load([
                'tugasAkhir',
                'dosenPembimbing',
                'dosenPenguji',
                'tugasAkhir.mahasiswa',
                'tugasAkhir.pembimbing'
            ]),
            'message' => 'Data penjadwalan berhasil diambil'
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Penjadwalan $penjadwalan)
    {
        $validator = Validator::make($request->all(), [
            'id_dospem' => [
                'sometimes',
                'required',
                'exists:dosen_pembimbing,id_pembimbing',
                Rule::notIn([$penjadwalan->id_dospeng])
            ],
            'id_dospeng' => [
                'sometimes',
                'required',
                'exists:dosen_penguji,id_penguji',
                Rule::notIn([$penjadwalan->id_dospem])
            ],
            'tanggal' => [
                'sometimes',
                'required',
                'date',
                'after_or_equal:today'
            ],
            'jam' => [
                'sometimes',
                'required',
                'date_format:H:i',
                function ($attribute, $value, $fail) use ($request) {
                    $waktuSidang = Carbon::parse($request->tanggal.' '.$value);
                    if ($waktuSidang->isWeekend()) {
                        $fail('Tidak bisa menjadwalkan di hari weekend');
                    }
                    if ($waktuSidang->hour < 8 || $waktuSidang->hour > 17) {
                        $fail('Jam sidang hanya bisa antara 08:00 - 17:00');
                    }
                }
            ],
            'ruangan' => 'sometimes|required|string|max:255',
            'status' => 'sometimes|required|in:terjadwal,berlangsung,selesai,dibatalkan'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
                'message' => 'Validasi gagal'
            ], 422);
        }

        $penjadwalan->update($validator->validated());

        return response()->json([
            'data' => $penjadwalan->fresh(),
            'message' => 'Jadwal berhasil diperbarui'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Penjadwalan $penjadwalan)
    {
        $penjadwalan->delete();

        return response()->json([
            'message' => 'Jadwal berhasil dihapus'
        ]);
    }
}