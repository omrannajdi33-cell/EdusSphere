<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdateAvatarRequest;
use App\Http\Requests\Profile\UpdatePasswordRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Services\StudentAvatarService;
use Illuminate\Http\RedirectResponse;
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

    public function uploadAvatar(UpdateAvatarRequest $request, StudentAvatarService $avatars): RedirectResponse
    {
        $student = $request->user()->student;

        if (! $student) {
            abort(403);
        }

        $avatars->store($student, $request->file('avatar'));

        return back()->with('success', 'Photo mise à jour.');
    }

    public function deleteAvatar(StudentAvatarService $avatars): RedirectResponse
    {
        $student = auth()->user()->student;

        if (! $student) {
            abort(403);
        }

        if ($student->avatar_path) {
            $avatars->deleteFor($student);
        }

        return back()->with('success', 'Photo supprimée.');
    }

    public function showAvatar(StudentAvatarService $avatars)
    {
        $user = auth()->user();
        $student = $user->student;

        if (! $student) {
            return $avatars->placeholderForName($user->name ?? '?');
        }

        return $avatars->response($student);
    }
}
