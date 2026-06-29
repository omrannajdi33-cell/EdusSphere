@extends('layouts.focus')

@section('title', $exam->title.' — Examen')

@section('focus-content')
<div
    data-focus-room
    data-hand-raise-url="{{ route('student.exams.attempts.hand-raise', $attempt) }}"
    class="h-[100dvh] flex flex-col"
>
    <div data-focus-gate class="fixed inset-0 z-50 flex items-center justify-center bg-es-ink/90 p-6">
        <div class="es-card max-w-md w-full p-8 text-center space-y-4">
            <p class="text-4xl">📝</p>
            <h1 class="text-2xl font-black text-es-ink">Examen — salle verrouillée</h1>
            <p class="text-es-muted">Plein écran obligatoire. Besoin d'aide ? Utilise le bouton « J'ai une question » — le prof sera alerté.</p>
            <button type="button" class="es-btn-primary w-full" data-focus-start>Commencer l'examen</button>
        </div>
    </div>

    <div data-focus-warn class="hidden fixed top-4 left-1/2 -translate-x-1/2 z-50 bg-red-600 text-white px-5 py-3 rounded-2xl font-bold shadow-lg" role="alert"></div>

    <div data-focus-shell class="hidden flex-1 flex flex-col min-h-0 h-[100dvh]">
        <div
            class="shrink-0 border-b border-stone-200 bg-amber-50/90 px-4 md:px-6 py-2.5 flex flex-wrap items-center justify-between gap-3"
            x-data="examTimer(@js($endsAt->toIso8601String()), @js(route('student.exams.submit', $attempt)))"
            x-init="start()"
        >
            <div class="min-w-0">
                <p class="text-[11px] font-bold uppercase tracking-wider text-amber-800/80">Examen en cours</p>
                <p class="font-extrabold text-es-ink truncate">{{ $exam->title }}</p>
            </div>
            <div class="flex items-center gap-3 shrink-0">
                <button type="button" class="es-btn es-btn-secondary es-btn-sm" data-hand-raise>✋ J'ai une question</button>
                <div class="text-right">
                    <p class="text-[11px] font-bold uppercase text-es-muted">Temps restant</p>
                    <p class="text-xl font-black tabular-nums" x-text="display" :class="urgent ? 'text-red-600' : 'text-es-primary'"></p>
                </div>
            </div>
        </div>

        @include('student.activities.partials.player-shell', [
            'activity' => $content,
            'progression' => null,
            'answers' => $answers,
            'previewMode' => false,
            'focusMode' => true,
            'examMode' => $usesOwnContent,
            'examAttempt' => $attempt,
            'saveUrl' => route('student.exams.attempts.save', $attempt),
            'submitUrl' => route('student.exams.submit', $attempt),
            'recordingUrlOverride' => auth()->check() ? route('student.activities.recording.upload', $content, false) : '',
        ])
    </div>
</div>

@push('scripts')
<script>
function examTimer(endsAtIso, submitUrl) {
    return {
        display: '--:--',
        urgent: false,
        interval: null,
        start() {
            const end = new Date(endsAtIso).getTime();
            const tick = () => {
                const diff = Math.max(0, end - Date.now());
                const m = Math.floor(diff / 60000);
                const s = Math.floor((diff % 60000) / 1000);
                this.display = `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
                this.urgent = diff < 5 * 60000;
                if (diff <= 0) {
                    clearInterval(this.interval);
                    this.autoSubmit();
                }
            };
            tick();
            this.interval = setInterval(tick, 1000);
        },
        async autoSubmit() {
            const token = document.querySelector('meta[name="csrf-token"]')?.content;
            await fetch(submitUrl, { method: 'POST', headers: { Accept: 'application/json', 'X-CSRF-TOKEN': token }, credentials: 'same-origin' });
            window.location.href = @js(route('student.dashboard'));
        },
    };
}
</script>
@endpush
@endsection
