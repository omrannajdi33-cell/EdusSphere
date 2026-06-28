@extends('layouts.student')

@section('student-content')
<div
    class="es-page-enter es-points-page"
    x-data="studentRewards({
        redeemUrl: @js(route('student.points.redeem')),
        csrf: @js(csrf_token()),
        total: @js($total),
    })"
>
    <x-student-points-hero :total="$total" :rewards-count="$rewards->count()" class="mb-8" />

    @if (session('success'))
        <x-alert type="success" class="mb-6 es-points-success-pop">{{ session('success') }}</x-alert>
    @endif

    <section class="mb-10">
        <div class="flex items-center gap-3 mb-5">
            <span class="es-points-section-emoji" aria-hidden="true">🎁</span>
            <div>
                <h2 class="text-2xl font-black text-es-ink">Récompenses</h2>
                <p class="text-sm font-bold text-es-muted">Échange tes points contre des surprises</p>
            </div>
        </div>

        @if ($rewards->isEmpty())
            <div class="es-points-empty-bubble">
                <p class="text-lg font-extrabold text-es-ink">Pas encore de récompenses</p>
                <p class="text-sm text-es-muted mt-2">Continue à gagner des points — ton prof va en ajouter bientôt !</p>
            </div>
        @else
            <p x-show="feedback" x-text="feedback" x-transition class="mb-4 text-sm font-bold text-emerald-700 bg-emerald-50 rounded-2xl px-4 py-3 border border-emerald-200"></p>
            <ul class="grid gap-4 sm:grid-cols-2">
                @foreach ($rewards as $reward)
                    @php
                        $canAfford = $total >= $reward->cost;
                        $emoji = match (true) {
                            str_contains(mb_strtolower($reward->name), 'jeu') => '🎮',
                            str_contains(mb_strtolower($reward->name), 'bonbon') || str_contains(mb_strtolower($reward->name), 'surprise') => '🍬',
                            str_contains(mb_strtolower($reward->name), 'autocollant') => '🏷️',
                            str_contains(mb_strtolower($reward->name), 'privilège') => '👑',
                            default => '🎁',
                        };
                    @endphp
                    <li @class([
                        'es-reward-bubble',
                        'es-reward-bubble-ready' => $canAfford,
                    ])>
                        <div class="flex items-start gap-4">
                            <span class="es-reward-bubble-emoji" aria-hidden="true">{{ $emoji }}</span>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-2">
                                    <p class="font-black text-xl text-es-ink">{{ $reward->name }}</p>
                                    <span class="es-behavior-reward-cost shrink-0">{{ $reward->cost }} pts</span>
                                </div>
                                @if ($reward->description)
                                    <p class="text-sm text-es-muted mt-1">{{ $reward->description }}</p>
                                @endif
                            </div>
                        </div>
                        <button
                            type="button"
                            @class([
                                'es-btn es-btn-sm w-full mt-4',
                                'es-btn-primary es-reward-btn-glow' => $canAfford,
                                'es-btn-secondary opacity-70 cursor-not-allowed' => ! $canAfford,
                            ])
                            :disabled="loading || total < {{ $reward->cost }}"
                            @click="redeem({{ $reward->id }}, @js($reward->name), {{ $reward->cost }})"
                        >
                            <span x-show="total >= {{ $reward->cost }}">✨ Utiliser mes points</span>
                            <span x-show="total < {{ $reward->cost }}" x-text="'Encore ' + ({{ $reward->cost }} - total) + ' pt(s)'"></span>
                        </button>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    <section>
        <div class="flex items-center gap-3 mb-5">
            <span class="es-points-section-emoji" aria-hidden="true">📜</span>
            <div>
                <h2 class="text-2xl font-black text-es-ink">Historique</h2>
                <p class="text-sm font-bold text-es-muted">Tes bons points et rappels à l'ordre</p>
            </div>
        </div>

        @if ($history->isEmpty())
            <div class="es-points-empty-bubble">
                <p class="text-lg font-extrabold text-es-ink">Aucun mouvement pour l'instant</p>
                <p class="text-sm text-es-muted mt-2">Continue comme ça ! 🌟</p>
            </div>
        @else
            <ul class="es-points-timeline">
                @foreach ($history as $entry)
                    @php
                        $positive = $entry['value'] > 0;
                        $isReward = ($entry['kind'] ?? '') === 'redemption';
                    @endphp
                    <li class="es-points-timeline-item">
                        <span @class([
                            'es-behavior-history-badge shrink-0',
                            'es-behavior-history-badge-good' => $positive && ! $isReward,
                            'es-behavior-history-badge-bad' => ! $positive && ! $isReward,
                            'es-behavior-history-badge-reward' => $isReward,
                        ])>
                            {{ $positive ? '+' : '' }}{{ $entry['value'] }}
                        </span>
                        <div class="flex-1 min-w-0 es-points-timeline-body">
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
    </section>
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
