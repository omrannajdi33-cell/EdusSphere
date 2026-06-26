<?php

namespace App\Http\Controllers;

use App\Support\DailyDiscovery;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View|RedirectResponse
    {
        $user = auth()->user();

        if ($user?->isTeacher()) {
            return redirect()->route('admin.dashboard');
        }

        $discovery = DailyDiscovery::today();
        $firstName = null;

        if ($user?->isStudent()) {
            $firstName = $user->student?->first_name ?? explode(' ', $user->name)[0];
        }

        return view('welcome', [
            'discovery' => $discovery,
            'firstName' => $firstName,
            'isStudent' => (bool) $user?->isStudent(),
        ]);
    }
}
