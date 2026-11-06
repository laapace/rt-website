<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureHasRole
{
    /**
     * Handle an incoming request.
     *
     * Penggunaan di route: ->middleware('role:ketua,sekretaris')
     * Parameter peran dipisah koma; user harus punya salah satu peran.
     */
    public function handle(Request $request, Closure $next, string ...$peran): Response
    {
        // Dukung juga satu parameter berisi koma (mis. 'ketua,sekretaris').
        $peran = collect($peran)
            ->flatMap(fn (string $p) => explode(',', $p))
            ->map(fn (string $p) => trim($p))
            ->filter()
            ->all();

        $user = $request->user();

        if (! $user || ! $user->hasRole(...$peran)) {
            abort(403, 'Anda tidak memiliki peran yang diperlukan untuk mengakses halaman ini.');
        }

        return $next($request);
    }
}
