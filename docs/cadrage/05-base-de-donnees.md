# Carte de la base de données — Phase 0.2

Première version du schéma MySQL pour EduSphere V1. Les migrations Laravel suivront cette structure.

---

## Diagramme relationnel (vue d'ensemble)

```
users ──────────────┬── students ──┬── exam_attempts
                    │              ├── progressions
                    │              ├── points
                    │              ├── grades
                    │              └── reports
                    │
school_levels ──────┘

subjects ── skills ──┬── lessons ── media_files
                     ├── activities ── activity_pages ── questions ── answers
                     └── exams ── exam_attempts

corrections ── annotations
point_actions ── points
schedules ── subjects
events
announcements ── notifications
```

---

## Tables détaillées

### `users`

| Colonne | Type | Notes |
|---------|------|-------|
| id | BIGINT PK | |
| name | VARCHAR(255) | Nom complet |
| email | VARCHAR(255) UNIQUE | |
| password | VARCHAR(255) | Hash bcrypt |
| role | ENUM('teacher','student') | |
| status | ENUM('active','inactive') | |
| email_verified_at | TIMESTAMP NULL | |
| remember_token | VARCHAR(100) | |
| created_at, updated_at | TIMESTAMP | |

### `students`

| Colonne | Type | Notes |
|---------|------|-------|
| id | BIGINT PK | |
| user_id | BIGINT FK → users | |
| first_name | VARCHAR(100) | |
| last_name | VARCHAR(100) | |
| birth_date | DATE NULL | |
| avatar_path | VARCHAR(255) NULL | |
| school_level_id | BIGINT FK → school_levels | |
| ui_preferences | JSON NULL | P1 |
| created_at, updated_at | TIMESTAMP | |

### `school_levels`

| Colonne | Type | Notes |
|---------|------|-------|
| id | BIGINT PK | |
| name | VARCHAR(100) | ex. CE1, CE2 |
| order | INT | Affichage |
| created_at, updated_at | TIMESTAMP | |

### `subjects`

