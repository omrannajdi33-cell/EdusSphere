/**
 * EduSphere — espaces de travail par matière (lecture, oral, écriture, maths)
 */

import { csrfFetch, readErrorMessage } from './csrf-fetch';

const workspaceState = new WeakMap();

function pickRecorderMime(preferVideo) {
    if (typeof MediaRecorder === 'undefined' || typeof MediaRecorder.isTypeSupported !== 'function') {
        return '';
    }

    const candidates = preferVideo
        ? [
            'video/webm;codecs=vp9,opus',
            'video/webm;codecs=vp8,opus',
            'video/webm',
            'video/mp4',
            'audio/webm;codecs=opus',
            'audio/webm',
            'audio/mp4',
            'audio/mpeg',
        ]
        : [
            'audio/webm;codecs=opus',
            'audio/webm',
            'audio/mp4',
            'audio/mpeg',
            'audio/ogg;codecs=opus',
        ];

    return candidates.find((type) => MediaRecorder.isTypeSupported(type)) ?? '';
}

function extensionForMime(mime) {
    if (mime.includes('mp4')) {
        return 'mp4';
    }
    if (mime.includes('mpeg')) {
        return 'mp3';
    }
    if (mime.includes('ogg')) {
        return 'ogg';
    }

    return 'webm';
}

export function initSubjectWorkspaces(root) {
    root.querySelectorAll('[data-reading-panel]').forEach(initReadingPanel);
    root.querySelectorAll('[data-oral-panel]').forEach((panel) => initOralPanel(panel, root));
    root.querySelectorAll('[data-rich-panel]').forEach(initRichPanel);
}

export function collectWorkspaceData(pageEl) {
    const reading = pageEl.querySelector('[data-reading-panel]');
    if (reading) {
        return {
            text_hidden: reading.dataset.textHidden === '1',
            notes: reading.querySelector('.player-workspace-notes')?.value ?? '',
        };
    }

    const oral = pageEl.querySelector('[data-oral-panel]');
    if (oral) {
        const state = workspaceState.get(oral) ?? {};
        return {
            recording_path: state.recordingPath ?? oral.dataset.recordingPath ?? '',
            recording_kind: state.recordingKind ?? oral.dataset.recordingKind ?? 'audio',
            recording_url: state.recordingUrl ?? '',
        };
    }

    const rich = pageEl.querySelector('[data-rich-panel]');
    if (rich) {
        const editor = rich.querySelector('[data-rich-editor]');
        return {
            rich_mode: rich.dataset.richMode ?? 'text',
            rich_html: editor?.innerHTML ?? '',
        };
    }

    if (pageEl.querySelector('[data-workspace-root]')) {
        return {};
    }

    return null;
}

export async function waitForPendingUploads(root) {
    const promises = [...root.querySelectorAll('[data-oral-panel]')]
        .map((panel) => workspaceState.get(panel)?.uploadPromise)
        .filter(Boolean);

    await Promise.all(promises);
}

function initReadingPanel(panel) {
    const toggle = panel.querySelector('.workspace-toggle-text');
    const textEl = panel.querySelector('[data-reading-text]');
    const notes = panel.querySelector('.player-workspace-notes');

    toggle?.addEventListener('click', () => {
        const wasHidden = panel.dataset.textHidden === '1';
        panel.dataset.textHidden = wasHidden ? '0' : '1';
        textEl?.classList.toggle('hidden', panel.dataset.textHidden === '1');
        toggle.textContent = panel.dataset.textHidden === '1' ? toggle.dataset.labelShow : toggle.dataset.labelHide;
        markPlayerDirty(panel);
    });

    notes?.addEventListener('input', () => markPlayerDirty(panel));
}

