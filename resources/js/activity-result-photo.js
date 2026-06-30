import { csrfFetch, readErrorMessage } from './csrf-fetch';

/** @type {WeakMap<HTMLElement, { path: string, uploadPromise: Promise<void>|null }>} */
const panelState = new WeakMap();

export function initResultPhoto(root) {
    const panel = root.querySelector('[data-result-photo-panel]');
    if (!panel || panel.dataset.initialized === '1') {
        return;
    }

    panel.dataset.initialized = '1';

    const uploadUrl = root.dataset.resultPhotoUploadUrl || '';
    const readOnly = root.dataset.readonly === '1' || root.dataset.preview === '1' || root.dataset.correction === '1';
    const state = { path: panel.dataset.resultPhotoPath || '', uploadPromise: null };
    panelState.set(root, state);

    const fileInput = panel.querySelector('[data-result-photo-input]');
    const preview = panel.querySelector('[data-result-photo-preview]');
    const previewImg = preview?.querySelector('img');
    const emptyHint = panel.querySelector('[data-result-photo-empty]');
    const takeBtn = panel.querySelector('[data-result-photo-take]');
    const removeBtn = panel.querySelector('[data-result-photo-remove]');
    const statusEl = panel.querySelector('[data-result-photo-status]');

    function syncUi() {
        const hasPhoto = Boolean(state.path);
        preview?.classList.toggle('hidden', !hasPhoto);
        emptyHint?.classList.toggle('hidden', hasPhoto);
        removeBtn?.classList.toggle('hidden', !hasPhoto || readOnly);
        takeBtn?.classList.toggle('hidden', readOnly);
        root.dataset.resultPhotoPath = state.path;
    }

    function setStatus(message, isError = false) {
        if (!statusEl) return;
        statusEl.textContent = message;
        statusEl.classList.toggle('text-red-600', isError);
        statusEl.classList.toggle('text-emerald-600', !isError && message.includes('✓'));
    }

    async function uploadPhoto(file) {
        if (!uploadUrl || readOnly || !file) return;

        setStatus('Envoi de la photo…');

        const form = new FormData();
        form.append('photo', file);

        try {
            const res = await csrfFetch(uploadUrl, { method: 'POST', body: form });
            if (!res.ok) {
                throw new Error(await readErrorMessage(res, 'upload failed'));
            }
            const data = await res.json();
            state.path = data.path || '';
            panel.dataset.resultPhotoPath = state.path;
            if (previewImg && data.url) {
                previewImg.src = `${data.url}${data.url.includes('?') ? '&' : '?'}t=${Date.now()}`;
            }
            syncUi();
            setStatus('Photo enregistrée ✓');
        } catch (err) {
            setStatus(err?.message && err.message !== 'upload failed' ? err.message : 'Impossible d\'envoyer la photo.', true);
            throw err;
        }
    }

    takeBtn?.addEventListener('click', () => fileInput?.click());

    fileInput?.addEventListener('change', () => {
        const file = fileInput.files?.[0];
        if (!file) return;
        state.uploadPromise = uploadPhoto(file).finally(() => {
            state.uploadPromise = null;
            fileInput.value = '';
        });
    });

    removeBtn?.addEventListener('click', async () => {
        if (readOnly || !state.path) return;
        if (!window.confirm('Supprimer cette photo ?')) return;

        const deleteUrl = root.dataset.resultPhotoDeleteUrl;
        if (!deleteUrl) return;

        try {
            const res = await csrfFetch(deleteUrl, { method: 'DELETE' });
            if (!res.ok) {
                throw new Error(await readErrorMessage(res, 'delete failed'));
            }
            state.path = '';
            panel.dataset.resultPhotoPath = '';
            if (previewImg) previewImg.removeAttribute('src');
            syncUi();
            setStatus('');
        } catch (err) {
            setStatus(err?.message && err.message !== 'delete failed' ? err.message : 'Impossible de supprimer la photo.', true);
        }
    });

    syncUi();
}

export function waitForPendingResultPhoto(root) {
    const state = panelState.get(root);
    return state?.uploadPromise ?? Promise.resolve();
}

export function hasResultPhoto(root) {
    if (root.dataset.requireResultPhoto !== '1') {
        return true;
    }

    const state = panelState.get(root);
    return Boolean(state?.path || root.dataset.resultPhotoPath);
}

export function toggleResultPhotoPanel(root, visible) {
    const wrapper = root.querySelector('[data-result-photo-wrapper]');
    if (wrapper) {
        wrapper.classList.toggle('hidden', !visible);
    }
}
