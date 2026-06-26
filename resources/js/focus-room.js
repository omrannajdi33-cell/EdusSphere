/**
 * EduSphere — mode salle d'examen (plein écran, verrouillage, main levée)
 */
import { csrfFetch } from './csrf-fetch';

export function initFocusRoom(root) {
    if (!root || root.dataset.focusInitialized === '1') {
        return;
    }
    root.dataset.focusInitialized = '1';

    const gate = root.querySelector('[data-focus-gate]');
    const shell = root.querySelector('[data-focus-shell]');
    const handBtn = root.querySelector('[data-hand-raise]');
    const handUrl = root.dataset.handRaiseUrl || '';
    const warnEl = root.querySelector('[data-focus-warn]');

    let handCooldown = false;

    const enter = async () => {
        gate?.classList.add('hidden');
        shell?.classList.remove('hidden');
        try {
            await root.requestFullscreen?.();
        } catch {
            /* plein écran refusé — on continue quand même */
        }
    };

    gate?.querySelector('[data-focus-start]')?.addEventListener('click', enter);

    window.addEventListener('beforeunload', (e) => {
        if (shell && !shell.classList.contains('hidden')) {
            e.preventDefault();
            e.returnValue = '';
        }
    });

    document.addEventListener('visibilitychange', () => {
        if (document.hidden && shell && !shell.classList.contains('hidden')) {
            flashWarn(warnEl);
        }
    });

    document.addEventListener('fullscreenchange', () => {
        if (!document.fullscreenElement && shell && !shell.classList.contains('hidden')) {
            flashWarn(warnEl, 'Reste en plein écran pendant l\'épreuve.');
        }
    });

    handBtn?.addEventListener('click', async () => {
        if (!handUrl || handCooldown) {
            return;
        }
        handCooldown = true;
        playHandSound();
        handBtn.disabled = true;
        handBtn.textContent = 'Prof notifié ✓';

        try {
            await csrfFetch(handUrl, { method: 'POST' });
        } catch {
            handBtn.textContent = 'Erreur — réessaie';
            handCooldown = false;
            handBtn.disabled = false;

            return;
        }

        setTimeout(() => {
            handBtn.textContent = '✋ J\'ai une question';
            handBtn.disabled = false;
            handCooldown = false;
        }, 15000);
    });
}

function flashWarn(el, msg = 'Ne quitte pas la page !') {
    if (!el) {
        return;
    }
    el.textContent = msg;
    el.classList.remove('hidden');
    el.classList.add('es-animate-in');
    setTimeout(() => el.classList.add('hidden'), 4000);
}

function playHandSound() {
    try {
        const ctx = new (window.AudioContext || window.webkitAudioContext)();
        [0, 0.15].forEach((delay) => {
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.frequency.value = 880;
            gain.gain.value = 0.15;
            osc.start(ctx.currentTime + delay);
            osc.stop(ctx.currentTime + delay + 0.12);
        });
    } catch {
        /* audio non disponible */
    }
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-focus-room]').forEach(initFocusRoom);
});
