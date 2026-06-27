<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PointAction;
use App\Models\PointReward;
use Illuminate\View\View;

class PointSettingsController extends Controller
{
    public function index(): View
    {
        return view('admin.points.settings', [
            'adminNav' => 'points',
            'positiveActions' => PointAction::positive()->orderBy('name')->get(),
            'negativeActions' => PointAction::negative()->orderBy('name')->get(),
            'rewards' => PointReward::ordered()->get(),
        ]);
    }
}
