<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
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

        // --- NEW CODE START ---
        // Grant "Super Access" if the user is an Admin.
        // We check against the Enum value (safest) or the hardcoded string 'admin'.
        if ($userRole === UserRole::ADMIN->value || strtolower($userRole) === 'admin') {
            return $next($request);
        }
        // --- NEW CODE END ---

        // 2. Perform the comparison (String vs String)
        if (strtolower($userRole) !== strtolower($role)) {
            abort(403, "Je hebt geen toegang tot deze pagina. Jij hebt niet de role: $role");
        }

        return $next($request);
    }
}