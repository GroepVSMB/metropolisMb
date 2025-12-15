<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
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
        if (! $request->user()) {
            return redirect('/login');
        }

        $userRole = $request->user()->role;

        // 1. If the role is an Enum Object, extract the string value
        if ($userRole instanceof \BackedEnum) {
            $userRole = $userRole->value;
        }

        // 2. Perform the comparison (String vs String)
        // We use strtolower just to be safe against case sensitivity (Planner vs planner)
        if (strtolower($userRole) !== strtolower($role)) {
            abort(403, "Je hebt geen toegang tot deze pagina. Jij hebt niet de role: $role");
        }

        return $next($request);
    }
}
