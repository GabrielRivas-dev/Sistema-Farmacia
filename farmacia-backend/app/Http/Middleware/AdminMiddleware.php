<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
{
    \Log::info('AdminMiddleware ejecutado', [
        'autenticado' => Auth::check(),
        'user_id' => Auth::check() ? Auth::id() : null,
        'user_role' => Auth::check() ? Auth::user()->role : null,
        'user_active' => Auth::check() ? Auth::user()->is_active : null
    ]);

    if (!Auth::check()) {
        return response()->json(['message' => 'No autenticado'], 401);
    }

    $user = Auth::user();
    
    if (!$user->isAdmin()) {
        \Log::warning('Usuario no es admin', [
            'user_id' => $user->id,
            'role' => $user->role,
            'is_active' => $user->is_active
        ]);
        
        return response()->json([
            'message' => 'No tienes permisos de administrador'
        ], 403);
    }

    return $next($request);
}
}