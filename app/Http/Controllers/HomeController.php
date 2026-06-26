<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;

class HomeController extends Controller
{
    public function __invoke(): RedirectResponse
    {
        $user = auth()->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->isTeacher()) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('student.dashboard');
    }
}
