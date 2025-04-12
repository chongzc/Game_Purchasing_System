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
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!$request->user() || $request->user()->u_role !== $role) {
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Unauthorized access'
                ], 403);
            }
            
            if ($role === 'admin') {
                return redirect('/game-store');
            } elseif ($role === 'developer') {
                return redirect('/game-store');
            } else {
                return redirect('/game-store');
            }
        }

        return $next($request);
    }
} 
