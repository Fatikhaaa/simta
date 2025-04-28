<?php

namespace App\Http\Controllers;

use App\Models\dosen_penguji;
use App\Models\DosenPenguji;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class DosenPengujiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = DosenPenguji::with(['user:id,name,email', 'ujians'])
            ->when($request->has('search'), function ($q) use ($request) {
                $q->where(function($query) use ($request) {
                    $query->where('nama', 'like', '%'.$request->search.'%')
                          ->orWhere('nidn', 'like', '%'.$request->search.'%')
                          ->orWhere('email', 'like', '%'.$request->search.'%');
                });
            })
            ->orderBy('nama');

        return response()->json([
            'data' => $query->paginate($request->per_page ?? 15),
            'message' => 'Dosen penguji retrieved successfully'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_pengguna' => 'required|exists:users,id|unique:dosen_penguji,id_pengguna',
            'nidn' => 'required|unique:dosen_penguji,nidn|max:20',
            'nama' => 'required|string|max:100',
            'no_telp' => 'nullable|string|max:15|regex:/^[0-9]+$/',
            'email' => [
                'required',
                'email',
                'max:100',
                Rule::unique('dosen_penguji', 'email'),
                Rule::unique('users', 'email')->where(function ($query) use ($request) {
                    return $query->where('id', '!=', $request->id_pengguna);
                })
            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
                'message' => 'Validation failed'
            ], 422);
        }

        $penguji = DosenPenguji::create($validator->validated());

        return response()->json([
            'data' => $penguji->load('user'),
            'message' => 'Dosen penguji created successfully'
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(DosenPenguji $dosenPenguji)
    {
        return response()->json([
            'data' => $dosenPenguji->load(['user', 'ujians.tugasAkhir']),
            'message' => 'Dosen penguji retrieved successfully'
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DosenPenguji $dosenPenguji)
    {
        $validator = Validator::make($request->all(), [
            'nidn' => [
                'sometimes',
                'required',
                'max:20',
                Rule::unique('dosen_penguji')->ignore($dosenPenguji->id),
            ],
            'nama' => 'sometimes|required|string|max:100',
            'no_telp' => 'nullable|string|max:15|regex:/^[0-9]+$/',
            'email' => [
                'sometimes',
                'required',
                'email',
                'max:100',
                Rule::unique('dosen_penguji', 'email')->ignore($dosenPenguji->id),
                Rule::unique('users', 'email')->ignore($dosenPenguji->id_pengguna)
            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
                'message' => 'Validation failed'
            ], 422);
        }

        $dosenPenguji->update($validator->validated());

        return response()->json([
            'data' => $dosenPenguji->fresh(),
            'message' => 'Dosen penguji updated successfully'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DosenPenguji $dosenPenguji)
    {
        $dosenPenguji->delete();

        return response()->json([
            'message' => 'Dosen penguji deleted successfully'
        ]);
    }
}