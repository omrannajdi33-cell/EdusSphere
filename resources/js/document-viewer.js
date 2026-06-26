/**
 * EduSphere — lecteur document intégré (PDF multi-pages + annotations)
 */
import * as pdfjsLib from 'pdfjs-dist';
import pdfWorker from 'pdfjs-dist/build/pdf.worker.min.mjs?url';
import { csrfFetch } from './csrf-fetch';

pdfjsLib.GlobalWorkerOptions.workerSrc = pdfWorker;

const viewerState = new WeakMap();

export function initDocumentViewers(root = document) {
    root.querySelectorAll('[data-document-viewer]').forEach(initDocumentViewer);
}

function initDocumentViewer(el) {
    if (el.dataset.initialized === '1') {
        return;
    }
    el.dataset.initialized = '1';

    const pdfUrl = el.dataset.pdfUrl;
    const saveUrl = el.dataset.saveUrl || '';
    const readOnly = el.dataset.readonly === '1';
    const initial = parseJson(el.dataset.initialAnnotations, {});

    const pagesWrap = el.querySelector('[data-doc-pages]');
    const prevBtn = el.querySelector('[data-doc-prev]');
    const nextBtn = el.querySelector('[data-doc-next]');
    const pageLabel = el.querySelector('[data-doc-page-label]');
    const saveStatus = el.querySelector('[data-doc-save-status]');
    const clearBtn = el.querySelector('[data-doc-clear]');

    const state = {
        pdf: null,
        pageCount: 0,
        currentPage: 1,
        pages: { ...initial },
        readOnly,
        saveUrl,
        saveTimer: null,
        isDirty: false,
    };

    viewerState.set(el, state);

    loadPdf(pdfUrl, pagesWrap, state).then(() => {
        renderPage(el, state, pagesWrap, pageLabel);
    }).catch(() => {
        if (pagesWrap) {
            pagesWrap.innerHTML = '<p class="text-red-600 p-4">Impossible de charger le document.</p>';
        }
    });

    prevBtn?.addEventListener('click', () => {
        if (state.currentPage > 1) {
            state.currentPage -= 1;
            renderPage(el, state, pagesWrap, pageLabel);
        }
    });

    nextBtn?.addEventListener('click', () => {
        if (state.currentPage < state.pageCount) {
            state.currentPage += 1;
            renderPage(el, state, pagesWrap, pageLabel);
        }
    });

    clearBtn?.addEventListener('click', () => {
        if (readOnly) {
            return;
        }
        const canvas = pagesWrap?.querySelector('.doc-annotation-canvas');
        const pageKey = String(state.currentPage);
        state.pages[pageKey] = { strokes: [] };
        redrawCanvas(canvas, []);
        markDirty(el, state, saveStatus);
    });
}

async function loadPdf(url, wrap, state) {
    const loading = pdfjsLib.getDocument(url);
    state.pdf = await loading.promise;
    state.pageCount = state.pdf.numPages;
}

async function renderPage(el, state, wrap, pageLabel) {
    if (!state.pdf || !wrap) {
        return;
    }

    wrap.innerHTML = '';
    const pageKey = String(state.currentPage);
    const pageData = state.pages[pageKey] ?? { strokes: [] };
    state.pages[pageKey] = pageData;

    const page = await state.pdf.getPage(state.currentPage);
    const baseScale = 1.35;
    const viewport = page.getViewport({ scale: baseScale });

    const stage = document.createElement('div');
    stage.className = 'doc-page-stage relative mx-auto bg-white shadow-es rounded-xl overflow-hidden';
    stage.style.width = `${viewport.width}px`;
    stage.style.height = `${viewport.height}px`;

    const pdfCanvas = document.createElement('canvas');
    pdfCanvas.width = viewport.width;
    pdfCanvas.height = viewport.height;
    pdfCanvas.className = 'absolute inset-0 w-full h-full';
    pdfCanvas.setAttribute('aria-hidden', 'true');

    const drawCanvas = document.createElement('canvas');
    drawCanvas.width = viewport.width;
    drawCanvas.height = viewport.height;
    drawCanvas.className = 'doc-annotation-canvas absolute inset-0 w-full h-full touch-none z-10';
    if (state.readOnly) {
        drawCanvas.classList.add('pointer-events-none');
    }

    stage.appendChild(pdfCanvas);
    stage.appendChild(drawCanvas);
    wrap.appendChild(stage);

    const ctx = pdfCanvas.getContext('2d');
    await page.render({ canvasContext: ctx, viewport }).promise;

    redrawCanvas(drawCanvas, pageData.strokes ?? []);
    bindDrawing(drawCanvas, pageKey, state, el);

    if (pageLabel) {
        pageLabel.textContent = `Page ${state.currentPage} / ${state.pageCount}`;
    }
}

