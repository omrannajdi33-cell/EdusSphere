@extends('layouts.student')

@section('student-content')
<div
    class="es-page-enter"
    x-data="studentRewards({
        redeemUrl: @js(route('student.points.redeem')),
        csrf: @js(csrf_token()),
        total: @js($total),
    })"
>
    <div class="mb-8">
        <h1 class="es-page-title">Mes points</h1>
        <p class="es-page-subtitle">Gagne des points et échange-les contre des récompenses</p>
    </div>

    @if (session('success'))
        <x-alert type="success" class="mb-6">{{ session('success') }}</x-alert>
    @endif

    <div class="es-behavior-student-hero mb-8">
        <p class="text-base font-extrabold text-es-muted uppercase tracking-wide">Solde disponible</p>
        <p
            class="es-points-value"
            :class="total >= 0 ? 'text-emerald-600' : 'text-red-600'"
            x-text="formatTotal(total)"
        >
            {{ $total >= 0 ? '+' : '' }}{{ $total }}
        </p>
        <p class="text-sm font-bold text-es-muted mt-2">points comportement</p>
    </div>

    <x-card title="Récompenses" class="mb-8">
        @if ($rewards->isEmpty())
            <p class="text-es-muted">Aucune récompense disponible pour le moment.</p>
        @else
            <p x-show="feedback" x-text="feedback" class="mb-4 text-sm font-bold text-emerald-700 bg-emerald-50 rounded-xl px-4 py-2"></p>
            <ul class="grid gap-4 sm:grid-cols-2">
                @foreach ($rewards as $reward)
                    <li class="es-behavior-reward-card flex flex-col">
                        <div class="flex items-start justify-between gap-3 mb-3">
                            <div class="flex-1 min-w-0">
                                <p class="font-extrabold text-lg text-es-ink">{{ $reward->name }}</p>
                                @if ($reward->description)
                                    <p class="text-sm text-es-muted mt-1">{{ $reward->description }}</p>
                                @endif
                            </div>
                            <span class="es-behavior-reward-cost shrink-0">{{ $reward->cost }} pts</span>
                        </div>
                        <button
                            type="button"
                            class="es-btn es-btn-primary es-btn-sm mt-auto w-full sm:w-auto"
                            :disabled="loading || total < {{ $reward->cost }}"
                            @click="redeem({{ $reward->id }}, @js($reward->name), {{ $reward->cost }})"
                        >
                            <span x-show="total >= {{ $reward->cost }}">Utiliser mes points</span>
                            <span x-show="total < {{ $reward->cost }}">Pas assez de points</span>
                        </button>
                    </li>
                @endforeach
            </ul>
        @endif
    </x-card>

    <x-card title="Historique">
        @if ($history->isEmpty())
            <p class="text-es-muted">Aucun mouvement pour le moment. Continue comme ça !</p>
        @else
            <ul class="divide-y divide-stone-100">
                @foreach ($history as $entry)
                    @php
                        $positive = $entry['value'] > 0;
                        $isReward = ($entry['kind'] ?? '') === 'redemption';
                    @endphp
                    <li class="py-4 flex items-start gap-4">
                        <span @class([
                            'es-behavior-history-badge shrink-0',
                            'es-behavior-history-badge-good' => $positive && ! $isReward,
                            'es-behavior-history-badge-bad' => ! $positive && ! $isReward,
                            'es-behavior-history-badge-reward' => $isReward,
                        ])>
                            {{ $positive ? '+' : '' }}{{ $entry['value'] }}
                        </span>
                        <div class="flex-1 min-w-0">
                            <p class="font-extrabold text-es-ink">
                                {{ $entry['label'] }}
                                @if ($isReward)
                                    <span class="text-xs font-bold text-indigo-600 ml-1">· Récompense</span>
                                @endif
                            </p>
                            @if (! empty($entry['description']))
                                <p class="text-sm text-es-muted mt-0.5">{{ $entry['description'] }}</p>
                            @endif
                            <p class="text-xs font-bold text-es-muted mt-2">
                                {{ $entry['at']?->translatedFormat('d M Y · H:i') }}
                            </p>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </x-card>
</div>
@endsection

@push('scripts')
<script>
function studentRewards(config) {
    return {
        total: config.total,
        loading: false,
        feedback: '',
        formatTotal(value) {
            const n = Number(value) || 0;
            return (n > 0 ? '+' : '') + n;
        },
        async redeem(rewardId, name, cost) {
            if (this.loading || this.total < cost) return;
            if (! confirm(`Utiliser ${cost} pts pour « ${name} » ?`)) return;

            this.loading = true;
            this.feedback = '';
            try {
                const res = await fetch(config.redeemUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': config.csrf,
                    },
                    body: JSON.stringify({ reward_id: rewardId }),
                });
                const data = await res.json();
                if (!res.ok) throw new Error(data.message || Object.values(data.errors || {})[0]?.[0] || 'Erreur');
                this.total = data.total;
                this.feedback = data.message;
                setTimeout(() => window.location.reload(), 800);
            } catch (e) {
                this.feedback = e.message || 'Impossible d\'échanger cette récompense.';
            } finally {
                this.loading = false;
            }
        },
    };
}
</script>
@endpush
