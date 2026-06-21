# Routes Laravel attendues — Phase 0.2

Fichiers : `routes/web.php`, `routes/auth.php` (ou Breeze/Fortify).

Préfixe middleware :
- `guest` — visiteurs non connectés
- `auth` — utilisateur connecté
- `role:teacher` — admin/professeur
- `role:student` — élève

---

## Routes publiques

```php
GET  /                          → redirect login ou dashboard
GET  /login                     → Auth\LoginController@create
POST /login                     → Auth\LoginController@store
POST /logout                    → Auth\LoginController@destroy
GET  /offline                   → Page offline PWA
```

---

## Routes Admin (`/admin`, middleware: auth + role:teacher)

### Dashboard

```php
GET  /admin                     → Admin\DashboardController@index
```

### Élèves

```php
GET    /admin/students                    → Admin\StudentController@index
GET    /admin/students/create             → Admin\StudentController@create
POST   /admin/students                    → Admin\StudentController@store
GET    /admin/students/{student}/edit     → Admin\StudentController@edit
PUT    /admin/students/{student}          → Admin\StudentController@update
DELETE /admin/students/{student}          → Admin\StudentController@destroy
```

### Matières

```php
GET    /admin/subjects                    → Admin\SubjectController@index
POST   /admin/subjects                    → Admin\SubjectController@store
PUT    /admin/subjects/{subject}          → Admin\SubjectController@update
DELETE /admin/subjects/{subject}          → Admin\SubjectController@destroy
```

### Compétences

```php
GET    /admin/subjects/{subject}/skills              → Admin\SkillController@index
POST   /admin/subjects/{subject}/skills              → Admin\SkillController@store
PUT    /admin/skills/{skill}                         → Admin\SkillController@update
DELETE /admin/skills/{skill}                         → Admin\SkillController@destroy
POST   /admin/subjects/{subject}/skills/validate-total → Admin\SkillController@validateTotal
```

### Leçons

```php
GET    /admin/lessons                     → Admin\LessonController@index
GET    /admin/lessons/create              → Admin\LessonController@create
POST   /admin/lessons                     → Admin\LessonController@store
GET    /admin/lessons/{lesson}/edit       → Admin\LessonController@edit
PUT    /admin/lessons/{lesson}            → Admin\LessonController@update
DELETE /admin/lessons/{lesson}            → Admin\LessonController@destroy
GET    /admin/lessons/{lesson}/preview    → Admin\LessonController@preview
POST   /admin/lessons/{lesson}/media      → Admin\LessonMediaController@store
DELETE /admin/lessons/{lesson}/media/{media} → Admin\LessonMediaController@destroy
```

### Activités

```php
GET    /admin/activities                  → Admin\ActivityController@index
GET    /admin/activities/create           → Admin\ActivityController@create
POST   /admin/activities                  → Admin\ActivityController@store
GET    /admin/activities/{activity}/edit  → Admin\ActivityController@edit
PUT    /admin/activities/{activity}       → Admin\ActivityController@update
DELETE /admin/activities/{activity}       → Admin\ActivityController@destroy
POST   /admin/activities/{activity}/publish   → Admin\ActivityController@publish
POST   /admin/activities/{activity}/unpublish → Admin\ActivityController@unpublish
GET    /admin/activities/{activity}/preview   → Admin\ActivityController@preview
POST   /admin/activities/{activity}/pages     → Admin\ActivityPageController@store
PUT    /admin/activity-pages/{page}           → Admin\ActivityPageController@update
DELETE /admin/activity-pages/{page}           → Admin\ActivityPageController@destroy
```

### Examens

```php
GET    /admin/exams                       → Admin\ExamController@index
POST   /admin/exams                       → Admin\ExamController@store
PUT    /admin/exams/{exam}                → Admin\ExamController@update
DELETE /admin/exams/{exam}                → Admin\ExamController@destroy
POST   /admin/exams/{exam}/open           → Admin\ExamController@open
POST   /admin/exams/{exam}/close          → Admin\ExamController@close
```

### Corrections

```php
GET    /admin/corrections                 → Admin\CorrectionController@index
GET    /admin/corrections/{submission}    → Admin\CorrectionController@show
POST   /admin/corrections/{submission}    → Admin\CorrectionController@store
POST   /admin/corrections/{submission}/return → Admin\CorrectionController@returnToStudent
POST   /admin/corrections/{submission}/validate → Admin\CorrectionController@validate
```

