<?php

namespace App\Http\Controllers;

use App\Models\Dospem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class DospemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Dospem::with(['user:id,name,email', 'bimbingans'])
            ->when($request->has('search'), function ($q) use ($request) {
                $q->where('nama', 'like', '%'.$request->search.'%')
                  ->orWhere('nidn', 'like', '%'.$request->search.'%')
                  ->orWhere('email', 'like', '%'.$request->search.'%');
            })
            ->orderBy('nama');

        return response()->json([
            'data' => $query->paginate($request->per_page ?? 15),
            'message' => 'Dosen pembimbing retrieved successfully'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_pengguna' => 'required|exists:users,id|unique:dosen_pembimbing,id_pengguna',
            'nidn' => 'required|unique:dosen_pembimbing,nidn|max:20',
            'nama' => 'required|string|max:100',
            'no_telp' => 'nullable|string|max:15|regex:/^[0-9]+$/',
            'email' => [
                'required',
                'email',
                'max:100',
                Rule::unique('dosen_pembimbing', 'email'),
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

        $pembimbing = Dospem::create($validator->validated());

        return response()->json([
            'data' => $pembimbing->load('user'),
            'message' => 'Dosen pembimbing created successfully'
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Dospem $dosenPembimbing)
    {
        return response()->json([
            'data' => $dosenPembimbing->load(['user', 'bimbingans.tugasAkhir']),
            'message' => 'Dosen pembimbing retrieved successfully'
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Dospem $dospem)
    {
        $validator = Validator::make($request->all(), [
            'nidn' => [
                'sometimes',
                'required',
                'max:20',
                Rule::unique('dosen_pembimbing')->ignore($dospem->id),
            ],
            'nama' => 'sometimes|required|string|max:100',
            'no_telp' => 'nullable|string|max:15|regex:/^[0-9]+$/',
            'email' => [
                'sometimes',
                'required',
                'email',
                'max:100',
                Rule::unique('dosen_pembimbing', 'email')->ignore($dospem->id),
                Rule::unique('users', 'email')->ignore($dospem->id_pengguna)
            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
                'message' => 'Validation failed'
            ], 422);
        }

        $dospem->update($validator->validated());

        return response()->json([
            'data' => $dospem->fresh(),
            'message' => 'Dosen pembimbing updated successfully'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Dospem $dospem)
    {
        $dospem->delete();

        return response()->json([
            'message' => 'Dosen pembimbing deleted successfully'
        ]);
    }
}