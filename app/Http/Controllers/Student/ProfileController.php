<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdateAvatarRequest;
use App\Http\Requests\Profile\UpdatePasswordRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        $user = auth()->user();
        $user->load('student.schoolLevel');

        return view('student.profile.edit', [
            'user' => $user,
            'student' => $user->student,
        ]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $request->user()->update($request->validated());

        return back()->with('success', 'Profil mis à jour.');
    }

    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        $request->user()->update([
            'password' => $request->validated('password'),
        ]);

        return back()->with('success', 'Mot de passe modifié.');
    }

    public function uploadAvatar(UpdateAvatarRequest $request): RedirectResponse
    {
        $student = $request->user()->student;

        if (! $student) {
            abort(403);
        }

        if ($student->avatar_path) {
            Storage::disk('local')->delete($student->avatar_path);
        }

        $file = $request->file('avatar');
        $filename = Str::uuid().'.'.$file->getClientOriginalExtension();
        $path = $file->storeAs('avatars/'.$student->id, $filename, 'local');

        $student->update(['avatar_path' => $path]);

        return back()->with('success', 'Photo mise à jour.');
    }

    public function deleteAvatar(): RedirectResponse
    {
        $student = auth()->user()->student;

        if (! $student) {
            abort(403);
        }

        if ($student->avatar_path) {
            Storage::disk('local')->delete($student->avatar_path);
            $student->update(['avatar_path' => null]);
        }

        return back()->with('success', 'Photo supprimée.');
    }

    public function showAvatar()
    {
        $student = auth()->user()->student;

        if (! $student?->avatar_path || ! Storage::disk('local')->exists($student->avatar_path)) {
            abort(404);
        }

        return Storage::disk('local')->response($student->avatar_path);
    }
}
