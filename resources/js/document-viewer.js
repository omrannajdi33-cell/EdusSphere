/**
 * EduSphere — lecteur document intégré (PDF + PPTX + annotations)
 */
import * as pdfjsLib from 'pdfjs-dist';
import pdfWorker from 'pdfjs-dist/build/pdf.worker.min.mjs?url';
import { loadPresentation, renderSlideToElement } from 'pptx-viewer';
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

    const fileUrl = el.dataset.fileUrl || el.dataset.pdfUrl;
    const docKind = (el.dataset.docKind || 'pdf').toLowerCase();
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
        kind: docKind,
        fileUrl,
        pdf: null,
        presentation: null,
        pageCount: 0,
        currentPage: 1,
        pages: { ...initial },
        readOnly,
        saveUrl,
        saveTimer: null,
        isDirty: false,
    };

    viewerState.set(el, state);

    loadDocument(fileUrl, docKind, pagesWrap, state).then(() => {
        renderPage(el, state, pagesWrap, pageLabel);
    }).catch(() => {
        showLoadError(pagesWrap, docKind);
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

async function loadDocument(url, kind, wrap, state) {
    if (kind === 'ppt') {
        throw new Error('legacy ppt');
    }

    if (kind === 'pptx') {
        state.presentation = await loadPresentation(url);
        state.pageCount = state.presentation.slides.length;
        return;
    }

    const loading = pdfjsLib.getDocument(url);
    state.pdf = await loading.promise;
    state.pageCount = state.pdf.numPages;
}

function showLoadError(wrap, kind) {
    if (!wrap) {
        return;
    }

    if (kind === 'ppt') {
        wrap.innerHTML = '<p class="text-amber-700 bg-amber-50 rounded-xl p-4 text-sm font-semibold">Ce fichier est au format PowerPoint ancien (.ppt). Enregistrez-le en <strong>.pptx</strong> ou en PDF pour l’afficher dans le lecteur EduSphere.</p>';
        return;
    }

    wrap.innerHTML = '<p class="text-red-600 p-4">Impossible de charger le document.</p>';
}

async function renderPage(el, state, wrap, pageLabel) {
    if (!wrap || state.pageCount < 1) {
        return;
    }

    if (state.kind === 'pptx') {
        await renderPptxPage(el, state, wrap, pageLabel);
        return;
    }

    await renderPdfPage(el, state, wrap, pageLabel);
}

async function renderPdfPage(el, state, wrap, pageLabel) {
    if (!state.pdf) {
        return;
    }

    wrap.innerHTML = '';
    const pageKey = String(state.currentPage);
    const pageData = state.pages[pageKey] ?? { strokes: [] };
    state.pages[pageKey] = pageData;

    const page = await state.pdf.getPage(state.currentPage);
    const baseScale = 1.35;
    const viewport = page.getViewport({ scale: baseScale });

    const stage = createStage(viewport.width, viewport.height);

    const pdfCanvas = document.createElement('canvas');
    pdfCanvas.width = viewport.width;
    pdfCanvas.height = viewport.height;
    pdfCanvas.className = 'absolute inset-0 w-full h-full';
    pdfCanvas.setAttribute('aria-hidden', 'true');

    const drawCanvas = createDrawCanvas(viewport.width, viewport.height, state.readOnly);

    stage.appendChild(pdfCanvas);
    stage.appendChild(drawCanvas);
    wrap.appendChild(stage);

    const ctx = pdfCanvas.getContext('2d');
    await page.render({ canvasContext: ctx, viewport }).promise;

    redrawCanvas(drawCanvas, pageData.strokes ?? []);
    bindDrawing(drawCanvas, pageKey, state, el);

    updatePageLabel(pageLabel, state, 'Page');
}

async function renderPptxPage(el, state, wrap, pageLabel) {
    if (!state.presentation) {
        return;
    }

    wrap.innerHTML = '';
    const pageKey = String(state.currentPage);
    const pageData = state.pages[pageKey] ?? { strokes: [] };
    state.pages[pageKey] = pageData;
    const slideIndex = state.currentPage - 1;

    const stage = document.createElement('div');
    stage.className = 'doc-page-stage relative mx-auto bg-white shadow-es rounded-xl overflow-hidden';

    const slideHost = document.createElement('div');
    slideHost.className = 'doc-pptx-slide-host relative';
    stage.appendChild(slideHost);
    wrap.appendChild(stage);

    renderSlideToElement(state.presentation, slideIndex, slideHost);

    const slideBox = slideHost.querySelector('svg, canvas, img') ?? slideHost.firstElementChild ?? slideHost;
    const width = Math.max(slideBox?.clientWidth || slideHost.clientWidth || 960, 320);
    const height = Math.max(slideBox?.clientHeight || slideHost.clientHeight || 540, 240);

    stage.style.width = `${width}px`;
    stage.style.minHeight = `${height}px`;

    const drawCanvas = createDrawCanvas(width, height, state.readOnly);
    stage.appendChild(drawCanvas);

    redrawCanvas(drawCanvas, pageData.strokes ?? []);
    bindDrawing(drawCanvas, pageKey, state, el);

    updatePageLabel(pageLabel, state, 'Diapositive');
}

function createStage(width, height) {
    const stage = document.createElement('div');
    stage.className = 'doc-page-stage relative mx-auto bg-white shadow-es rounded-xl overflow-hidden';
    stage.style.width = `${width}px`;
    stage.style.height = `${height}px`;
    return stage;
}

function createDrawCanvas(width, height, readOnly) {
    const drawCanvas = document.createElement('canvas');
    drawCanvas.width = width;
    drawCanvas.height = height;
    drawCanvas.className = 'doc-annotation-canvas absolute inset-0 w-full h-full touch-none z-10';
    if (readOnly) {
        drawCanvas.classList.add('pointer-events-none');
    }
    return drawCanvas;
}

function updatePageLabel(pageLabel, state, prefix) {
    if (pageLabel) {
        pageLabel.textContent = `${prefix} ${state.currentPage} / ${state.pageCount}`;
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

    const end = () => {
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
