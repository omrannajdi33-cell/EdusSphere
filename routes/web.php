<?php

use App\Http\Controllers\Admin\ActivityController as AdminActivityController;
use App\Http\Controllers\Admin\ActivityEditorController;
use App\Http\Controllers\Admin\CorrectionController;
use App\Http\Controllers\Admin\AnnouncementController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\ExamEditorController;
use App\Http\Controllers\Admin\ExamAttemptController;
use App\Http\Controllers\Admin\ExamController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LessonController;
use App\Http\Controllers\Admin\LessonDocumentController;
use App\Http\Controllers\Admin\PointActionController;
use App\Http\Controllers\Admin\PointController;
use App\Http\Controllers\Admin\PointRewardController;
use App\Http\Controllers\Admin\PointSettingsController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ScheduleController;
use App\Http\Controllers\Admin\SkillController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\ActivityMediaController;
use App\Http\Controllers\ActivityRecordingController;
use App\Http\Controllers\LessonMediaController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Student\ActivityController as StudentActivityController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Student\ExamHandRaiseController;
use App\Http\Controllers\Student\ExamController as StudentExamController;
use App\Http\Controllers\Student\LessonAnnotationController;
use App\Http\Controllers\Student\LessonController as StudentLessonController;
use App\Http\Controllers\Student\BulletinController;
use App\Http\Controllers\Student\PointRedemptionController;
use App\Http\Controllers\Student\PointsController;
use App\Http\Controllers\Student\NotificationController as StudentNotificationController;
use App\Http\Controllers\Student\ProfileController as StudentProfileController;
use App\Http\Controllers\Student\ScheduleController as StudentScheduleController;
use App\Models\Activity;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
});

