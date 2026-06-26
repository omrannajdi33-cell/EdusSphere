<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($user = $request->user()) {
            return redirect()->intended($this->homeFor($user));
        }

        return $next($request);
    }

    private function homeFor(User $user): string
    {
        return $user->isTeacher()
            ? route('admin.dashboard')
            : route('student.dashboard');
    }
}
