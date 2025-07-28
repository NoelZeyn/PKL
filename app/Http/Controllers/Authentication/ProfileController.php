<?php

namespace App\Http\Controllers\Authentication;

use App\Models\Admin;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function showProfile()
    {
        try {
            $admin = Auth::user(); // Sudah instance Admin karena pakai guard 'auth:api'

            if (!$admin) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pengguna tidak ditemukan.'
                ], 404);
            }

            $dataDiri = $admin->dataDiri;

            return response()->json([
                'status' => 'success',
                'message' => 'Data profil pengguna berhasil diambil.',
                'data' => [
                    'NID'          => $admin->NID,
                    'nama_lengkap' => $dataDiri->nama_lengkap ?? '',
                    'jabatan'      => $dataDiri->jabatan ?? '',
                    'foto_profil'  => $dataDiri && $dataDiri->foto_profil ? asset('storage/app/public/' . $dataDiri->foto_profil) : null,
                    'kontak'       => $dataDiri->kontak ?? '',
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil data profil.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateProfile(Request $request)
    {
        try {
            $admin = Admin::with('dataDiri')->findOrFail(Auth::id());

            $validator = Validator::make($request->all(), [
                'nama_lengkap' => 'required|string|max:255',
                'jabatan' => 'required|string|max:255',
                'bpjs' => 'nullable|string|max:255',
                'kontak' => 'nullable|string|max:255',
                'foto_profil' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'password' => 'nullable|string|min:6|confirmed',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Update password (jika diisi dan beda)
            if ($request->filled('password') && !Hash::check($request->password, $admin->password)) {
                $admin->password = Hash::make($request->password);
                $admin->save();
            }

            // Update data diri
            $dataDiriData = $request->only(['nama_lengkap', 'jabatan', 'bpjs', 'kontak']);

            // Upload foto
            if ($request->hasFile('foto_profil')) {
                $path = $request->file('foto_profil')->store('Photo-Profile');

                if (!$path) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Gagal mengupload foto profil.'
                    ], 500);
                }

                if ($admin->dataDiri && $admin->dataDiri->foto_profil && Storage::exists($admin->dataDiri->foto_profil)) {
                    Storage::delete($admin->dataDiri->foto_profil);
                }

                // Update foto_profil di relasi data_diri
                if ($admin->dataDiri) {
                    $admin->dataDiri->update(['foto_profil' => $path]);
                } else {
                    $admin->dataDiri()->create(['foto_profil' => $path]);
                }
            }


            if ($admin->dataDiri) {
                $admin->dataDiri->update($dataDiriData);
            } else {
                $admin->dataDiri()->create($dataDiriData);
            }

            $updated = $admin->fresh('dataDiri');

            return response()->json([
                'status' => 'success',
                'message' => 'Profil berhasil diperbarui.',
                'data' => [
                    'nama_lengkap' => $updated->dataDiri->nama_lengkap,
                    'jabatan' => $updated->dataDiri->jabatan,
                    'bpjs' => $updated->dataDiri->bpjs,
                    'kontak' => $updated->dataDiri->kontak,
                    'foto_profil' => $updated->dataDiri->foto_profil ? asset('storage/' . $updated->dataDiri->foto_profil) : null,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat memperbarui profil: ' . $e->getMessage()
            ], 500);
        }
    }


    public function updatePassword(Request $request)
    {
        try {
            $admin = Admin::findOrFail(Auth::id());

            $validator = Validator::make($request->all(), [
                'current_password' => 'required|string',
                'password' => 'required|string|min:8|confirmed',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }
            if (!Hash::check($request->current_password, $admin->password)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Password saat ini salah'
                ], 400);
            }
            if (!Hash::check($request->password, $admin->password)) {
                $admin->update([
                    'password' => Hash::make($request->password),
                    'password_changed_at' => now(),
                ]);
            }
            return response()->json([
                'status' => 'success',
                'message' => 'Password berhasil diperbarui.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat memperbarui password: ' . $e->getMessage()
            ], 500);
        }
    }
}