Route::post('/logout', [AuthController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::get('/offline', fn () => view('offline'))->name('offline');

Route::get('/csrf-token', fn () => response()->json(['token' => csrf_token()]))->name('csrf.token');

Route::middleware('auth')->get('/lesson-media/{lesson}/{media}', LessonMediaController::class)->name('lesson-media.show');

Route::middleware('auth')->get('/activity-media/{activity}/{media}', ActivityMediaController::class)->name('activity-media.show');

Route::middleware('auth')->get('/activities/{activity}/students/{student}/recording', ActivityRecordingController::class)->name('activities.recording.show');

Route::prefix('admin')
    ->middleware(['auth', 'role:'.User::ROLE_TEACHER])
    ->name('admin.')
    ->group(function () {
        Route::get('/', DashboardController::class)->name('dashboard');

        Route::get('/students/{student}/avatar', [StudentController::class, 'showAvatar'])->name('students.avatar.show');
        Route::resource('students', StudentController::class)->except(['show']);

        Route::get('/points', [PointController::class, 'index'])->name('points.index');
        Route::post('/points', [PointController::class, 'store'])->name('points.store');
        Route::get('/points/settings', [PointSettingsController::class, 'index'])->name('points.settings');
        Route::post('/point-actions', [PointActionController::class, 'store'])->name('point-actions.store');
        Route::put('/point-actions/{action}', [PointActionController::class, 'update'])->name('point-actions.update');
        Route::delete('/point-actions/{action}', [PointActionController::class, 'destroy'])->name('point-actions.destroy');
        Route::post('/point-rewards', [PointRewardController::class, 'store'])->name('point-rewards.store');
        Route::put('/point-rewards/{reward}', [PointRewardController::class, 'update'])->name('point-rewards.update');
        Route::delete('/point-rewards/{reward}', [PointRewardController::class, 'destroy'])->name('point-rewards.destroy');

        Route::resource('subjects', SubjectController::class)->except(['show']);
        Route::resource('subjects.skills', SkillController::class)->except(['show']);

        Route::get('activities/{activity}/build', [AdminActivityController::class, 'build'])->name('activities.build');
        Route::get('activities/{activity}/preview', [AdminActivityController::class, 'preview'])->name('activities.preview');
        Route::post('activities/{activity}/publish', [AdminActivityController::class, 'publish'])->name('activities.publish');
        Route::post('activities/{activity}/unpublish', [AdminActivityController::class, 'unpublish'])->name('activities.unpublish');
        Route::resource('activities', AdminActivityController::class)->except(['show']);
        Route::get('activities/{activity}/editor', fn (Activity $activity) => redirect()->route('admin.activities.build', ['activity' => $activity, 'step' => 2]))->name('activities.editor');
        Route::get('activities/{activity}/submissions', [ActivityEditorController::class, 'submissions'])->name('activities.submissions');
        Route::get('activities/{activity}/corrections/{student}', [ActivityEditorController::class, 'correct'])->name('activities.corrections.show');
        Route::post('activities/{activity}/corrections/{student}', [ActivityEditorController::class, 'saveCorrection'])->name('activities.corrections.save');
        Route::post('activities/{activity}/corrections/{student}/finalize', [ActivityEditorController::class, 'finalizeCorrection'])->name('activities.corrections.finalize');
        Route::post('activities/{activity}/corrections/{student}/return', [ActivityEditorController::class, 'returnCorrection'])->name('activities.corrections.return');
        Route::post('activities/{activity}/pages', [ActivityEditorController::class, 'storePage'])->name('activities.pages.store');
        Route::delete('activities/{activity}/pages/{page}', [ActivityEditorController::class, 'destroyPage'])->name('activities.pages.destroy');
        Route::post('activities/{activity}/pages/{page}/questions', [ActivityEditorController::class, 'storeQuestion'])->name('activities.questions.store');
        Route::delete('activities/{activity}/questions/{question}', [ActivityEditorController::class, 'destroyQuestion'])->name('activities.questions.destroy');

        Route::post('lessons/{lesson}/documents', [LessonDocumentController::class, 'store'])->name('lessons.documents.store');
        Route::put('lessons/{lesson}/documents/{media}', [LessonDocumentController::class, 'update'])->name('lessons.documents.update');
        Route::delete('lessons/{lesson}/documents/{media}', [LessonDocumentController::class, 'destroy'])->name('lessons.documents.destroy');
        Route::post('lessons/{lesson}/publish', [LessonController::class, 'publish'])->name('lessons.publish');
        Route::post('lessons/{lesson}/unpublish', [LessonController::class, 'unpublish'])->name('lessons.unpublish');
        Route::resource('lessons', LessonController::class)->except(['show']);

        Route::resource('schedules', ScheduleController::class)->only(['index', 'store', 'update', 'destroy']);

        Route::post('exams/{exam}/open', [ExamController::class, 'open'])->name('exams.open');
        Route::post('exams/{exam}/close', [ExamController::class, 'close'])->name('exams.close');
        Route::get('exams/{exam}/build', [ExamController::class, 'build'])->name('exams.build');
        Route::post('exams/{exam}/pages', [ExamEditorController::class, 'storePage'])->name('exams.pages.store');
        Route::delete('exams/{exam}/pages/{page}', [ExamEditorController::class, 'destroyPage'])->name('exams.pages.destroy');
        Route::post('exams/{exam}/pages/{page}/questions', [ExamEditorController::class, 'storeQuestion'])->name('exams.questions.store');
        Route::delete('exams/{exam}/questions/{question}', [ExamEditorController::class, 'destroyQuestion'])->name('exams.questions.destroy');
        Route::resource('exams', ExamController::class)->except(['show', 'store']);

        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/generate', [ReportController::class, 'create'])->name('reports.generate');
        Route::post('reports/generate', [ReportController::class, 'store'])->name('reports.store');
        Route::get('reports/{report}', [ReportController::class, 'show'])->name('reports.show');
        Route::get('reports/{report}/pdf', [ReportController::class, 'pdf'])->name('reports.pdf');

        Route::post('announcements/{announcement}/publish', [AnnouncementController::class, 'publish'])->name('announcements.publish');
        Route::post('announcements/{announcement}/unpublish', [AnnouncementController::class, 'unpublish'])->name('announcements.unpublish');
        Route::resource('announcements', AnnouncementController::class)->except(['show']);

        Route::get('corrections', [CorrectionController::class, 'index'])->name('corrections.index');
        Route::get('exams/attempts/{attempt}/correct', [ExamAttemptController::class, 'correct'])->name('exams.attempts.correct');
        Route::post('exams/attempts/{attempt}/finalize', [ExamAttemptController::class, 'finalize'])->name('exams.attempts.finalize');
        Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
        Route::post('notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');

        Route::get('/settings', [AdminProfileController::class, 'edit'])->name('settings');
        Route::put('/settings', [AdminProfileController::class, 'update'])->name('settings.update');
        Route::put('/settings/password', [AdminProfileController::class, 'updatePassword'])->name('settings.password');
    });

Route::prefix('student')
    ->middleware(['auth', 'role:'.User::ROLE_STUDENT])
    ->name('student.')
    ->group(function () {
        Route::get('/', StudentDashboardController::class)->name('dashboard');

        Route::get('/subjects', function () {
            return view('student.subjects.index', [
                'activeNav' => 'subjects',
                'subjects' => \App\Models\Subject::with('skills')->ordered()->get(),
            ]);
        })->name('subjects.index');

        Route::get('/lessons', [StudentLessonController::class, 'index'])->name('lessons.index');
        Route::get('/lessons/{lesson}', [StudentLessonController::class, 'show'])->name('lessons.show');
        Route::post('/lessons/{lesson}/annotations', [LessonAnnotationController::class, 'save'])->name('lessons.annotations.save');
        Route::get('/activities', [StudentActivityController::class, 'index'])->name('activities.index');
        Route::get('/homework', [\App\Http\Controllers\Student\HomeworkController::class, 'index'])->name('homework.index');
        Route::get('/activities/{activity}/play', [StudentActivityController::class, 'play'])->name('activities.play');
        Route::post('/activities/{activity}/save', [StudentActivityController::class, 'save'])->name('activities.save');
        Route::post('/activities/{activity}/recording', [StudentActivityController::class, 'uploadRecording'])->name('activities.recording.upload');
        Route::get('/activities/{activity}/recording', [StudentActivityController::class, 'showRecording'])->name('activities.recording');
        Route::post('/activities/{activity}/submit', [StudentActivityController::class, 'submit'])->name('activities.submit');
        Route::get('/exams', [StudentExamController::class, 'index'])->name('exams.index');
        Route::post('/exams/{exam}/start', [StudentExamController::class, 'start'])->name('exams.start');
        Route::get('/exams/attempts/{attempt}', [StudentExamController::class, 'take'])->name('exams.take');
        Route::post('/exams/attempts/{attempt}/hand-raise', ExamHandRaiseController::class)->name('exams.attempts.hand-raise');
        Route::post('/exams/attempts/{attempt}/save', [StudentExamController::class, 'save'])->name('exams.attempts.save');
        Route::post('/exams/attempts/{attempt}/submit', [StudentExamController::class, 'submit'])->name('exams.submit');
        Route::get('/schedule', [StudentScheduleController::class, 'index'])->name('schedule.index');
        Route::get('/bulletin', [BulletinController::class, 'index'])->name('bulletin.index');
        Route::get('/bulletin/{report}', [BulletinController::class, 'show'])->name('bulletin.show');
        Route::get('/bulletin/{report}/pdf', [BulletinController::class, 'pdf'])->name('bulletin.pdf');
        Route::get('/points', PointsController::class)->name('points.index');
        Route::post('/points/redeem', [PointRedemptionController::class, 'store'])->name('points.redeem');

        Route::get('/notifications', [StudentNotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/read-all', [StudentNotificationController::class, 'markAllRead'])->name('notifications.read-all');
        Route::post('/notifications/{notification}/read', [StudentNotificationController::class, 'markRead'])->name('notifications.read');
        Route::get('/profile', [StudentProfileController::class, 'edit'])->name('profile');
        Route::put('/profile', [StudentProfileController::class, 'update'])->name('profile.update');
        Route::put('/profile/password', [StudentProfileController::class, 'updatePassword'])->name('profile.password');
        Route::post('/profile/avatar', [StudentProfileController::class, 'uploadAvatar'])->name('profile.avatar');
        Route::get('/profile/avatar', [StudentProfileController::class, 'showAvatar'])->name('profile.avatar.show');
        Route::delete('/profile/avatar', [StudentProfileController::class, 'deleteAvatar'])->name('profile.avatar.delete');
    });
