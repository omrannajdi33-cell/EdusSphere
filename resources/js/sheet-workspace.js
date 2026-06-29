/**
 * EduSphere — feuilles plein écran (PDF empilé + scroll 2D)
 */
import * as pdfjsLib from 'pdfjs-dist';

pdfjsLib.GlobalWorkerOptions.workerSrc = `https://cdn.jsdelivr.net/npm/pdfjs-dist@${pdfjsLib.version}/build/pdf.worker.min.mjs`;

const sheetState = new WeakMap();

export function initSheetWorkspaces(root) {
    bindSheetUi(root);
    root.querySelectorAll('[data-page]:not(.hidden):not([hidden]) [data-sheet-surface]').forEach((surface) => {
        ensureSheetSurfaceReady(surface, root);
    });
}

export function ensureSheetPageReady(pageEl, root) {
    const surface = pageEl.querySelector('[data-sheet-surface]');
    if (surface) {
        ensureSheetSurfaceReady(surface, root || pageEl.closest('#activity-player'));
    }
}

function bindSheetUi(root) {
    root.querySelectorAll('[data-page-brief-open]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const id = btn.dataset.pageBriefOpen;
            const panel = root.querySelector(`[data-page-brief="${id}"]`);
            panel?.classList.remove('hidden');
            panel?.classList.add('flex');
        });
    });

    root.querySelectorAll('[data-page-brief-close]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const panel = btn.closest('[data-page-brief]');
            panel?.classList.add('hidden');
            panel?.classList.remove('flex');
        });
    });

    root.querySelectorAll('[data-page-questions-open]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const id = btn.dataset.pageQuestionsOpen;
            const panel = root.querySelector(`[data-page-questions-panel="${id}"]`);
            panel?.classList.remove('hidden');
            panel?.classList.add('flex');
        });
    });

    root.querySelectorAll('[data-page-questions-close]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const panel = btn.closest('[data-page-questions-panel]');
            panel?.classList.add('hidden');
            panel?.classList.remove('flex');
        });
    });
}

function ensureSheetSurfaceReady(surface, root) {
    if (surface.dataset.sheetReady === '1' || surface.dataset.sheetReady === 'pending') {
        return;
    }

    if (surface.dataset.pdfUrl) {
        surface.dataset.sheetReady = 'pending';
        renderPdfStack(surface).catch((err) => {
            console.error('[sheet-workspace]', err);
            surface.dataset.sheetReady = '';
            const loading = surface.querySelector('[data-pdf-loading]');
            if (loading) {
                loading.innerHTML = '<p class="text-sm font-semibold text-red-600 bg-red-50 px-4 py-2 rounded-xl">Impossible de charger le PDF.</p>';
            }
        });
    } else if (surface.dataset.mathScroll === '1') {
        fitMathSurface(surface);
        surface.dataset.sheetReady = '1';
        if (root) {
            notifySheetResize(root);
        }
    }
}

export function getSheetDimensions(pageEl) {
    const surface = pageEl.querySelector('[data-sheet-surface]');
    if (!surface) {
        return null;
    }

    const width = parseInt(surface.dataset.sheetWidth, 10) || surface.offsetWidth || 900;
    const height = parseInt(surface.dataset.sheetHeight, 10)
        || parseInt(surface.dataset.scrollHeight, 10)
        || surface.offsetHeight
        || 1123;

    return { width, height, surface };
}

function notifySheetResize(root) {
    root.dispatchEvent(new CustomEvent('activity-player:resize-sheet'));
}

async function fetchPdfBytes(url) {
    const response = await fetch(url, {
        credentials: 'same-origin',
        headers: { Accept: 'application/pdf,*/*' },
    });

    if (!response.ok) {
        throw new Error(`pdf fetch ${response.status}`);
    }

    return response.arrayBuffer();
}

async function renderPdfStack(surface) {
    const url = surface.dataset.pdfUrl;
    const layers = surface.querySelector('[data-pdf-layers]');
    const loading = surface.querySelector('[data-pdf-loading]');
    const scrollEl = surface.closest('[data-sheet-scroll]');
    const playerBody = surface.closest('.ap-player-body');
    if (!url || !layers) {
        return;
    }

    const bytes = await fetchPdfBytes(url);
    const pdf = await pdfjsLib.getDocument({ data: bytes }).promise;

    const containerWidth = Math.max(
        320,
        scrollEl?.clientWidth || playerBody?.clientWidth || surface.offsetWidth || 900,
    );
    const firstPage = await pdf.getPage(1);
    const baseViewport = firstPage.getViewport({ scale: 1 });
    const scale = containerWidth / baseViewport.width;

    layers.innerHTML = '';
    let totalHeight = 0;
    let maxWidth = 0;
    const gap = 8;

    for (let pageNum = 1; pageNum <= pdf.numPages; pageNum += 1) {
        const page = await pdf.getPage(pageNum);
        const viewport = page.getViewport({ scale });
        const canvas = document.createElement('canvas');
        canvas.width = viewport.width;
        canvas.height = viewport.height;
        canvas.className = 'block max-w-none';
        canvas.style.width = `${viewport.width}px`;
        canvas.style.height = `${viewport.height}px`;
        canvas.style.marginBottom = pageNum < pdf.numPages ? `${gap}px` : '0';

        await page.render({
            canvasContext: canvas.getContext('2d'),
            viewport,
        }).promise;

        layers.appendChild(canvas);
        totalHeight += viewport.height + (pageNum < pdf.numPages ? gap : 0);
        maxWidth = Math.max(maxWidth, viewport.width);
    }

    surface.style.width = `${maxWidth}px`;
    surface.style.minHeight = `${totalHeight}px`;
    surface.dataset.sheetWidth = String(Math.round(maxWidth));
    surface.dataset.sheetHeight = String(Math.round(totalHeight));
    surface.dataset.sheetReady = '1';
    surface.dataset.pdfReady = '1';
    loading?.remove();

    sheetState.set(surface, { width: maxWidth, height: totalHeight });

    const player = surface.closest('#activity-player');
    if (player) {
        notifySheetResize(player);
    }
}

function fitMathSurface(surface) {
    const scrollEl = surface.closest('[data-sheet-scroll]');
    const playerBody = surface.closest('.ap-player-body');
    const scrollHeight = parseInt(surface.dataset.scrollHeight, 10) || 3200;
    const width = Math.max(
        320,
        scrollEl?.clientWidth || playerBody?.clientWidth || parseInt(surface.style.width, 10) || 900,
    );

    surface.style.width = `${width}px`;
    surface.style.minHeight = `${scrollHeight}px`;
    surface.dataset.sheetWidth = String(width);
    surface.dataset.sheetHeight = String(scrollHeight);
    sheetState.set(surface, { width, height: scrollHeight });
}
