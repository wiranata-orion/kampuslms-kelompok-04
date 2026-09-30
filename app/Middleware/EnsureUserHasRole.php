<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Meloloskan request hanya jika user yang login memiliki salah satu
     * role yang diizinkan.
     *
     * Dipakai lewat alias 'role' di route, mis.:
     *   ->middleware(['auth', 'role:admin'])
     *   ->middleware(['auth', 'role:admin,dosen']) // lebih dari satu role
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        // Seharusnya tidak pernah null karena middleware 'auth' selalu
        // dipasang sebelum 'role' di semua grup route kita — tapi dijaga
        // agar aman kalau suatu saat 'role' dipakai sendirian.
        if (! $user) {
            abort(401, 'Kamu harus login terlebih dahulu.');
        }

        if (! in_array($user->role, $roles, true)) {
            abort(403, 'Kamu tidak punya akses ke halaman ini.');
        }

        return $next($request);
    }
}