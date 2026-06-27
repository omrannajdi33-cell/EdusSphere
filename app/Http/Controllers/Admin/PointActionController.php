<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePointActionRequest;
use App\Http\Requests\Admin\UpdatePointActionRequest;
use App\Models\PointAction;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PointActionController extends Controller
{
    public function store(StorePointActionRequest $request): RedirectResponse
    {
        PointAction::create($request->validated());

        return back()->with('success', 'Action de points ajoutée.');
    }

    public function update(UpdatePointActionRequest $request, PointAction $action): RedirectResponse
    {
        $action->update($request->validated());

        return back()->with('success', 'Action mise à jour.');
    }

    public function destroy(PointAction $action): RedirectResponse
    {
        $action->update(['is_active' => false]);

        return back()->with('success', 'Action désactivée.');
    }
}
