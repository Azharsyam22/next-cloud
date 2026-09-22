<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventAcademicPasswordChange
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Aturan rules.md §4: User akademik tidak boleh bisa mengganti password lewat CloudCampus
        if ($user && $user->isAcademic()) {
            $message = 'Akun akademik dikelola oleh SSO Sistem Akademik kampus dan tidak diizinkan mengubah kata sandi lokal.';

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $message,
                ], Response::HTTP_FORBIDDEN);
            }

            abort(Response::HTTP_FORBIDDEN, $message);
        }

        return $next($request);
    }
}
