<?php

namespace App\Http\Controllers;

use App\Models\Informasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class InformasiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Informasi::query()
            ->when($request->has('search'), function($q) use ($request) {
                $q->where('judul', 'like', '%'.$request->search.'%')
                  ->orWhere('konten', 'like', '%'.$request->search.'%');
            })
            ->when($request->has('sort'), function($q) use ($request) {
                $q->orderBy($request->sort, $request->order ?? 'desc');
            }, function($q) {
                $q->latest();
            });

        if ($request->has('per_page')) {
            $informasi = $query->paginate($request->per_page);
        } else {
            $informasi = $query->get();
        }

        return response()->json([
            'data' => $informasi,
            'message' => 'Data informasi berhasil diambil'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'judul' => 'required|string|max:255',
            'konten' => 'required|string',
            'tanggal' => 'required|date|date_format:Y-m-d',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_pinned' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
                'message' => 'Validasi gagal'
            ], 422);
        }

        $data = $validator->validated();
        
        // Generate slug dari judul
        $data['slug'] = Str::slug($data['judul']);

        // Handle upload gambar jika ada
        if ($request->hasFile('gambar')) {
            $path = $request->file('gambar')->store('informasi', 'public');
            $data['gambar'] = $path;
        }

        $informasi = Informasi::create($data);

        return response()->json([
            'data' => $informasi,
            'message' => 'Informasi berhasil dibuat'
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $informasi = Informasi::findOrFail($id);

        return response()->json([
            'data' => $informasi,
            'message' => 'Detail informasi berhasil diambil'
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $informasi = Informasi::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'judul' => 'sometimes|string|max:255',
            'konten' => 'sometimes|string',
            'tanggal' => 'sometimes|date|date_format:Y-m-d',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_pinned' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
                'message' => 'Validasi gagal'
            ], 422);
        }

        $data = $validator->validated();

        // Update slug jika judul diubah
        if ($request->has('judul')) {
            $data['slug'] = Str::slug($data['judul']);
        }

        // Handle update gambar
        if ($request->hasFile('gambar')) {
            // Hapus gambar lama jika ada
            if ($informasi->gambar) {
                Storage::disk('public')->delete($informasi->gambar);
            }
            
            $path = $request->file('gambar')->store('informasi', 'public');
            $data['gambar'] = $path;
        }

        $informasi->update($data);

        return response()->json([
            'data' => $informasi->fresh(),
            'message' => 'Informasi berhasil diperbarui'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $informasi = Informasi::findOrFail($id);

        // Hapus gambar terkait jika ada
        if ($informasi->gambar) {
            Storage::disk('public')->delete($informasi->gambar);
        }

        $informasi->delete();

        return response()->json([
            'message' => 'Informasi berhasil dihapus'
        ]);
    }
}