<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePointRewardRequest;
use App\Http\Requests\Admin\UpdatePointRewardRequest;
use App\Models\PointReward;
use Illuminate\Http\RedirectResponse;

class PointRewardController extends Controller
{
    public function store(StorePointRewardRequest $request): RedirectResponse
    {
        PointReward::create($request->validated());

        return back()->with('success', 'Récompense ajoutée.');
    }

    public function update(UpdatePointRewardRequest $request, PointReward $reward): RedirectResponse
    {
        $reward->update($request->validated());

        return back()->with('success', 'Récompense mise à jour.');
    }

    public function destroy(PointReward $reward): RedirectResponse
    {
        $reward->update(['is_active' => false]);

        return back()->with('success', 'Récompense désactivée.');
    }
}
