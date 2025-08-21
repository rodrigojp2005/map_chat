<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UpdateUserActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        
        // Atualizar atividade apenas para usuários autenticados
        if (Auth::check()) {
            $user = Auth::user();
            
            // Atualizar last_seen apenas se passou mais de 1 minuto da última atualização
            if (!$user->last_seen || $user->last_seen->diffInMinutes(now()) > 1) {
                $user->update(['last_seen' => now()]);
            }
        }
        
        return $response;
    }
}
