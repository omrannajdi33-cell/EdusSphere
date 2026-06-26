/**
 * EduSphere — espaces de travail par matière (lecture, oral, écriture, maths)
 */

import { csrfFetch } from './csrf-fetch';

const workspaceState = new WeakMap();

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

    return null;
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
    const pageId = panel.closest('[data-page-id]')?.dataset.pageId;
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

    workspaceState.set(panel, {
        recordingPath: panel.dataset.recordingPath || '',
        recordingKind: panel.dataset.recordingKind || 'audio',
        recordingUrl: preview?.querySelector('audio,video')?.src ?? '',
    });

    async function startRecording(useVideo) {
        kind = useVideo ? 'video' : 'audio';
        try {
            stream = await navigator.mediaDevices.getUserMedia(useVideo ? { video: true, audio: true } : { audio: true });
            if (useVideo && liveVideo) {
                liveVideo.srcObject = stream;
                liveVideo.classList.remove('hidden');
            }
            mediaRecorder = new MediaRecorder(stream);
            chunks = [];
            mediaRecorder.ondataavailable = (e) => {
                if (e.data.size > 0) {
                    chunks.push(e.data);
                }
            };
            mediaRecorder.onstop = () => uploadRecording();
            mediaRecorder.start();
            if (statusEl) {
                statusEl.textContent = useVideo ? 'Enregistrement vidéo…' : 'Enregistrement audio…';
            }
            btnStop?.classList.remove('hidden');
        } catch {
            if (statusEl) {
                statusEl.textContent = 'Micro ou caméra non disponible.';
            }
        }
    }

    function stopRecording() {
        mediaRecorder?.stop();
        stream?.getTracks().forEach((t) => t.stop());
        liveVideo?.classList.add('hidden');
        btnStop?.classList.add('hidden');
    }

    async function uploadRecording() {
        if (! uploadUrl || ! pageId || chunks.length === 0) {
            return;
        }
        const blob = new Blob(chunks, { type: kind === 'video' ? 'video/webm' : 'audio/webm' });
        const form = new FormData();
        form.append('page_id', pageId);
        form.append('kind', kind);
        form.append('recording', blob, kind === 'video' ? 'video.webm' : 'audio.webm');

        if (statusEl) {
            statusEl.textContent = 'Envoi en cours…';
        }

        try {
            const res = await csrfFetch(uploadUrl, {
                method: 'POST',
                body: form,
            });
            if (! res.ok) {
                throw new Error('upload failed');
            }
            const data = await res.json();
            workspaceState.set(panel, {
                recordingPath: data.path,
                recordingKind: data.kind,
                recordingUrl: data.url,
            });
            if (preview) {
                preview.classList.remove('hidden');
                preview.innerHTML = kind === 'video'
                    ? `<video controls class="w-full max-h-64 rounded-xl" src="${data.url}"></video>`
                    : `<audio controls class="w-full" src="${data.url}"></audio>`;
            }
            if (statusEl) {
                statusEl.textContent = 'Enregistrement sauvegardé ✓';
            }
            markPlayerDirty(panel);
        } catch {
            if (statusEl) {
                statusEl.textContent = 'Erreur lors de l\'envoi.';
            }
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