function bindDrawing(canvas, pageKey, state, root) {
    if (state.readOnly) {
        return;
    }

    const ctx = canvas.getContext('2d');
    let drawing = false;
    let stroke = null;

    const pos = (e) => {
        const rect = canvas.getBoundingClientRect();
        const scaleX = canvas.width / rect.width;
        const scaleY = canvas.height / rect.height;

        return {
            x: (e.clientX - rect.left) * scaleX,
            y: (e.clientY - rect.top) * scaleY,
        };
    };

    canvas.addEventListener('pointerdown', (e) => {
        drawing = true;
        stroke = { tool: 'pen', color: '#4f46e5', width: 3, points: [pos(e)] };
        canvas.setPointerCapture(e.pointerId);
    });

    canvas.addEventListener('pointermove', (e) => {
        if (!drawing || !stroke) {
            return;
        }
        stroke.points.push(pos(e));
        const strokes = [...(state.pages[pageKey]?.strokes ?? []), stroke];
        redrawCanvas(canvas, strokes.slice(0, -1));
        drawStroke(ctx, stroke);
    });

    const end = (e) => {
        if (!drawing || !stroke) {
            return;
        }
        drawing = false;
        if (!state.pages[pageKey]) {
            state.pages[pageKey] = { strokes: [] };
        }
        state.pages[pageKey].strokes.push(stroke);
        stroke = null;
        markDirty(root, state, root.querySelector('[data-doc-save-status]'));
    };

    canvas.addEventListener('pointerup', end);
    canvas.addEventListener('pointerleave', end);
}

function markDirty(root, state, saveStatus) {
    state.isDirty = true;
    if (saveStatus) {
        saveStatus.textContent = 'Modifications…';
    }
    clearTimeout(state.saveTimer);
    state.saveTimer = setTimeout(() => saveAnnotations(root, state, saveStatus), 1200);
}

async function saveAnnotations(root, state, saveStatus) {
    if (!state.saveUrl || !state.isDirty) {
        return;
    }

    if (saveStatus) {
        saveStatus.textContent = 'Sauvegarde…';
    }

    try {
        const body = {
            pages: state.pages,
        };
        if (root.dataset.mediaId) {
            body.media_file_id = parseInt(root.dataset.mediaId, 10);
        }

        const res = await csrfFetch(state.saveUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(body),
        });
        if (!res.ok) {
            throw new Error('save failed');
        }
        state.isDirty = false;
        if (saveStatus) {
            saveStatus.textContent = 'Sauvegardé ✓';
        }
    } catch {
        if (saveStatus) {
            saveStatus.textContent = 'Erreur de sauvegarde';
        }
    }
}

function redrawCanvas(canvas, strokes) {
    if (!canvas) {
        return;
    }
    const ctx = canvas.getContext('2d');
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    (strokes ?? []).forEach((s) => drawStroke(ctx, s));
}

function drawStroke(ctx, stroke) {
    const pts = stroke.points ?? [];
    if (pts.length < 2) {
        return;
    }
    ctx.strokeStyle = stroke.color ?? '#4f46e5';
    ctx.lineWidth = stroke.width ?? 3;
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';
    ctx.beginPath();
    ctx.moveTo(pts[0].x, pts[0].y);
    for (let i = 1; i < pts.length; i += 1) {
        ctx.lineTo(pts[i].x, pts[i].y);
    }
    ctx.stroke();
}

function parseJson(raw, fallback) {
    try {
        return raw ? JSON.parse(raw) : fallback;
    } catch {
        return fallback;
    }
}

document.addEventListener('DOMContentLoaded', () => initDocumentViewers());
window.initDocumentViewers = initDocumentViewers;
