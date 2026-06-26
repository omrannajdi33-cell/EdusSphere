@props([
    'students',
    'levels',
    'classGroups',
    'selectedIds' => [],
])

@php
    $selectedIds = old('student_ids', $selectedIds);
    $studentPayload = $students->map(fn ($s) => [
        'id' => $s->id,
        'name' => $s->full_name,
        'level_id' => $s->school_level_id,
        'class_id' => $s->class_group_id,
        'level' => $s->schoolLevel?->name,
        'class' => $s->classGroup?->name,
    ])->values();
@endphp

<div
    class="rounded-2xl border border-stone-200 p-5 space-y-5"
    x-data="{
        students: @js($studentPayload),
        selected: @js(array_map('intval', $selectedIds)),
        filterLevel: '',
        filterClass: '',
        search: '',
        visible(student) {
            if (this.filterLevel && String(student.level_id) !== this.filterLevel) return false;
            if (this.filterClass && String(student.class_id) !== this.filterClass) return false;
            if (this.search) {
                const q = this.search.toLowerCase();
                return student.name.toLowerCase().includes(q)
                    || (student.level || '').toLowerCase().includes(q)
                    || (student.class || '').toLowerCase().includes(q);
            }
            return true;
        },
        toggleLevel(levelId) {
            const ids = this.students.filter(s => String(s.level_id) === String(levelId)).map(s => s.id);
            const allSelected = ids.length > 0 && ids.every(id => this.selected.includes(id));
            if (allSelected) {
                this.selected = this.selected.filter(id => !ids.includes(id));
            } else {
                ids.forEach(id => { if (!this.selected.includes(id)) this.selected.push(id); });
            }
        },
        toggleClass(classId) {
            const ids = this.students.filter(s => String(s.class_id) === String(classId)).map(s => s.id);
            const allSelected = ids.length > 0 && ids.every(id => this.selected.includes(id));
            if (allSelected) {
                this.selected = this.selected.filter(id => !ids.includes(id));
            } else {
                ids.forEach(id => { if (!this.selected.includes(id)) this.selected.push(id); });
            }
        },
        clearSelection() { this.selected = []; },
        selectVisible() {
            this.students.filter(s => this.visible(s)).forEach(s => {
                if (!this.selected.includes(s.id)) this.selected.push(s.id);
            });
        },
    }"
>
    <div>
        <h3 class="font-extrabold text-es-ink">Destinataires</h3>
        <p class="text-sm text-es-muted mt-1">Choisis les élèves qui verront cette activité. Utilise les raccourcis par niveau ou par classe.</p>
    </div>

    @if ($students->isEmpty())
        <x-alert type="warning" title="Aucun élève actif">
            Ajoute des élèves avant de publier une activité.
        </x-alert>
    @else
        <div class="flex flex-wrap gap-2">
            @foreach ($levels as $level)
                <button type="button" class="es-qtype-chip" @click="toggleLevel('{{ $level->id }}')">
                    {{ $level->name }} ({{ $level->students_count }})
                </button>
            @endforeach
            @foreach ($classGroups as $group)
                <button type="button" class="es-qtype-chip" @click="toggleClass('{{ $group->id }}')">
                    {{ $group->name }} ({{ $group->students_count }})
                </button>
            @endforeach
        </div>

        <div class="grid gap-3 sm:grid-cols-3">
            <div>
                <label class="es-label text-xs">Filtrer par niveau</label>
                <select x-model="filterLevel" class="es-select">
                    <option value="">Tous les niveaux</option>
                    @foreach ($levels as $level)
                        <option value="{{ $level->id }}">{{ $level->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="es-label text-xs">Filtrer par classe</label>
                <select x-model="filterClass" class="es-select">
                    <option value="">Toutes les classes</option>
                    @foreach ($classGroups as $group)
                        <option value="{{ $group->id }}">{{ $group->display_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="es-label text-xs">Rechercher</label>
                <input type="search" x-model="search" placeholder="Nom, niveau, classe…" class="es-input">
            </div>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-2 text-sm">
            <p class="font-bold text-es-ink">
                <span x-text="selected.length"></span> élève(s) sélectionné(s)
            </p>
            <div class="flex gap-2">
                <button type="button" class="es-link text-sm font-bold" @click="selectVisible()">Sélectionner la liste filtrée</button>
                <button type="button" class="es-link text-sm font-bold text-es-muted" @click="clearSelection()">Tout désélectionner</button>
            </div>
        </div>

        <div class="max-h-72 overflow-y-auto rounded-xl border border-stone-100 divide-y divide-stone-100">
            <template x-for="student in students" :key="student.id">
                <label
                    class="flex items-center gap-3 px-4 py-3 hover:bg-stone-50 cursor-pointer"
                    x-show="visible(student)"
                    x-cloak
                >
                    <input
                        type="checkbox"
                        name="student_ids[]"
                        :value="student.id"
                        class="es-checkbox"
                        :checked="selected.includes(student.id)"
                        @change="$event.target.checked ? selected.push(student.id) : selected = selected.filter(id => id !== student.id)"
                    >
                    <span class="flex-1 min-w-0">
                        <span class="font-bold text-es-ink block truncate" x-text="student.name"></span>
                        <span class="text-xs text-es-muted">
                            <span x-text="student.level || 'Sans niveau'"></span>
                            <template x-if="student.class">
                                <span> · <span x-text="student.class"></span></span>
                            </template>
                        </span>
                    </span>
                </label>
            </template>
        </div>

        @error('student_ids')
            <p class="es-field-error">{{ $message }}</p>
        @enderror
    @endif
</div>