### Points

```php
GET    /admin/points                      → Admin\PointController@index
POST   /admin/points/{student}            → Admin\PointController@store
GET    /admin/points/settings             → Admin\PointActionController@index
PUT    /admin/point-actions/{action}      → Admin\PointActionController@update
```

### Horaires

```php
GET    /admin/schedules                   → Admin\ScheduleController@index
POST   /admin/schedules                   → Admin\ScheduleController@store
PUT    /admin/schedules/{schedule}        → Admin\ScheduleController@update
DELETE /admin/schedules/{schedule}        → Admin\ScheduleController@destroy
```

### Annonces

```php
GET    /admin/announcements               → Admin\AnnouncementController@index
POST   /admin/announcements               → Admin\AnnouncementController@store
PUT    /admin/announcements/{announcement} → Admin\AnnouncementController@update
DELETE /admin/announcements/{announcement} → Admin\AnnouncementController@destroy
POST   /admin/announcements/{announcement}/publish → Admin\AnnouncementController@publish
```

### Bulletins

```php
GET    /admin/reports                     → Admin\ReportController@index
POST   /admin/reports/generate            → Admin\ReportController@generate
GET    /admin/reports/{report}/pdf        → Admin\ReportController@downloadPdf
```

### Paramètres

```php
GET    /admin/settings                    → Admin\ProfileController@edit
PUT    /admin/settings                    → Admin\ProfileController@update
PUT    /admin/settings/password           → Admin\ProfileController@updatePassword
```

---

## Routes Élève (`/student`, middleware: auth + role:student)

### Dashboard & matières

```php
GET  /student                             → Student\DashboardController@index
GET  /student/subjects                    → Student\SubjectController@index
GET  /student/subjects/{subject}          → Student\SubjectController@show
```

### Leçons

```php
GET  /student/lessons                     → Student\LessonController@index
GET  /student/lessons/{lesson}            → Student\LessonController@show
POST /student/lessons/{lesson}/progress   → Student\LessonProgressController@update
```

### Activités

```php
GET  /student/activities                  → Student\ActivityController@index
GET  /student/activities/{activity}       → Student\ActivityController@show
POST /student/activities/{activity}/save  → Student\ActivitySaveController@store
POST /student/activities/{activity}/submit → Student\ActivityController@submit
GET  /student/activities/{activity}/correction → Student\ActivityController@correction
```

### Examens

```php
GET  /student/exams                       → Student\ExamController@index
POST /student/exams/{exam}/start          → Student\ExamController@start
GET  /student/exams/{exam}/take           → Student\ExamController@take
POST /student/exams/{exam}/save           → Student\ExamSaveController@store
POST /student/exams/{exam}/submit         → Student\ExamController@submit
GET  /student/exams/{exam}/result         → Student\ExamController@result
```

### Points, progression, horaire

```php
GET  /student/points                      → Student\PointController@index
GET  /student/progress                    → Student\ProgressController@index
GET  /student/schedule                    → Student\ScheduleController@index
GET  /student/announcements               → Student\AnnouncementController@index
GET  /student/notifications               → Student\NotificationController@index
POST /student/notifications/{id}/read     → Student\NotificationController@markRead
```

### Profil

```php
GET  /student/profile                     → Student\ProfileController@edit
PUT  /student/profile                     → Student\ProfileController@update
POST /student/profile/avatar             → Student\ProfileController@uploadAvatar
DELETE /student/profile/avatar            → Student\ProfileController@deleteAvatar
```

---

## Routes API internes (AJAX, JSON)

Préfixe : `/api` ou routes web avec header `Accept: application/json`.

```php
POST /api/activities/{activity}/autosave     → Api\ActivityAutosaveController
POST /api/exams/{attempt}/autosave           → Api\ExamAutosaveController
GET  /api/notifications/unread-count         → Api\NotificationController@count
```

Middleware : `auth`, `throttle:60,1` sur autosave.

---

## Fichiers médias privés

```php
GET /media/{media}    → MediaController@show   (auth + policy)
```

---

## Résumé

| Groupe | Routes approx. |
|--------|----------------|
| Auth | 4 |
| Admin CRUD + actions | ~55 |
| Student | ~25 |
| API autosave | 3 |
| **Total** | **~87 routes** |
