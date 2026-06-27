<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreStudentRequest;
use App\Http\Requests\Admin\UpdateStudentRequest;
use App\Models\SchoolLevel;
use App\Models\Student;
use App\Models\User;
use App\Services\StudentAvatarService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function index(Request $request): View
    {
        $query = Student::with(['user', 'schoolLevel', 'classGroup'])->latest();

        if ($search = $request->string('q')->trim()->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($u) => $u->where('email', 'like', "%{$search}%"));
            });
        }

        if ($levelId = $request->integer('level')) {
            $query->where('school_level_id', $levelId);
        }

        return view('admin.students.index', [
            'adminNav' => 'students',
            'students' => $query->paginate(12)->withQueryString(),
            'levels' => SchoolLevel::orderBy('display_order')->get(),
            'search' => $search ?? '',
            'levelFilter' => $levelId ?: null,
        ]);
    }

    public function create(): View
    {
        return view('admin.students.form', [
            'adminNav' => 'students',
            'student' => new Student,
            'user' => new User(['role' => User::ROLE_STUDENT, 'status' => 'active']),
            'levels' => SchoolLevel::orderBy('display_order')->get(),
        ]);
    }

    public function store(StoreStudentRequest $request): RedirectResponse
    {
        $student = DB::transaction(function () use ($request) {
            $data = $request->validated();

            $user = User::create([
                'name' => trim($data['first_name'].' '.$data['last_name']),
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => User::ROLE_STUDENT,
                'status' => $data['status'] ?? 'active',
            ]);

            $student = Student::create([
                'user_id' => $user->id,
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'birth_date' => $data['birth_date'] ?? null,
                'school_level_id' => $data['school_level_id'] ?? null,
            ]);

            if ($request->hasFile('avatar')) {
                app(StudentAvatarService::class)->store($student, $request->file('avatar'));
            }

            return $student;
        });

        return redirect()
            ->route('admin.students.edit', $student)
            ->with('success', 'Élève créé avec succès.');
    }

    public function edit(Student $student): View
    {
        $student->load(['user', 'schoolLevel', 'classGroup']);

        return view('admin.students.form', [
            'adminNav' => 'students',
            'student' => $student,
            'user' => $student->user,
            'levels' => SchoolLevel::orderBy('display_order')->get(),
        ]);
    }

    public function update(UpdateStudentRequest $request, Student $student): RedirectResponse
    {
        DB::transaction(function () use ($request, $student) {
            $data = $request->validated();

            $student->user->update([
                'name' => trim($data['first_name'].' '.$data['last_name']),
                'email' => $data['email'],
                'status' => $data['status'] ?? 'active',
            ]);

            if (! empty($data['password'])) {
                $student->user->update(['password' => Hash::make($data['password'])]);
            }

            $student->update([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'birth_date' => $data['birth_date'] ?? null,
                'school_level_id' => $data['school_level_id'] ?? null,
            ]);

            if ($request->hasFile('avatar')) {
                app(StudentAvatarService::class)->store($student, $request->file('avatar'));
            }

            if ($request->boolean('remove_avatar') && $student->avatar_path) {
                app(StudentAvatarService::class)->deleteFor($student);
            }
        });

        return back()->with('success', 'Élève mis à jour.');
    }

    public function destroy(Student $student): RedirectResponse
    {
        $user = $student->user;

        if ($student->avatar_path) {
            app(StudentAvatarService::class)->deleteFor($student);
        }

        $user?->delete();

        return redirect()
            ->route('admin.students.index')
            ->with('success', 'Élève supprimé.');
    }

    public function showAvatar(Student $student, StudentAvatarService $avatars)
    {
        return $avatars->response($student);
    }
}