| Colonne | Type | Notes |
|---------|------|-------|
| id | BIGINT PK | |
| name | VARCHAR(100) | |
| color | VARCHAR(7) | Hex (#3B82F6) |
| icon | VARCHAR(50) | Nom icône |
| display_order | INT | |
| created_at, updated_at | TIMESTAMP | |

### `skills`

| Colonne | Type | Notes |
|---------|------|-------|
| id | BIGINT PK | |
| subject_id | BIGINT FK → subjects | |
| name | VARCHAR(255) | |
| weight_percent | DECIMAL(5,2) | Somme par matière = 100 |
| display_order | INT | |
| created_at, updated_at | TIMESTAMP | |

### `lessons`

| Colonne | Type | Notes |
|---------|------|-------|
| id | BIGINT PK | |
| subject_id | BIGINT FK | |
| skill_id | BIGINT FK | |
| title | VARCHAR(255) | |
| description | TEXT NULL | |
| cover_image_path | VARCHAR(255) NULL | |
| school_level_id | BIGINT FK NULL | |
| estimated_duration_min | INT NULL | |
| status | ENUM('draft','published') | |
| published_at | TIMESTAMP NULL | |
| created_at, updated_at | TIMESTAMP | |

### `activities`

| Colonne | Type | Notes |
|---------|------|-------|
| id | BIGINT PK | |
| subject_id | BIGINT FK | |
| skill_id | BIGINT FK | |
| title | VARCHAR(255) | |
| description | TEXT NULL | |
| status | ENUM('draft','published','archived') | |
| published_at | TIMESTAMP NULL | |
| created_at, updated_at | TIMESTAMP | |

### `activity_pages`

| Colonne | Type | Notes |
|---------|------|-------|
| id | BIGINT PK | |
| activity_id | BIGINT FK | |
| page_order | INT | |
| title | VARCHAR(255) NULL | |
| content | JSON | Canvas, config outils |
| created_at, updated_at | TIMESTAMP | |

### `questions`

| Colonne | Type | Notes |
|---------|------|-------|
| id | BIGINT PK | |
| activity_page_id | BIGINT FK | |
| type | VARCHAR(50) | qcm, true_false, short, long… |
| prompt | TEXT | |
| config | JSON | Options, bonne réponse, points |
| display_order | INT | |
| created_at, updated_at | TIMESTAMP | |

### `answers` (réponses élève)

| Colonne | Type | Notes |
|---------|------|-------|
| id | BIGINT PK | |
| student_id | BIGINT FK | |
| question_id | BIGINT FK NULL | |
| activity_page_id | BIGINT FK | |
| exam_attempt_id | BIGINT FK NULL | |
| content | JSON | Texte, dessin, choix |
| is_correct | BOOLEAN NULL | Après correction |
| score | DECIMAL(5,2) NULL | |
| created_at, updated_at | TIMESTAMP | |

### `exams`

| Colonne | Type | Notes |
|---------|------|-------|
| id | BIGINT PK | |
| subject_id | BIGINT FK | |
| skill_id | BIGINT FK | |
| title | VARCHAR(255) | |
| description | TEXT NULL | |
| duration_minutes | INT | |
| max_attempts | INT DEFAULT 1 | |
| opens_at | TIMESTAMP | |
| closes_at | TIMESTAMP | |
| status | ENUM('draft','scheduled','open','closed') | |
| created_at, updated_at | TIMESTAMP | |

### `exam_attempts`

| Colonne | Type | Notes |
|---------|------|-------|
| id | BIGINT PK | |
| exam_id | BIGINT FK | |
| student_id | BIGINT FK | |
| started_at | TIMESTAMP | |
| finished_at | TIMESTAMP NULL | |
| duration_seconds | INT NULL | |
| pages_visited | INT DEFAULT 0 | |
| answers_count | INT DEFAULT 0 | |
| final_score | DECIMAL(5,2) NULL | |
| status | ENUM('in_progress','submitted','corrected') | |
| created_at, updated_at | TIMESTAMP | |

### `corrections`

| Colonne | Type | Notes |
|---------|------|-------|
| id | BIGINT PK | |
| student_id | BIGINT FK | |
| activity_id | BIGINT FK NULL | |
| exam_attempt_id | BIGINT FK NULL | |
| teacher_id | BIGINT FK → users | |
| status | ENUM('submitted','to_correct','corrected','returned','validated') | |
| score | DECIMAL(5,2) NULL | |
| comment | TEXT NULL | |
| created_at, updated_at | TIMESTAMP | |

### `correction_history`

| Colonne | Type | Notes |
|---------|------|-------|
| id | BIGINT PK | |
| correction_id | BIGINT FK | |
| user_id | BIGINT FK | |
| action | VARCHAR(50) | |
| comment | TEXT NULL | |
| created_at | TIMESTAMP | |

### `annotations`

| Colonne | Type | Notes |
|---------|------|-------|
| id | BIGINT PK | |
| correction_id | BIGINT FK | |
| activity_page_id | BIGINT FK | |
| teacher_id | BIGINT FK | |
| data | JSON | Traits, encres, formes |
| created_at, updated_at | TIMESTAMP | |

### `grades`

| Colonne | Type | Notes |
|---------|------|-------|
| id | BIGINT PK | |
| student_id | BIGINT FK | |
| subject_id | BIGINT FK NULL | |
| skill_id | BIGINT FK NULL | |
| value | DECIMAL(5,2) | |
| type | ENUM('activity','exam','average_skill','average_subject','general') | |
| source_id | BIGINT NULL | Polymorphique |
| calculated_at | TIMESTAMP | |
| created_at, updated_at | TIMESTAMP | |

### `progressions`

| Colonne | Type | Notes |
|---------|------|-------|
| id | BIGINT PK | |
| student_id | BIGINT FK | |
| lesson_id | BIGINT FK NULL | |
| activity_id | BIGINT FK NULL | |
| last_page | INT DEFAULT 1 | |
| percent_complete | DECIMAL(5,2) | |
| time_spent_seconds | INT DEFAULT 0 | |
| updated_at | TIMESTAMP | |

### `point_actions`

| Colonne | Type | Notes |
|---------|------|-------|
| id | BIGINT PK | |
| name | VARCHAR(100) | |
| description | VARCHAR(255) NULL | |
| value | INT | +/- points |
| type | ENUM('positive','negative') | |
| is_active | BOOLEAN DEFAULT true | |
| created_at, updated_at | TIMESTAMP | |

### `points`

| Colonne | Type | Notes |
|---------|------|-------|
| id | BIGINT PK | |
| student_id | BIGINT FK | |
| point_action_id | BIGINT FK | |
| awarded_by | BIGINT FK → users | |
| value | INT | Copie au moment T |
| note | VARCHAR(255) NULL | |
| created_at | TIMESTAMP | |

### `schedules`

| Colonne | Type | Notes |
|---------|------|-------|
| id | BIGINT PK | |
| subject_id | BIGINT FK | |
| title | VARCHAR(255) | |
| color | VARCHAR(7) NULL | |
| day_of_week | TINYINT | 1=lundi … 7=dimanche |
| period_number | TINYINT | 1–4 |
| starts_at | TIME | |
| ends_at | TIME | |
| schedule_date | DATE NULL | Pour planif. ponctuelle |
| created_at, updated_at | TIMESTAMP | |

### `events`

| Colonne | Type | Notes |
|---------|------|-------|
| id | BIGINT PK | |
| title | VARCHAR(255) | |
| type | ENUM('exam','homework','reminder','event') | |
| starts_at | DATETIME | |
| ends_at | DATETIME NULL | |
| subject_id | BIGINT FK NULL | |
| description | TEXT NULL | |
| created_at, updated_at | TIMESTAMP | |

### `announcements`

| Colonne | Type | Notes |
|---------|------|-------|
| id | BIGINT PK | |
| title | VARCHAR(255) | |
| body | TEXT | |
| target_type | ENUM('all','level','student') | |
| target_id | BIGINT NULL | |
| published_at | TIMESTAMP NULL | |
| created_by | BIGINT FK → users | |
| created_at, updated_at | TIMESTAMP | |

### `notifications`

| Colonne | Type | Notes |
|---------|------|-------|
| id | BIGINT PK | |
| user_id | BIGINT FK | |
| type | VARCHAR(50) | |
| data | JSON | |
| read_at | TIMESTAMP NULL | |
| created_at | TIMESTAMP | |

### `reports` (bulletins)

| Colonne | Type | Notes |
|---------|------|-------|
| id | BIGINT PK | |
| student_id | BIGINT FK | |
| period_label | VARCHAR(100) | ex. Trimestre 1 |
| general_average | DECIMAL(5,2) | |
| subject_averages | JSON | |
| comments | TEXT NULL | |
| pdf_path | VARCHAR(255) NULL | |
| generated_by | BIGINT FK → users | |
| generated_at | TIMESTAMP | |
| created_at, updated_at | TIMESTAMP | |

### `media_files`

| Colonne | Type | Notes |
|---------|------|-------|
| id | BIGINT PK | |
| lesson_id | BIGINT FK NULL | |
| activity_id | BIGINT FK NULL | |
| filename | VARCHAR(255) | |
| path | VARCHAR(500) | |
| mime_type | VARCHAR(100) | |
| size_bytes | BIGINT | |
| page_count | INT NULL | V2 conversion |
| created_at, updated_at | TIMESTAMP | |

---

## Index recommandés

```sql
INDEX idx_students_user ON students(user_id);
INDEX idx_skills_subject ON skills(subject_id);
INDEX idx_answers_student ON answers(student_id);
INDEX idx_exam_attempts_student ON exam_attempts(student_id, exam_id);
INDEX idx_progressions_student ON progressions(student_id);
INDEX idx_points_student ON points(student_id);
INDEX idx_notifications_user_read ON notifications(user_id, read_at);
```

---

## Contraintes métier

1. `SUM(skills.weight_percent) WHERE subject_id = X` doit égaler **100.00**
2. Un élève ne peut accéder qu'à ses propres `answers`, `exam_attempts`, `points`
3. `corrections.status = submitted` bloque l'édition côté élève (sauf `returned`)

---

## Tables V2 (non migrées en V1)

| Table | Usage |
|-------|-------|
| `activity_page_versions` | Historique modifications pages |
| `document_pages` | Pages converties depuis PDF/PPT |
| `audit_logs` | Logs admin |

---

## Total V1

**22 tables** (+ `correction_history` = 23 entités)
