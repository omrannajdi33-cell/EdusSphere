@extends('layouts.admin')

@section('admin-content')
<div class="es-page-enter">
    <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6 mb-8">
        <div>
            <h1 class="es-page-title">Notions</h1>
            <p class="es-page-subtitle">Organise les notions par catégories — assignables à l'horaire, aux activités, examens et projets.</p>
        </div>
    </div>

    @if (session('success'))
        <x-alert type="success" class="mb-6">{{ session('success') }}</x-alert>
    @endif

    @if ($errors->any())
        <x-alert type="error" class="mb-6" title="Corrige ces points :">
            <ul class="list-disc pl-5 space-y-1 text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-alert>
    @endif

    <x-card class="mb-8 !p-4">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="min-w-[12rem]">
                <label class="es-label">Matière</label>
                <select name="subject" class="es-select" onchange="this.form.submit()">
                    @foreach ($subjects as $s)
                        <option value="{{ $s->id }}" @selected($subject?->id === $s->id)>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </x-card>

    @if ($subject)
        <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_320px]">
            <div class="space-y-6">
                @forelse ($categories as $category)
                    <section class="es-card p-6">
                        <div class="flex flex-wrap items-start justify-between gap-4 mb-4">
                            <div>
                                <h2 class="text-xl font-black text-es-ink">{{ $category->name }}</h2>
                                @if ($category->skill)
                                    <p class="text-sm text-es-muted mt-1">Compétence : {{ $category->skill->name }}</p>
                                @endif
                                @if ($category->description)
                                    <p class="text-sm text-es-muted mt-2">{{ $category->description }}</p>
                                @endif
                            </div>
                            <div class="flex gap-2">
                                <details class="relative">
                                    <summary class="es-btn es-btn-secondary es-btn-sm cursor-pointer list-none">Modifier</summary>
                                    <div class="absolute right-0 z-10 mt-2 w-72 es-card p-4 shadow-es">
                                        <form method="POST" action="{{ route('admin.notion-categories.update', $category) }}" class="space-y-3">
                                            @csrf @method('PUT')
                                            <x-input label="Nom" name="name" value="{{ $category->name }}" required/>
                                            <div>
                                                <label class="es-label">Compétence (optionnel)</label>
                                                <select name="skill_id" class="es-select">
                                                    <option value="">— Aucune —</option>
                                                    @foreach ($subject->skills as $skill)
                                                        <option value="{{ $skill->id }}" @selected($category->skill_id == $skill->id)>{{ $skill->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <textarea name="description" rows="2" class="es-textarea" placeholder="Description courte">{{ $category->description }}</textarea>
                                            <x-button type="submit" class="w-full es-btn-sm">Enregistrer</x-button>
                                        </form>
                                    </div>
                                </details>
                                <form method="POST" action="{{ route('admin.notion-categories.destroy', $category) }}" onsubmit="return confirm('Supprimer cette catégorie et toutes ses notions ?')">
                                    @csrf @method('DELETE')
                                    <x-button type="submit" variant="danger" class="es-btn-sm">Suppr.</x-button>
                                </form>
                            </div>
                        </div>

                        <div class="space-y-4">
                            @forelse ($category->notions as $notion)
                                <article class="rounded-2xl border border-stone-200 bg-stone-50/60 p-4">
                                    <div class="flex flex-wrap items-start justify-between gap-3 mb-2">
                                        <h3 class="font-extrabold text-es-ink">{{ $notion->title }}</h3>
                                        <div class="flex gap-2">
                                            <details>
                                                <summary class="text-sm font-bold text-es-primary cursor-pointer list-none">Éditer</summary>
                                                <form method="POST" action="{{ route('admin.notions.update', $notion) }}" class="mt-3 space-y-2 min-w-[16rem]">
                                                    @csrf @method('PUT')
                                                    <x-input name="title" value="{{ $notion->title }}" required/>
                                                    <textarea name="content" rows="4" class="es-textarea" required>{{ $notion->content }}</textarea>
                                                    <x-button type="submit" class="es-btn-sm">Enregistrer</x-button>
                                                </form>
                                            </details>
                                            <form method="POST" action="{{ route('admin.notions.destroy', $notion) }}" onsubmit="return confirm('Supprimer cette notion ?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-xs font-bold text-red-600">Suppr.</button>
                                            </form>
                                        </div>
                                    </div>
                                    <p class="text-sm text-es-ink whitespace-pre-wrap leading-relaxed">{{ $notion->content }}</p>
                                </article>
                            @empty
                                <p class="text-sm text-es-muted">Aucune notion dans cette catégorie.</p>
                            @endforelse
                        </div>

                        <form method="POST" action="{{ route('admin.notions.store') }}" class="mt-5 pt-5 border-t border-stone-200 space-y-3">
                            @csrf
                            <input type="hidden" name="notion_category_id" value="{{ $category->id }}">
                            <p class="font-extrabold text-sm text-es-ink">+ Ajouter une notion</p>
                            <x-input name="title" placeholder="Titre de la notion" required/>
                            <textarea name="content" rows="3" class="es-textarea" placeholder="Paragraphe : ce que l'élève doit apprendre…" required></textarea>
                            <x-button type="submit" variant="secondary" class="es-btn-sm">Ajouter la notion</x-button>
                        </form>
                    </section>
                @empty
                    <div class="es-empty">
                        <p class="font-extrabold">Aucune catégorie pour {{ $subject->name }}</p>
                        <p class="text-es-muted mt-2">Crée une première catégorie à droite (ex. « Grammaire », « Géométrie »).</p>
                    </div>
                @endforelse
            </div>

            <aside class="space-y-6">
                <div class="es-card p-5">
                    <h2 class="font-extrabold text-lg mb-4">Nouvelle catégorie</h2>
                    <form method="POST" action="{{ route('admin.notion-categories.store') }}" class="space-y-4">
                        @csrf
                        <input type="hidden" name="subject_id" value="{{ $subject->id }}">
                        <x-input label="Nom de la catégorie" name="name" placeholder="Ex. Conjugaison" required/>
                        <div>
                            <label class="es-label">Compétence liée (optionnel)</label>
                            <select name="skill_id" class="es-select">
                                <option value="">— Aucune —</option>
                                @foreach ($subject->skills as $skill)
                                    <option value="{{ $skill->id }}">{{ $skill->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <textarea name="description" rows="2" class="es-textarea" placeholder="Description courte (optionnel)"></textarea>
                        <x-button type="submit" class="w-full">Créer la catégorie</x-button>
                    </form>
                </div>

                <div class="es-card p-5 text-sm text-es-muted space-y-2">
                    <p class="font-extrabold text-es-ink">Comment utiliser les notions ?</p>
                    <ul class="list-disc pl-5 space-y-1">
                        <li>Crée des catégories par thème.</li>
                        <li>Ajoute des paragraphes pour chaque notion.</li>
                        <li>Assigne-les depuis l'<strong>Horaire</strong>, ou plus tard depuis activités / examens / projets.</li>
                    </ul>
                </div>
            </aside>
        </div>
    @endif
</div>
@endsection
