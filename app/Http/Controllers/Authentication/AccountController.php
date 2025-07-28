<?php

namespace App\Http\Controllers\Authentication;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin;

class AccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $accounts = Admin::with('dataDiri')->get();
            return response()->json([
                'status' => 'success',
                'data' => $accounts
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve accounts',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'nid' => 'required|string|unique:admins,nid',
                'password' => 'required|string|min:6',
                'id_penempatan_fk' => 'required|integer|exists:penempatans,id',
                'tingkatan_otoritas' => 'required|string',
            ]);

            $account = Admin::create([
                'nid' => $request->nid,
                'password' => bcrypt($request->password),
                'id_penempatan_fk' => $request->id_penempatan_fk,
                'tingkatan_otoritas' => $request->tingkatan_otoritas,
                'access' => 'active',
                'password_changed_at' => now(),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Account created successfully',
                'data' => $account
            ], 201);
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $account = Admin::findOrFail($id);
            $account->update($request->all());
            return response()->json([
                'status' => 'success',
                'message' => 'Account updated successfully',
                'data' => $account
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update account',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $account = Admin::findOrFail($id);
            $account->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'Account deleted successfully'
            ], 200);
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
}
