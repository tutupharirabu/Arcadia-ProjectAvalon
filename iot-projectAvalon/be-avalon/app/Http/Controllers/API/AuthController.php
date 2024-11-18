<?php

namespace App\Http\Controllers\API;

use App\Models\Role;
use App\Models\User;
use App\Models\OTP_codes;
use Illuminate\Http\Request;
use App\Mail\RegisterMailSend;
use Illuminate\Support\Carbon;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register (Request $request) {
        $validatedData = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|min:8|confirmed',
            'password_confirmation' => 'required',
        ]);

        if ($validatedData->fails()) {
            return response()->json(['error' => $validatedData->errors()], 400);
        }

        $roleData = Role::where('title', 'user')->first();

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => $roleData->roles_id,
        ]);

        $user->generateOtpCodeData($user);
        $token = JWTAuth::fromUser($user);

        Mail::to($user->email)->send(new RegisterMailSend($user));

        return response([
            'message' => "Register berhasil! Silahkan cek email untuk verifikasi.",
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    public function generateOtpCodeData (Request $request) {
        $request->validate([
            'email' => 'required|email|max:255',
        ]);

        $user = User::where('email', $request->email)->first();
        $user->generateOtpCodeData($user);

        Mail::to($user->email)->send(new RegisterMailSend($user));

        return response([
            'message' => "Berhasil generate OTP code!",
        ], 200);
    }

    public function verificationEmail (Request $request) {
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

    // public function getUser() {
    //     $currentUser = auth()->user();
    //     $dataUser = User::with('profile')->find($currentUser->users_id);

    //     return response()->json([
    //         'message' => 'Berhasil menampilkan data user!',
    //         'data' => $dataUser,
    //     ], 200);
    // }

    public function login (Request $request) {
        $request->validate([
            'email' => 'required|email|max:255',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');

        if (!$token = Auth::guard('api')->attempt($credentials)) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Email atau password salah.'
            ], 401);
        }

        $data = User::where('email', $request->email)
            ->with(['profile', 'role'])
            ->first();

        return response([
            "message" => "Login berhasil!",
            "user" => [
                "id" => $data->id,
                "name" => $data->name,
                "email" => $data->email,
                "role" => $data->role ? $data->role->name : 'No Role Assigned',
                "profile" => [
                    "bio" => $data->profile ? $data->profile->bio : null,
                    "umur" => $data->profile ? $data->profile->umur : null,
                ],
            ],
            "token" => $token
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