function initOralPanel(panel, root) {
    const uploadUrl = root.dataset.recordingUrl;
    const pageId = panel.closest('[data-page]')?.dataset.pageId;
    const statusEl = panel.querySelector('.oral-status');
    const preview = panel.querySelector('[data-oral-preview]');
    const liveVideo = panel.querySelector('.oral-live-video');
    const btnAudio = panel.querySelector('.oral-record-audio');
    const btnVideo = panel.querySelector('.oral-record-video');
    const btnStop = panel.querySelector('.oral-stop');

    let mediaRecorder = null;
    let chunks = [];
    let stream = null;
    let kind = 'audio';
    let recorderMime = '';

    const state = {
        recordingPath: panel.dataset.recordingPath || '',
        recordingKind: panel.dataset.recordingKind || 'audio',
        recordingUrl: preview?.querySelector('audio,video')?.src ?? '',
        uploadPromise: null,
        isRecording: false,
    };
    workspaceState.set(panel, state);

    function setStatus(message, isError = false) {
        if (!statusEl) {
            return;
        }
        statusEl.textContent = message;
        statusEl.classList.toggle('text-red-600', isError);
        statusEl.classList.toggle('text-es-muted', !isError);
    }

    function mediaUnavailableMessage(err) {
        if (!navigator.mediaDevices?.getUserMedia) {
            return 'Micro/caméra indisponible — utilise Chrome ou Safari récent en HTTPS.';
        }
        if (err?.name === 'NotAllowedError') {
            return 'Autorise le micro et la caméra dans les réglages du navigateur.';
        }
        if (err?.name === 'NotFoundError') {
            return 'Aucun micro ou caméra détecté sur cet appareil.';
        }

        return 'Micro ou caméra non disponible.';
    }

    async function startRecording(useVideo) {
        if (state.isRecording) {
            return;
        }

        kind = useVideo ? 'video' : 'audio';
        recorderMime = pickRecorderMime(useVideo);

        if (!recorderMime) {
            setStatus('Enregistrement non supporté par ce navigateur.', true);
            return;
        }

        try {
            stream = await navigator.mediaDevices.getUserMedia(
                useVideo ? { video: { facingMode: 'user' }, audio: true } : { audio: true },
            );

            if (useVideo && liveVideo) {
                liveVideo.srcObject = stream;
                liveVideo.muted = true;
                liveVideo.playsInline = true;
                liveVideo.classList.remove('hidden');
                try {
                    await liveVideo.play();
                } catch {
                    /* autoplay bloqué — preview live optionnelle */
                }
            }

            mediaRecorder = new MediaRecorder(stream, { mimeType: recorderMime });
            chunks = [];
            mediaRecorder.ondataavailable = (e) => {
                if (e.data.size > 0) {
                    chunks.push(e.data);
                }
            };
            mediaRecorder.onstop = () => {
                state.isRecording = false;
                state.uploadPromise = uploadRecording();
            };
            mediaRecorder.start(250);
            state.isRecording = true;

            setStatus(useVideo ? 'Enregistrement vidéo en cours…' : 'Enregistrement audio en cours…');
            btnAudio?.setAttribute('disabled', 'disabled');
            btnVideo?.setAttribute('disabled', 'disabled');
            btnStop?.classList.remove('hidden');
        } catch (err) {
            setStatus(mediaUnavailableMessage(err), true);
            stream?.getTracks().forEach((t) => t.stop());
            stream = null;
        }
    }

    function stopRecording() {
        if (!mediaRecorder || mediaRecorder.state === 'inactive') {
            return;
        }

        mediaRecorder.stop();
        stream?.getTracks().forEach((t) => t.stop());
        stream = null;
        liveVideo?.classList.add('hidden');
        if (liveVideo) {
            liveVideo.srcObject = null;
        }
        btnStop?.classList.add('hidden');
        btnAudio?.removeAttribute('disabled');
        btnVideo?.removeAttribute('disabled');
    }

    async function uploadRecording() {
        if (!uploadUrl || !pageId || chunks.length === 0) {
            return;
        }

        const ext = extensionForMime(recorderMime || (kind === 'video' ? 'video/webm' : 'audio/webm'));
        const blob = new Blob(chunks, { type: recorderMime || (kind === 'video' ? 'video/webm' : 'audio/webm') });
        const form = new FormData();
        form.append('page_id', pageId);
        form.append('kind', kind);
        form.append('recording', blob, `${kind}.${ext}`);

        setStatus('Envoi de l\'enregistrement…');

        try {
            const res = await csrfFetch(uploadUrl, {
                method: 'POST',
                body: form,
            });
            if (!res.ok) {
                throw new Error(await readErrorMessage(res, 'upload failed'));
            }
            const data = await res.json();
            state.recordingPath = data.path;
            state.recordingKind = data.kind;
            state.recordingUrl = data.url;
            panel.dataset.recordingPath = data.path;
            panel.dataset.recordingKind = data.kind;

            if (preview) {
                preview.classList.remove('hidden');
                preview.innerHTML = kind === 'video'
                    ? `<video controls class="w-full max-h-64 rounded-xl" src="${data.url}"></video>`
                    : `<audio controls class="w-full" src="${data.url}"></audio>`;
            }

            setStatus('Enregistrement sauvegardé ✓');
            markPlayerDirty(panel);
        } catch (err) {
            setStatus(
                err?.message && err.message !== 'upload failed'
                    ? err.message
                    : 'Erreur lors de l\'envoi.',
                true,
            );
        } finally {
            state.uploadPromise = null;
            chunks = [];
        }
    }

    btnAudio?.addEventListener('click', () => startRecording(false));
    btnVideo?.addEventListener('click', () => startRecording(true));
    btnStop?.addEventListener('click', stopRecording);
}

function initRichPanel(panel) {
    panel.querySelectorAll('.rich-tab').forEach((tab) => {
        tab.addEventListener('click', () => {
            const mode = tab.dataset.mode;
            panel.dataset.richMode = mode;
            panel.querySelector('[data-rich-editor]')?.classList.toggle('hidden', mode !== 'text');
            panel.querySelector('[data-rich-toolbar]')?.classList.toggle('hidden', mode !== 'text');
            panel.querySelector('[data-rich-draw-wrap]')?.classList.toggle('hidden', mode !== 'draw');
            panel.querySelectorAll('.rich-tab').forEach((t) => {
                t.classList.toggle('es-btn-primary', t.dataset.mode === mode);
                t.classList.toggle('es-btn-secondary', t.dataset.mode !== mode);
            });
            const toolbar = document.getElementById('player-toolbar');
            if (toolbar) {
                toolbar.classList.toggle('hidden', mode !== 'draw');
            }
            markPlayerDirty(panel);
        });
    });

    panel.querySelectorAll('.rich-cmd').forEach((btn) => {
        btn.addEventListener('click', () => {
            document.execCommand(btn.dataset.cmd, false, null);
            panel.querySelector('[data-rich-editor]')?.focus();
            markPlayerDirty(panel);
        });
    });

    panel.querySelector('[data-rich-editor]')?.addEventListener('input', () => markPlayerDirty(panel));
}

function markPlayerDirty(el) {
    el.closest('#activity-player')?.dispatchEvent(new CustomEvent('activity-player:dirty'));
}
