<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!$request->user()) {
            return redirect()->route('login');
        }
        
        // Si aucun rôle n'est spécifié ou si l'utilisateur est admin, continuer
        if (empty($roles) || $request->user()->type === 'admin') {
            return $next($request);
        }
        
        // Vérifier si l'utilisateur a l'un des rôles spécifiés
        foreach ($roles as $role) {
            if ($request->user()->type === $role) {
                return $next($request);
            }
        }
        
        // Si l'utilisateur n'a pas les rôles requis
        return abort(403, 'Accès non autorisé : vous n\'avez pas les permissions nécessaires.');
    }
}
