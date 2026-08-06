<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyPasswordResetToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $user = User::where('email', $request->email)
            ->where('password_reset_token', $request->reset_token)
            ->first();

        if (!$user) {
            return response()->json([
                'error' => 'Token reset tidak valid atau sudah kadaluarsa!'
            ], 403);
        }

        return $next($request);
    }
}
