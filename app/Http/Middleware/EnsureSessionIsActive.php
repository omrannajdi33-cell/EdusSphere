<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSessionIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            return $next($request);
        }

        $idleMinutes = config('edusphere.session_idle_minutes', 30);
        $lastActivity = $request->session()->get('last_activity_at');

        if ($lastActivity && now()->diffInMinutes($lastActivity) >= $idleMinutes) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Session expirée après inactivité. Reconnecte-toi.',
            ]);
        }

        // Évite une écriture session à chaque requête (gain perf sur Redis/DB).
        if (! $lastActivity || now()->diffInSeconds($lastActivity) >= 60) {
            $request->session()->put('last_activity_at', now());
        }

        return $next($request);
    }
}
