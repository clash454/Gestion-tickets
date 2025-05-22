<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ($user->hasPermissionTo('access_filament') || $user->type === 'admin')) {
            return $next($request);
        }

        abort(403, 'Accès non autorisé à l\'interface d\'administration.');
    }
} 