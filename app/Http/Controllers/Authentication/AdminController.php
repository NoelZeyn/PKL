<?php

namespace App\Http\Controllers\Authentication;

use App\Models\Admin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register', 'me']]);
    }
    public function me()
    {
        return response()->json(Auth::user());
    }

    public function login()
    {
        $credentials = request(['NID', 'password']);

        $user = Admin::where('NID', request('NID'))->first();

        if (!$user) {
            return response()->json(['error' => !$user ? 'NID atau password salah' : 'Tolong registrasi kembali'], !$user ? 404 : 403);
        }

        if (!$token = Auth::attempt($credentials)) {
            return response()->json(['error' => 'NID atau password salah'], 401);
        }
        if ($user->password_changed_at) {
            $passwordAgeInDays = now()->diffInDays($user->password_changed_at);
            if ($passwordAgeInDays >= 90) {
                return response()->json([
                    'error' => 'Password Anda sudah kedaluwarsa. Harap ganti password tiap 3 bulan. Hubungi kontak Ibu Linda Aqnes'
                ], 423);
            }
        }

        return $this->respondWithToken($token);
    }

    public function register(Request $request): JsonResponse
    {
        $messages = [
            'NID.required' => 'NID wajib diisi.',
            'NID.unique' => 'NID sudah terdaftar.',
            'NID.size' => 'NID harus terdiri dari 10 karakter.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'tingkatan_otoritas.in' => 'Tingkatan otoritas tidak valid.',
        ];

        $validator = Validator::make($request->all(), [
            'NID' => 'required|size:10|unique:admin,NID',
            'password' => 'required|min:8',
            'tingkatan_otoritas' => 'required|in:superadmin,admin,user,user_review,anggaran',
            'id_penempatan_fk' => 'required|exists:penempatan,id',
        ], $messages);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $user = Admin::create([
                'NID' => $request->NID,
                'id_penempatan_fk' => $request->id_penempatan_fk,
                'password' => Hash::make($request->password),
                'tingkatan_otoritas' => $request->tingkatan_otoritas, // Default to level 1
                'access' => 'pending',
                'password_changed_at' => now(),
            ]);
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Registration successful',
                'user' => $user
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Registration failed: ' . $e->getMessage());
            return response()->json(['error' => 'Registration failed: '. $e->getMessage()], 500);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Registration failed: ' . $e->getMessage());
            return response()->json(['error' => 'Registration failed: '. $e->getMessage()], 500);
        }
    }



    public function logout()
    {
        Auth::logout();
        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(Auth::refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'status' => 'success',
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::factory()->getTTL() * 60
        ]);
    }
}
