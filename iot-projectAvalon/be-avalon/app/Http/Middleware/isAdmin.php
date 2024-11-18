<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class isAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $currentUser = Auth::guard('api')->user();
        $roleAdminIds = Role::whereIn('title', ['admin', 'super-admin'])->pluck('roles_id')->toArray();

        if (in_array($currentUser->roles_id, $roleAdminIds)) {
            return $next($request);
        }

        return response()->json([
            "message" => "Hanya Admin dan Super Admin yang dapat mengakses situs ini",
        ], 401);
    }
}
