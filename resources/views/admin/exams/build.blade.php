@extends('layouts.admin')

@section('admin-content')
<div
    class="es-page-enter es-wizard-page"
    x-data="{
        step: {{ session('exam_step', 1) }},
        panel: null,
        pageType: 'interactive',
        openPanel(name) { this.panel = this.panel === name ? null : name; },
        closePanel() { this.panel = null; },
        goStep(n) { this.step = n; this.panel = null; window.location.hash = 'step-' + n; }
    }"
    x-init="if (window.location.hash.match(/step-(\\d)/)) step = parseInt(window.location.hash.replace('#step-',''), 10)"
>
    <div class="mb-6 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
        <div>
            <a href="{{ route('admin.exams.index') }}" class="es-link text-sm font-bold">← Examens</a>
            <h1 class="es-page-title mt-3">{{ $exam->title }}</h1>
            <p class="es-page-subtitle">Création en plusieurs étapes — tout reste sur cette page</p>
        </div>
        @if ($exam->status === 'open')
            <span class="es-badge es-badge-success">Ouvert</span>
        @endif
    </div>

    {{-- Barre de progression --}}
    <nav aria-label="Progression" class="es-wizard-nav mb-8">
        <ol class="es-wizard-track">
            @foreach ([1 => ['Informations', 'Titre, matière, poids bulletin'], 2 => ['Contenu', 'Questions et étapes'], 3 => ['Planification', 'Dates et ouverture']] as $num => $meta)
                <li class="es-wizard-step" :class="step === {{ $num }} ? 'es-wizard-step-active' : (step > {{ $num }} ? 'es-wizard-step-done' : '')">
                    <button type="button" @click="goStep({{ $num }})" class="es-wizard-step-link w-full text-left">
                        <span class="es-wizard-badge" x-text="step > {{ $num }} ? '✓' : '{{ $num }}'"></span>
                        <span class="es-wizard-text">
                            <span class="es-wizard-label">{{ $meta[0] }}</span>
                            <span class="es-wizard-desc">{{ $meta[1] }}</span>
                        </span>
                    </button>
                </li>
            @endforeach
        </ol>
    </nav>

    @if (session('success'))
        <x-alert type="success" class="mb-6">{{ session('success') }}</x-alert>
    @endif

    {{-- Étape 1 --}}
    <section x-show="step === 1" x-cloak class="max-w-2xl">
        <div class="es-wizard-panel">
            <div class="es-wizard-panel-head">
                <span class="es-wizard-panel-num">1</span>
                <div>
                    <h2 class="text-2xl font-black text-es-ink">Informations & bulletin</h2>
                    <p class="text-es-muted mt-1">Chaque examen compte pour un % de la note finale du bulletin.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.exams.update', $exam) }}" class="space-y-5 mt-8">
                @csrf @method('PUT')
                <input type="hidden" name="redirect_step" value="2">

                <x-input label="Titre de l'examen" name="title" value="{{ old('title', $exam->title) }}" required/>

                <div>
                    <label class="es-label">Description</label>
                    <textarea name="description" rows="2" class="es-textarea">{{ old('description', $exam->description) }}</textarea>
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label class="es-label">Matière</label>
                        <select name="subject_id" id="exam-subject" class="es-select" required>
                            @foreach ($subjects as $subject)
                                <option value="{{ $subject->id }}" @selected(old('subject_id', $exam->subject_id) == $subject->id)>{{ $subject->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="es-label">Compétence</label>
                        <select name="skill_id" id="exam-skill" class="es-select" required>
                            @foreach ($skills as $skill)
                                <option value="{{ $skill->id }}" data-subject="{{ $skill->subject_id }}" @selected(old('skill_id', $exam->skill_id) == $skill->id)>{{ $skill->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label class="es-label">Période bulletin</label>
                        <select name="report_period_id" class="es-select">
                            @foreach ($periods as $period)
                                <option value="{{ $period->id }}" @selected(old('report_period_id', $exam->report_period_id) == $period->id)>{{ $period->label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input label="Poids dans le bulletin (%)" name="weight_percent" type="number" step="0.5" min="0" max="100"
                            value="{{ old('weight_percent', $exam->weight_percent) }}" required/>
                        <p class="text-xs text-es-muted mt-1">Ex. 3 examens à 33% = note finale complète.</p>
                    </div>
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <x-input label="Durée (min)" name="duration_minutes" type="number" min="5" max="480" value="{{ old('duration_minutes', $exam->duration_minutes) }}" required/>
                    <x-input label="Tentatives max" name="max_attempts" type="number" min="1" max="10" value="{{ old('max_attempts', $exam->max_attempts) }}" required/>
                </div>

                <input type="hidden" name="opens_at" value="{{ $exam->opens_at?->format('Y-m-d\TH:i') }}">
                <input type="hidden" name="closes_at" value="{{ $exam->closes_at?->format('Y-m-d\TH:i') }}">
                <input type="hidden" name="status" value="{{ $exam->status }}">

                <x-button type="submit">Enregistrer et continuer →</x-button>
            </form>
        </div>
    </section>

    {{-- Étape 2 — panneau latéral intégré (pas de pop-up plein écran) --}}
    <section x-show="step === 2" x-cloak>
        <div class="grid gap-4 lg:grid-cols-5 lg:items-start">
            <div class="lg:col-span-2 space-y-2">
                <div class="flex items-center justify-between gap-2">
                    <h2 class="text-base font-black">Étapes</h2>
                    <button type="button" @click="openPanel('add-page')" class="es-btn es-btn-primary es-btn-sm">+ Étape</button>
                </div>

                @forelse ($exam->pages as $page)
                    @php $meta = config('activity.page_types.'.$page->type, []); @endphp
                    <article
                        class="es-step-card !p-4"
                        style="--step-accent: {{ $meta['color'] ?? '#4f46e5' }}"
                        :class="panel === 'questions-{{ $page->id }}' ? 'ring-2 ring-es-primary/40' : ''"
                    >
                        <div class="flex justify-between gap-2">
                            <div class="min-w-0">
                                <p class="text-[10px] font-bold uppercase opacity-70">Étape {{ $page->page_order }}</p>
                                <h3 class="font-extrabold text-sm truncate">{{ $page->title }}</h3>
                                <p class="text-xs text-es-muted">
                                    {{ $meta['label'] ?? $page->type }}
                                    @if ($page->isInteractive()) · {{ $page->questions->count() }} Q @endif
                                </p>
                            </div>
                            <form method="POST" action="{{ route('admin.exams.pages.destroy', [$exam, $page]) }}" onsubmit="return confirm('Supprimer ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-500 font-bold text-lg leading-none p-1" aria-label="Supprimer">×</button>
                            </form>
                        </div>
                        @if ($page->isInteractive())
                            <button type="button" @click="openPanel('questions-{{ $page->id }}')"
                                class="mt-2 text-xs font-bold text-es-primary hover:underline">
                                Questions →
                            </button>
                        @endif
                    </article>
                @empty
                    <div class="es-empty !py-8"><p class="font-extrabold text-sm">Aucune étape</p></div>
                @endforelse

                @if ($exam->pages->isNotEmpty())
                    <x-button type="button" class="w-full es-btn-sm mt-2" @click="goStep(3)">Continuer → Planification</x-button>
                @endif
            </div>

            <div class="lg:col-span-3 lg:sticky lg:top-4">
                {{-- Panneau : ajouter une étape --}}
                <div x-show="panel === 'add-page'" x-cloak class="es-side-panel">
                    <div class="es-side-panel-head">
                        <h3 class="es-side-panel-title">Nouvelle étape</h3>
                        <button type="button" @click="closePanel()" class="text-es-muted font-bold text-lg leading-none" aria-label="Fermer">×</button>
                    </div>

                    <div class="grid grid-cols-2 gap-2 mb-4">
                        @foreach ($pageTypes as $typeKey => $typeMeta)
                            <button type="button" @click="pageType = '{{ $typeKey }}'"
                                class="es-type-picker !p-3"
                                :class="pageType === '{{ $typeKey }}' ? 'es-type-picker-active' : ''"
                                style="--type-color: {{ $typeMeta['color'] ?? '#4f46e5' }}">
                                <span class="es-type-picker-icon text-lg">{{ $typeMeta['icon'] }}</span>
                                <span class="es-type-picker-label text-xs">{{ $typeMeta['label'] }}</span>
                            </button>
                        @endforeach
                    </div>

                    <form method="POST" action="{{ route('admin.exams.pages.store', $exam) }}" class="space-y-3">
                        @csrf
                        <input type="hidden" name="type" :value="pageType">
                        <x-input label="Titre" name="title" required placeholder="Ex. Partie A" class="!py-2 text-sm"/>
                        <div>
                            <label class="es-label text-xs">Consignes</label>
                            <textarea name="body" class="es-textarea text-sm" rows="2"></textarea>
                        </div>
                        <div class="flex gap-2">
                            <x-button type="submit" class="es-btn-sm">Ajouter</x-button>
                            <x-button type="button" variant="secondary" class="es-btn-sm" @click="closePanel()">Annuler</x-button>
                        </div>
                    </form>
                </div>

                {{-- Panneaux questions (un par page interactive) --}}
                @foreach ($exam->pages as $page)
                    @if ($page->isInteractive())
                        <div x-show="panel === 'questions-{{ $page->id }}'" x-cloak class="es-side-panel">
                            <div class="es-side-panel-head">
                                <div class="min-w-0">
                                    <p class="text-[10px] font-bold uppercase text-es-muted">Questions</p>
                                    <h3 class="es-side-panel-title">{{ $page->title }}</h3>
                                </div>
                                <button type="button" @click="closePanel()" class="text-es-muted font-bold text-lg leading-none shrink-0" aria-label="Fermer">×</button>
                            </div>

                            @if ($page->questions->isNotEmpty())
                                <ul class="space-y-2 mb-4 max-h-40 overflow-y-auto">
                                    @foreach ($page->questions as $question)
                                        @php $qMeta = config('activity.question_types.'.$question->type, []); @endphp
                                        <li class="flex items-start justify-between gap-2 text-sm py-1.5 border-b border-stone-50 last:border-0">
                                            <div class="min-w-0">
                                                <span class="text-[10px] font-bold text-es-primary">{{ $qMeta['label'] ?? $question->type }}</span>
                                                <p class="font-semibold text-xs leading-snug truncate">{{ $question->prompt }}</p>
                                            </div>
                                            <form method="POST" action="{{ route('admin.exams.questions.destroy', [$exam, $question]) }}">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-red-500 font-bold text-sm shrink-0">×</button>
                                            </form>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-xs text-es-muted mb-3">Aucune question pour l'instant.</p>
                            @endif

                            @include('admin.exams.partials.question-form', [
                                'exam' => $exam,
                                'page' => $page,
                                'questionTypes' => collect($questionTypes)->only(['mcq', 'true_false', 'multi_select', 'numeric', 'choice_cards', 'short_text']),
                                'compact' => true,
                            ])
                        </div>
                    @endif
                @endforeach

                {{-- État par défaut --}}
                <div x-show="!panel" class="es-wizard-panel !p-5">
                    <h2 class="text-base font-black mb-1">Éditeur de contenu</h2>
                    <p class="text-es-muted text-xs mb-4">Choisis une étape à gauche pour ajouter des questions, ou crée une nouvelle étape.</p>
                    <button type="button" @click="openPanel('add-page')" class="es-btn es-btn-secondary es-btn-sm">+ Nouvelle étape</button>
                </div>
            </div>
        </div>
    </section>

    {{-- Étape 3 --}}
    <section x-show="step === 3" x-cloak class="max-w-2xl">
        <div class="es-wizard-panel">
            <div class="es-wizard-panel-head">
                <span class="es-wizard-panel-num">3</span>
                <div>
                    <h2 class="text-2xl font-black">Planification</h2>
                    <p class="text-es-muted mt-1">Définis quand les élèves peuvent passer l'examen.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.exams.update', $exam) }}" class="space-y-5 mt-8">
                @csrf @method('PUT')

                <div class="grid gap-5 sm:grid-cols-2">
                    <x-input label="Ouverture" name="opens_at" type="datetime-local" value="{{ old('opens_at', $exam->opens_at?->format('Y-m-d\TH:i')) }}" required/>
                    <x-input label="Fermeture" name="closes_at" type="datetime-local" value="{{ old('closes_at', $exam->closes_at?->format('Y-m-d\TH:i')) }}" required/>
                </div>

                <div>
                    <label class="es-label">Statut</label>
                    <select name="status" class="es-select">
                        @foreach (config('exam.statuses') as $value => $label)
                            <option value="{{ $value }}" @selected(old('status', $exam->status) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <input type="hidden" name="title" value="{{ $exam->title }}">
                <input type="hidden" name="subject_id" value="{{ $exam->subject_id }}">
                <input type="hidden" name="skill_id" value="{{ $exam->skill_id }}">
                <input type="hidden" name="report_period_id" value="{{ $exam->report_period_id }}">
                <input type="hidden" name="weight_percent" value="{{ $exam->weight_percent }}">
                <input type="hidden" name="duration_minutes" value="{{ $exam->duration_minutes }}">
                <input type="hidden" name="max_attempts" value="{{ $exam->max_attempts }}">

                <div class="flex flex-wrap gap-3">
                    <x-button type="submit">Enregistrer</x-button>
                    @if ($exam->contentReady() && $exam->status !== 'open')
                        <form method="POST" action="{{ route('admin.exams.open', $exam) }}" class="inline">
                            @csrf
                            <x-button type="submit" variant="secondary">Ouvrir aux élèves</x-button>
                        </form>
                    @endif
                    <x-button href="{{ route('admin.exams.index') }}" variant="secondary">Terminer</x-button>
                </div>
            </form>
        </div>
    </section>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const subject = document.getElementById('exam-subject');
    const skill = document.getElementById('exam-skill');
    if (!subject || !skill) return;
    function filter() {
        const sid = subject.value;
        skill.querySelectorAll('option').forEach(o => {
            const ok = o.dataset.subject === sid;
            o.hidden = !ok; o.disabled = !ok;
        });
        if (skill.selectedOptions[0]?.disabled) {
            const first = [...skill.options].find(o => !o.disabled);
            if (first) skill.value = first.value;
        }
    }
    subject.addEventListener('change', filter);
    filter();
});
</script>
@endpush
@endsection
