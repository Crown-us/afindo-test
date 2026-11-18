<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Membatasi route berdasarkan role user. Contoh: ->middleware('role:admin').
 * Dipasang setelah middleware 'auth', jadi user di sini selalu sudah login.
 */
class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        abort_unless(
            $user && collect($roles)->contains(fn (string $role) => $user->hasRole($role)),
            Response::HTTP_FORBIDDEN,
            'Anda tidak memiliki akses ke halaman ini.'
        );

        return $next($request);
    }
}
