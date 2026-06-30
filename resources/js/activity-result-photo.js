import { csrfFetch, readErrorMessage } from './csrf-fetch';

/** @type {WeakMap<HTMLElement, { photos: Array<{path: string, url: string}>, uploadPromise: Promise<void>|null }>} */
const panelState = new WeakMap();

function photoUrl(baseShowUrl, path) {
    if (!baseShowUrl || !path) return '';
    const sep = baseShowUrl.includes('?') ? '&' : '?';
    return `${baseShowUrl}${sep}path=${encodeURIComponent(path)}`;
}

export function initResultPhoto(root) {
    const panel = root.querySelector('[data-result-photo-panel]');
    if (!panel || panel.dataset.initialized === '1') {
        return;
    }

    panel.dataset.initialized = '1';

    const uploadUrl = root.dataset.resultPhotoUploadUrl || '';
    const deleteUrl = root.dataset.resultPhotoDeleteUrl || '';
    const showUrl = root.dataset.resultPhotoShowUrl || '';
    const readOnly = root.dataset.readonly === '1' || root.dataset.preview === '1' || root.dataset.correction === '1';

    let initialPhotos = [];
    try {
        initialPhotos = JSON.parse(panel.dataset.resultPhotos || '[]');
    } catch {
        initialPhotos = [];
    }

    const state = {
        photos: initialPhotos.map((path) => ({ path, url: photoUrl(showUrl, path) })),
        uploadPromise: null,
    };
    panelState.set(root, state);

    const fileInput = panel.querySelector('[data-result-photo-input]');
    const gallery = panel.querySelector('[data-result-photo-gallery]');
    const emptyHint = panel.querySelector('[data-result-photo-empty]');
    const takeBtn = panel.querySelector('[data-result-photo-take]');
    const statusEl = panel.querySelector('[data-result-photo-status]');
    const countEl = panel.querySelector('[data-result-photo-count]');

    function syncRootDataset() {
        root.dataset.resultPhotoCount = String(state.photos.length);
    }

    function renderGallery() {
        if (!gallery) return;

        gallery.innerHTML = '';

        state.photos.forEach((photo, index) => {
            const item = document.createElement('div');
            item.className = 'relative rounded-xl border border-stone-200 bg-white overflow-hidden';
            item.dataset.resultPhotoItem = photo.path;

            const img = document.createElement('img');
            img.src = `${photo.url}${photo.url.includes('?') ? '&' : '?'}t=${Date.now()}`;
            img.alt = `Photo ${index + 1}`;
            img.className = 'w-full h-36 object-contain bg-stone-50';
            item.appendChild(img);

            const label = document.createElement('p');
            label.className = 'text-[10px] font-bold text-center text-es-muted py-1';
            label.textContent = `Photo ${index + 1}`;
            item.appendChild(label);

            if (!readOnly) {
                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className = 'absolute top-1 right-1 rounded-lg bg-white/95 px-2 py-0.5 text-[10px] font-bold text-red-600 shadow-sm';
                removeBtn.textContent = 'Suppr.';
                removeBtn.addEventListener('click', () => deletePhoto(photo.path));
                item.appendChild(removeBtn);
            }

            gallery.appendChild(item);
        });

        const hasPhotos = state.photos.length > 0;
        emptyHint?.classList.toggle('hidden', hasPhotos);
        gallery.classList.toggle('hidden', !hasPhotos);
        takeBtn?.classList.toggle('hidden', readOnly);

        if (countEl) {
            countEl.textContent = hasPhotos ? `${state.photos.length} photo(s)` : '';
        }

        syncRootDataset();
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

        const res = await csrfFetch(uploadUrl, { method: 'POST', body: form });
        if (!res.ok) {
            throw new Error(await readErrorMessage(res, 'upload failed'));
        }

        const data = await res.json();
        const path = data.path || '';
        if (path) {
            state.photos.push({ path, url: data.url || photoUrl(showUrl, path) });
        }
        renderGallery();
        setStatus('Photo ajoutée ✓');
    }

    async function deletePhoto(path) {
        if (readOnly || !path) return;
        if (!window.confirm('Supprimer cette photo ?')) return;
        if (!deleteUrl) return;

        try {
            const res = await csrfFetch(deleteUrl, {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
                body: JSON.stringify({ path }),
            });
            if (!res.ok) {
                throw new Error(await readErrorMessage(res, 'delete failed'));
            }
            state.photos = state.photos.filter((p) => p.path !== path);
            renderGallery();
            setStatus('');
        } catch (err) {
            setStatus(err?.message && err.message !== 'delete failed' ? err.message : 'Impossible de supprimer la photo.', true);
        }
    }

    takeBtn?.addEventListener('click', () => fileInput?.click());

    fileInput?.addEventListener('change', () => {
        const file = fileInput.files?.[0];
        if (!file) return;
        state.uploadPromise = uploadPhoto(file).catch((err) => {
            setStatus(err?.message && err.message !== 'upload failed' ? err.message : 'Impossible d\'envoyer la photo.', true);
        }).finally(() => {
            state.uploadPromise = null;
            fileInput.value = '';
        });
    });

    renderGallery();
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
    if (state?.photos?.length) {
        return true;
    }

    const count = parseInt(root.dataset.resultPhotoCount || '0', 10);
    return count > 0;
}

export function toggleResultPhotoPanel(root, visible) {
    const wrapper = root.querySelector('[data-result-photo-wrapper]');
    if (wrapper) {
        wrapper.classList.toggle('hidden', !visible);
    }
}
