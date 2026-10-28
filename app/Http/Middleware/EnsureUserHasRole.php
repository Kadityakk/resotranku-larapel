<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Restricts a route to one or more role names, e.g. `role:admin,cashier`.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if ($user === null) {
            return redirect()->guest(route('login'));
        }

        $user->loadMissing('role');

        abort_unless($user->hasRole(...$roles), 403, 'Anda tidak memiliki akses ke halaman ini.');

        return $next($request);
    }
}
