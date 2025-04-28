<?php

namespace App\Http\Controllers;

use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MahasiswaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Mahasiswa::with(['user:id,name,email', 'tugasAkhir'])
            ->when($request->has('search'), function ($q) use ($request) {
                $q->where('nama', 'like', '%'.$request->search.'%')
                  ->orWhere('nim', 'like', '%'.$request->search.'%')
                  ->orWhere('program_studi', 'like', '%'.$request->search.'%');
            })
            ->orderBy('nama');

        if ($request->wantsJson()) {
            return response()->json($query->paginate($request->per_page ?? 15));
        }

        return view('mahasiswa.index', [
            'mahasiswa' => $query->paginate(10),
            'search' => $request->search ?? ''
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_pengguna' => 'required|exists:users,id|unique:mahasiswa,id_pengguna',
            'nim' => 'required|unique:mahasiswa,nim|max:15',
            'nama' => 'required|string|max:100',
            'program_studi' => 'required|string|max:100',
            'no_telp' => 'nullable|string|max:15|regex:/^[0-9]+$/',
        ]);

        $mahasiswa = Mahasiswa::create($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Mahasiswa created successfully',
                'data' => $mahasiswa->load('user')
            ], 201);
        }

        return redirect()->route('mahasiswa.index')
            ->with('success', 'Mahasiswa berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Mahasiswa $mahasiswa)
    {
        $mahasiswa->load(['user', 'tugasAkhir']);

        if ($request->wantsJson()) {
            return response()->json([
                'data' => $mahasiswa
            ]);
        }

        return view('mahasiswa.show', compact('mahasiswa'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Mahasiswa $mahasiswa)
    {
        $validated = $request->validate([
            'nim' => [
                'sometimes',
                'required',
                'max:15',
                Rule::unique('mahasiswa')->ignore($mahasiswa->id),
            ],
            'nama' => 'sometimes|required|string|max:100',
            'program_studi' => 'sometimes|required|string|max:100',
            'no_telp' => 'nullable|string|max:15|regex:/^[0-9]+$/',
        ]);

        $mahasiswa->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Mahasiswa updated successfully',
                'data' => $mahasiswa->fresh()
            ]);
        }

        return redirect()->route('mahasiswa.index')
            ->with('success', 'Data mahasiswa diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Mahasiswa $mahasiswa)
    {
        $mahasiswa->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Mahasiswa deleted successfully'
            ]);
        }

        return redirect()->route('mahasiswa.index')
            ->with('success', 'Mahasiswa berhasil dihapus');
    }
}