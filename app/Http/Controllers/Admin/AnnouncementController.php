<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAnnouncementRequest;
use App\Http\Requests\Admin\UpdateAnnouncementRequest;
use App\Models\Announcement;
use App\Models\SchoolLevel;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function index(): View
    {
        return view('admin.announcements.index', [
            'adminNav' => 'announcements',
            'announcements' => Announcement::with('author')->latest()->paginate(12),
        ]);
    }

    public function create(): View
    {
        return $this->formView(new Announcement(['target_type' => 'all']));
    }

    public function store(StoreAnnouncementRequest $request): RedirectResponse
    {
        $data = $request->validated();
        unset($data['publish_now']);

        $announcement = Announcement::create([
            ...$data,
            'target_id' => $this->resolveTargetId($data),
            'created_by' => $request->user()->id,
            'published_at' => $request->boolean('publish_now') ? now() : null,
        ]);

        return redirect()
            ->route('admin.announcements.edit', $announcement)
            ->with('success', 'Annonce créée.');
    }

    public function edit(Announcement $announcement): View
    {
        return $this->formView($announcement);
    }

    public function update(UpdateAnnouncementRequest $request, Announcement $announcement): RedirectResponse
    {
        $data = $request->validated();
        unset($data['publish_now']);

        $announcement->update([
            ...$data,
            'target_id' => $this->resolveTargetId($data),
        ]);

        return back()->with('success', 'Annonce mise à jour.');
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        $announcement->delete();

        return redirect()
            ->route('admin.announcements.index')
            ->with('success', 'Annonce supprimée.');
    }

    public function publish(Announcement $announcement): RedirectResponse
    {
        $announcement->update(['published_at' => now()]);

        return back()->with('success', 'Annonce publiée.');
    }

    public function unpublish(Announcement $announcement): RedirectResponse
    {
        $announcement->update(['published_at' => null]);

        return back()->with('success', 'Annonce dépubliée.');
    }

    protected function formView(Announcement $announcement): View
    {
        return view('admin.announcements.form', [
            'adminNav' => 'announcements',
            'announcement' => $announcement,
            'levels' => SchoolLevel::orderBy('display_order')->get(),
            'students' => Student::with('user')->orderBy('last_name')->orderBy('first_name')->get(),
        ]);
    }

    /** @param  array<string, mixed>  $data */
    private function resolveTargetId(array $data): ?int
    {
        if ($data['target_type'] === 'all') {
            return null;
        }

        return isset($data['target_id']) ? (int) $data['target_id'] : null;
    }
}
