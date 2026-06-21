@props(['title' => null])

<div {{ $attributes->merge(['class' => 'rounded-3xl bg-white p-6 shadow-sm border border-slate-100']) }}>
    @if ($title)
        <h3 class="text-lg font-semibold mb-3">{{ $title }}</h3>
    @endif
    {{ $slot }}
</div>
