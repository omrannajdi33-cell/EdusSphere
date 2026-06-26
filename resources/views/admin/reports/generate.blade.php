@extends('layouts.admin')

@section('admin-content')
<div class="es-page-enter max-w-2xl">
    <a href="{{ route('admin.reports.index') }}" class="es-link text-sm font-bold">← Bulletin</a>

    <div class="mt-4 mb-8">
        <h1 class="es-page-title">Générer le bulletin</h1>
        <p class="es-page-subtitle">Crée le bulletin sur le site et en PDF. Chaque période reprend les notes des bulletins précédents de l'année.</p>
    </div>

    <x-card>
        <form method="POST" action="{{ route('admin.reports.store') }}" class="space-y-5">
            @csrf

            <div>
                <label for="report_period_id" class="es-label">Période bulletin</label>
                <select name="report_period_id" id="report_period_id" class="es-select" required>
                    @foreach ($periods as $p)
                        <option value="{{ $p->id }}" @selected(old('report_period_id') == $p->id)>
                            {{ $p->label }} ({{ $p->school_year }})
                        </option>
                    @endforeach
                </select>
                <p class="text-xs text-es-muted mt-1">Trimestre 2 inclut le T1, trimestre 3 inclut T1 + T2 + T3.</p>
            </div>

            <div>
                <label class="es-label">Portée</label>
                <div class="space-y-3">
                    <label class="flex items-center gap-3 rounded-xl border border-stone-200 p-4 cursor-pointer hover:bg-stone-50">
                        <input type="radio" name="scope" value="all" checked class="h-5 w-5" onchange="document.getElementById('scope-fields').classList.add('hidden')">
                        <span class="font-semibold">Tous les élèves</span>
                    </label>
                    <label class="flex items-center gap-3 rounded-xl border border-stone-200 p-4 cursor-pointer hover:bg-stone-50">
                        <input type="radio" name="scope" value="class" class="h-5 w-5" onchange="document.getElementById('scope-fields').classList.remove('hidden'); document.getElementById('student-field').classList.add('hidden'); document.getElementById('class-field').classList.remove('hidden');">
                        <span class="font-semibold">Une classe</span>
                    </label>
                    <label class="flex items-center gap-3 rounded-xl border border-stone-200 p-4 cursor-pointer hover:bg-stone-50">
                        <input type="radio" name="scope" value="student" class="h-5 w-5" onchange="document.getElementById('scope-fields').classList.remove('hidden'); document.getElementById('class-field').classList.add('hidden'); document.getElementById('student-field').classList.remove('hidden');">
                        <span class="font-semibold">Un élève</span>
                    </label>
                </div>
            </div>

            <div id="scope-fields" class="hidden space-y-4">
                <div id="class-field" class="hidden">
                    <label for="class_group_id" class="es-label">Classe</label>
                    <select name="class_group_id" id="class_group_id" class="es-select">
                        <option value="">Choisir…</option>
                        @foreach ($classes as $class)
                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div id="student-field" class="hidden">
                    <label for="student_id" class="es-label">Élève</label>
                    <select name="student_id" id="student_id" class="es-select">
                        <option value="">Choisir…</option>
                        @foreach ($students as $student)
                            <option value="{{ $student->id }}">
                                {{ $student->full_name }}
                                @if ($student->classGroup) ({{ $student->classGroup->name }}) @endif
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label for="comment" class="es-label">Appréciation générale (optionnel)</label>
                <textarea name="comment" id="comment" rows="4" class="es-textarea" placeholder="Travail sérieux, continue ainsi…">{{ old('comment') }}</textarea>
            </div>

            <x-button type="submit" class="w-full">Générer le bulletin</x-button>
        </form>
    </x-card>
</div>

<script>
document.querySelectorAll('input[name="scope"]').forEach(radio => {
    radio.addEventListener('change', () => {
        const scope = document.querySelector('input[name="scope"]:checked')?.value;
        const fields = document.getElementById('scope-fields');
        const classField = document.getElementById('class-field');
        const studentField = document.getElementById('student-field');
        if (scope === 'all') {
            fields.classList.add('hidden');
            document.getElementById('class_group_id').value = '';
            document.getElementById('student_id').value = '';
        } else if (scope === 'class') {
            fields.classList.remove('hidden');
            classField.classList.remove('hidden');
            studentField.classList.add('hidden');
            document.getElementById('student_id').value = '';
        } else {
            fields.classList.remove('hidden');
            studentField.classList.remove('hidden');
            classField.classList.add('hidden');
            document.getElementById('class_group_id').value = '';
        }
    });
});
</script>
@endsection
