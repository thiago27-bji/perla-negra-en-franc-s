<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $user = Auth::user();

        // 🔥 Solo permite rol admin (FK_rol = 1)
        if ($user->FK_rol != 1) {
            return redirect('/')->with('error', 'No tienes permisos de administrador');
        }

        return $next($request);
    }
}