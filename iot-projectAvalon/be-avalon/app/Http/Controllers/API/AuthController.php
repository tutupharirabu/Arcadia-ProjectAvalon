<?php

namespace App\Http\Controllers\API;

use App\Models\Role;
use App\Models\User;
use App\Models\OTP_codes;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Mail\RegisterMailSend;
use Illuminate\Support\Carbon;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use App\Mail\ForgotPasswordMailSend;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|min:8|confirmed',
            'password_confirmation' => 'required',
        ]);

        if ($validatedData->fails()) {
            return response()->json(['error' => $validatedData->errors()], 400);
        }

        $roleData = Role::where('title', 'petani')->first();

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'roles_id' => $roleData->roles_id,
        ]);

        $user->generateOtpCodeData($user);
        $token = JWTAuth::fromUser($user);

        Mail::to($user->email)->send(new RegisterMailSend($user));

        return response([
            'message' => "Register berhasil! Silahkan cek email untuk verifikasi.",
            "user" => [
                "name" => $request->name,
                "email" => $request->email,
            ],
            'token' => $token,
        ], 201);
    }

    public function generateOtpCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['error' => 'Email tidak ditemukan!'], 404);
        }

        $user->generateOtpCodeData($user);

        Mail::to($user->email)->send(new RegisterMailSend($user));

        return response([
            'message' => "OTP telah dikirim ke email Anda!",
        ], 200);
    }

    public function verificationEmail(Request $request)
    {
        $request->validate([
            'otp_code' => 'required|numeric',
        ]);

        $otp = OTP_codes::where('otp_code', $request->otp_code)->first();

        if (!$otp) {
            return response([
                "message" => "Gagal verifikasi email! (OTP Code TIDAK DITEMUKAN)",
            ], 404);
        }

        if (Carbon::now() > $otp->valid_until) {
            return response([
                "message" => "Gagal verifikasi email! (OTP Code SUDAH KADALUARSA)",
            ], 400);
        }

        $user = User::find($otp->users_id);
        $user->email_verified_at = Carbon::now();
        $user->save();

        $otp->delete();

        return response([
            "message" => "Berhasil verifikasi email!",
        ], 200);
    }

    public function getUser()
    {
        $currentUser = auth()->user();
        $dataUser = User::with('role')->find($currentUser->users_id);

        return response()->json([
            'message' => 'Berhasil menampilkan data user!',
            'data' => $dataUser,
        ], 200);
    }

    public function login(Request $request)
    {
        // Validasi input
        $request->validate([
            'email' => 'required|email|max:255',
            'password' => 'required',
        ]);

        // Ambil kredensial
        $credentials = $request->only('email', 'password');

        // Coba login dengan kredensial
        if (!$token = Auth::guard('api')->attempt($credentials)) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Email atau password salah.',
            ], 401);
        }

        // Ambil data pengguna
        $user = User::where('email', $request->email)
            ->with(['role'])
            ->first();

        // Kirim token ke Node.js
        try {
            $nodeResponse = Http::post(env('NODE_API_URL_1') . '/api/store-token', [
                'token' => $token,
                'users_id' => $user->users_id, // Tambahkan users_id
            ]);

            if ($nodeResponse->failed()) {
                return response()->json([
                    'message' => 'Login berhasil, namun gagal mengirim token ke Node.js.',
                    'error' => $nodeResponse->body(),
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Login berhasil, namun gagal mengirim token ke Node.js.',
                'error' => $e->getMessage(),
            ], 500);
        }

        // Berikan respons login berhasil
        return response()->json([
            "message" => "Login berhasil!",
            "user" => [
                "id" => $user->users_id,
                "name" => $user->name,
                "email" => $user->email,
                "role" => $user->roles_id ? Str::ucfirst($user->role->title) : 'No Role Assigned',
            ],
            "token" => $token,
        ], 200);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['error' => 'Email tidak ditemukan!'], 404);
        }

        if (!$user->email_verified_at) {
            return response()->json(['error' => 'Email belum diverifikasi.'], 403);
        }

        $user->generateOtpCodeData($user);
        Mail::to($user->email)->send(new ForgotPasswordMailSend($user));

        return response()->json([
            'message' => 'OTP telah dikirim ke email Anda!',
        ], 200);
    }

    public function verifyOtpForgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp_code' => 'required|numeric'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['error' => 'Email tidak ditemukan.'], 404);
        }

        $otp = OTP_codes::where('users_id', $user->users_id)
            ->where('otp_code', $request->otp_code)
            ->first();

        if (!$otp) {
            return response()->json(['error' => 'Kode OTP tidak valid.'], 400);
        }

        if (Carbon::now() > $otp->valid_until) {
            return response()->json(['error' => 'Kode OTP sudah kadaluarsa.'], 400);
        }

        $resetToken = Str::random(60);
        $user->password_reset_token = $resetToken;
        $user->save();

        return response()->json([
            'message' => 'Verifikasi OTP berhasil~',
            'reset_token' => $resetToken,
        ], 200);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'reset_token' => 'required',
            'password' => 'required|min:8|confirmed',
            'password_confirmation' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        $user->password = Hash::make($request->password);
        $user->password_reset_token = null;
        $user->save();

        OTP_codes::where('users_id', $user->users_id)->delete();

        return response()->json([
            'message' => 'Password berhasil diubah~'
        ], 200);
    }

    public function logout()
    {
        try {
            Auth::guard('api')->logout();
            return response()->json(['message' => 'Logout berhasil~']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Logout gagal!'], 500);
        }
    }
}
