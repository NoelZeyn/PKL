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
        $this->middleware('auth:api', ['except' => ['login', 'register', 'checkEmail']]);
    }
        public function me()
    {
        return response()->json(Auth::user());
    }

    public function login()
    {
        $credentials = request(['email', 'password']);

        $user = Admin::where('email', request('email'))->first();

        if (!$user || $user->status !== 'success') {
            return response()->json(['error' => !$user ? 'Email atau password salah' : 'Tolong registrasi kembali'], !$user ? 404 : 403);
        }

        if (!$token = Auth::attempt($credentials)) {
            return response()->json(['error' => 'Email atau password salah'], 401);
        }

        return $this->respondWithToken($token);
    }

    public function register(Request $request): JsonResponse
    {
        $messages = [
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'email.dns' => 'Domain email tidak valid.',
            'password.min' => 'Password minimal 8 karakter.',
        ];

        $validator = Validator::make($request->all(), [
            'nama_lengkap' => 'required|string|max:255',
            'email' => 'required|email:rfc,dns|unique:admin,email|max:255',
            'password' => 'required|min:8',
            'posisi' => 'required|string',
            'foto_ktp' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
            'foto_profil' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120'
        ], $messages);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $pathFK = $request->file('foto_ktp')->store('KTP', 'public');
            $pathFP = $request->file('foto_profil')->store('Photo-Profile', 'public');

            $user = Admin::create([
                'nama_lengkap' => $request->nama_lengkap,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'status' => 'pending',
                'posisi' => $request->posisi,
                'foto_ktp' => $pathFK,
                'foto_profil' => $pathFP,
            ]);

            return response()->json([
                'message' => 'Pendaftaran berhasil',
                'user' => $user,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Pendaftaran gagal',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function checkEmail(Request $request)
    {
        $user = Admin::where('email', $request->email)->first();

        if ($user) {
            return response()->json([
                'message' => 'Tolong pakai email lain',
                'data' => $user,
            ], 404);
        }

        return response()->json([
            'message' => 'Email tersedia',
        ], 200);
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
