<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
   public function handle(Request $request, Closure $next, string $role): Response
    {
        // checks if the user is authenticated/logged in
        if (! $request->user()) {
            return redirect('/login');
        }

        // Does the user have the required role?
        if ($request->user()->role !== $role) {
            //Error is no access
            abort(403, 'Je hebt geen toegang tot deze pagina.');
        }

        // continue if everything is ok
        return $next($request);
    }
}
