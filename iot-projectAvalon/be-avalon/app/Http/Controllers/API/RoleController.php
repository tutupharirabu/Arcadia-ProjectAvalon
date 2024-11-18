<?php

namespace App\Http\Controllers\API;

use App\Models\Role;
use Illuminate\Http\Request;
use App\Http\Middleware\isAdmin;
use App\Http\Controllers\Controller;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $allRole = Role::all();

        if($allRole->isEmpty()) {
            return response([
                "message" => "Database Role masih kosong",
            ], 204);
        }  else {
            return response([
                "message" => "Tampil data Role berhasil!",
                "data" => $allRole,
            ], 200);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|unique:roles'
        ], [
            'required' => 'Silahkan masukkan :attribute yang sesuai!',
            'unique' => 'Terdapat data dengan nama role yang sama dalam database',
        ]);

        Role::create($validatedData);

        return response([
            "message" => "Tambah Role berhasil!",
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $showData = Role::with('list_user')->find($id);

        if (!$showData) {
            return response([
                "message" => "Data $id tidak ada dalam database~",
            ], 404);
        } else {
            return response([
                "message" => "Detail data Role berhasil ditampilkan!",
                "data" => $showData,
            ], 200);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validatedData = $request->validate([
            'title' => 'required|unique:roles'
        ], [
            'required' => 'Silahkan masukkan :attribute yang sesuai!',
            'unique' => 'Terdapat data dengan nama role yang sama dalam database',
        ]);

        $findData = Role::where('roles_id', '=', $id)
                            ->update($validatedData);

        if (!$findData) {
            return response([
                "message" => "Data $id tidak ada dalam database~",
            ], 404);
        }

        return response([
            "message" => "Update data Role berhasil dilakukan!",
        ], 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $findData = Role::find($id);

        if (!$findData) {
            return response([
                "message" => "Data $id tidak ada dalam database~",
            ], 400);
        } else {
            $findData->delete();

            return response([
                "message" => "Role berhasil dihapus!",
            ], 200);
        }
    }
}
