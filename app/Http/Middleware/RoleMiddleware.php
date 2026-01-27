<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    // LET OP: Verander 'string $role' naar '...$roles' (de drie puntjes zijn belangrijk!)
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (! $request->user()) {
            return redirect('/login');
        }

        $userRole = $request->user()->role;

        // 1. Haal de string waarde uit de Enum
        if ($userRole instanceof \BackedEnum) {
            $userRole = $userRole->value;
        }

        // 2. Admin mag altijd alles (Super Access)
        if ($userRole === UserRole::ADMIN->value || strtolower($userRole) === 'admin') {
            return $next($request);
        }

        // 3. Check of de rol van de user in de lijst met toegestane rollen staat.
        // We checken nu of $userRole voorkomt in de array $roles.
        if (! in_array($userRole, $roles)) {
            // We tonen de vereiste rollen in de foutmelding voor duidelijkheid
            abort(403, "Je hebt geen toegang tot deze pagina. Vereiste rol(len): " . implode(', ', $roles));
        }

        return $next($request);
    }
}