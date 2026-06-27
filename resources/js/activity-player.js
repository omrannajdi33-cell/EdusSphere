import { initSubjectWorkspaces, collectWorkspaceData } from './subject-workspaces';
import { csrfFetch, csrfToken, readErrorMessage } from './csrf-fetch';
function initActivityPlayer(root) {
    if (!root || root.dataset.initialized === '1') {
        return;
    }
    root.dataset.initialized = '1';

    const pages = [...root.querySelectorAll('[data-page]')];
    if (pages.length === 0) {
        return;
    }

    const saveUrl = root.dataset.saveUrl || '';
    const submitUrl = root.dataset.submitUrl || '';
    const correctionUrl = root.dataset.correctionUrl || '';
    const isPreview = root.dataset.preview === '1';
    const isCorrection = root.dataset.correction === '1';
    const isReadonly = root.dataset.readonly === '1';
    const totalPages = parseInt(root.dataset.totalPages, 10) || pages.length;
    let currentIndex = Math.max(0, (parseInt(root.dataset.initialPage, 10) || 1) - 1);
    currentIndex = Math.min(currentIndex, pages.length - 1);

    const prevBtn = root.querySelector('#player-prev');
    const nextBtn = root.querySelector('#player-next');
    const submitBtn = root.querySelector('#player-submit');
    const pageIndicator = root.querySelector('#player-page-indicator');
    const saveStatus = root.querySelector('#player-save-status');
    const saveRetryBtn = root.querySelector('#player-save-retry');
    const toolbar = root.querySelector('#player-toolbar');
    const toolButtons = [...root.querySelectorAll('.player-tool')];
    const clearBtn = root.querySelector('#player-clear-canvas');

    let activeTool = isCorrection ? 'pen' : 'pen';
    let isDrawing = false;
    let currentStroke = null;
    const pageState = new Map();

    pages.forEach((pageEl) => {
        const studentCanvas = pageEl.querySelector('.player-canvas-student');
        const teacherCanvas = pageEl.querySelector('.player-canvas-teacher');
        const notes = pageEl.querySelector('.player-notes');
        const needsCanvas = pageEl.dataset.needsCanvas === '1';

        if (!needsCanvas) {
            pageState.set(pageEl, { needsCanvas: false });
            return;
        }

        const activeCanvas = isCorrection ? teacherCanvas : studentCanvas;
        const ctx = activeCanvas?.getContext('2d');
        const studentCtx = studentCanvas?.getContext('2d');
        const teacherCtx = teacherCanvas?.getContext('2d');
        let strokes = [];
        let teacherStrokes = [];

        try {
            const initial = studentCanvas?.dataset.initial ? JSON.parse(studentCanvas.dataset.initial) : [];
            strokes = Array.isArray(initial) ? initial : [];
        } catch {
            strokes = [];
        }

        try {
            const tInitial = teacherCanvas?.dataset.initial ? JSON.parse(teacherCanvas.dataset.initial) : [];
            teacherStrokes = Array.isArray(tInitial) ? tInitial : [];
        } catch {
            teacherStrokes = [];
        }

        pageState.set(pageEl, {
            needsCanvas: true,
            studentCanvas,
            teacherCanvas,
            notes,
            ctx,
            studentCtx,
            teacherCtx,
            strokes,
            teacherStrokes,
            studentReadonly: isCorrection || isReadonly || studentCanvas?.dataset.readonly === '1',
        });

        resizeCanvas(pageEl);
        redraw(pageEl);

        const drawTarget = isCorrection ? teacherCanvas : studentCanvas;
        if (drawTarget && !isReadonly && !isPreview) {
            drawTarget.addEventListener('pointerdown', (e) => onPointerDown(e, pageEl));
            drawTarget.addEventListener('pointermove', (e) => onPointerMove(e, pageEl));
            drawTarget.addEventListener('pointerup', () => onPointerUp(pageEl));
            drawTarget.addEventListener('pointerleave', () => onPointerUp(pageEl));
        } else if (drawTarget && isCorrection) {
            drawTarget.addEventListener('pointerdown', (e) => onPointerDown(e, pageEl));
            drawTarget.addEventListener('pointermove', (e) => onPointerMove(e, pageEl));
            drawTarget.addEventListener('pointerup', () => onPointerUp(pageEl));
            drawTarget.addEventListener('pointerleave', () => onPointerUp(pageEl));
        }
    });

    window.addEventListener('resize', () => pages.forEach((p) => {
        if (pageState.get(p)?.needsCanvas) {
            resizeCanvas(p);
        }
    }));

    toolButtons.forEach((btn) => {
        btn.addEventListener('click', () => setTool(btn.dataset.tool));
    });

    clearBtn?.addEventListener('click', () => {
        const pageEl = pages[currentIndex];
        const state = pageState.get(pageEl);
        if (!state?.needsCanvas) {
            return;
        }
        if (isCorrection) {
            state.teacherStrokes = [];
        } else {
            state.strokes = [];
            if (state.notes) {
                state.notes.value = '';
            }
        }
        redraw(pageEl);
        markDirty();
    });

    prevBtn?.addEventListener('click', () => goToPage(currentIndex - 1));
    nextBtn?.addEventListener('click', () => {
        if (currentIndex >= pages.length - 1 && submitBtn && !submitBtn.classList.contains('hidden')) {
            submitActivity();
            return;
        }
        goToPage(currentIndex + 1);
    });

    submitBtn?.addEventListener('click', submitActivity);

    saveRetryBtn?.addEventListener('click', () => saveNow(true));

    root.querySelectorAll('[data-question-id] input, [data-question-id] textarea, [data-question-id] select').forEach((el) => {
        if (!el.disabled) {
            el.addEventListener('change', () => markDirty());
            el.addEventListener('input', () => markDirty());
        }
    });

    const AUTO_SAVE_MS = 20000;
    let saveTimer = null;
    let autoSaveTimer = null;
    let isDirty = false;
    let isSaving = false;
    let lastSavedAt = null;
    let lastError = false;

    if (!isPreview && !isCorrection && (saveUrl || correctionUrl)) {
        autoSaveTimer = setInterval(() => {
            if (isDirty && !isSaving) {
                saveNow();
            }
        }, AUTO_SAVE_MS);

        window.addEventListener('beforeunload', () => {
            if (!isDirty || isPreview) {
                return;
            }
            const token = csrfToken();
            const pageEl = pages[currentIndex];
            const body = buildSaveBody(pageEl);
            if (body && saveUrl && token) {
                fetch(saveUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': token,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify(body),
                    credentials: 'same-origin',
                    keepalive: true,
                });
            }
        });
    }

    root.addEventListener('activity-player:dirty', () => {
        isDirty = true;
        scheduleSave();
    });

    initSubjectWorkspaces(root);

    showPage(currentIndex);

    function setTool(tool) {
        activeTool = tool;
        toolButtons.forEach((btn) => {
            btn.setAttribute('aria-pressed', btn.dataset.tool === tool ? 'true' : 'false');
        });

        const state = pageState.get(pages[currentIndex]);
        if (!state?.needsCanvas || !state.notes) {
            return;
        }

        if (tool === 'text' && !isCorrection) {
            state.notes.classList.remove('hidden');
            state.studentCanvas?.classList.add('pointer-events-none');
            state.notes.focus();
        } else {
            state.notes.classList.add('hidden');
            if (!isCorrection) {
                state.studentCanvas?.classList.remove('pointer-events-none');
            }
        }
    }

    function resizeCanvas(pageEl) {
        const state = pageState.get(pageEl);
        if (!state?.needsCanvas) {
            return;
        }

        [state.studentCanvas, state.teacherCanvas].filter(Boolean).forEach((canvas) => {
            const parent = canvas.parentElement;
            const rect = parent?.getBoundingClientRect() ?? { width: 800, height: 420 };
            const isMath = pageEl.dataset.pageType === 'math_scroll';
            const scrollH = parseInt(pageEl.dataset.scrollHeight, 10) || 3200;
            const ratio = window.devicePixelRatio || 1;
            canvas.width = Math.max(1, rect.width * ratio);
            canvas.height = Math.max(isMath ? scrollH : 420, rect.height) * ratio;
            canvas.style.width = `${rect.width}px`;
            canvas.style.height = `${Math.max(420, rect.height)}px`;
        });

        state.studentCtx?.setTransform(window.devicePixelRatio || 1, 0, 0, window.devicePixelRatio || 1, 0, 0);
        state.teacherCtx?.setTransform(window.devicePixelRatio || 1, 0, 0, window.devicePixelRatio || 1, 0, 0);
        redraw(pageEl);
    }

    function drawStrokes(context, canvas, strokeList, readOnlyStyle = false) {
        if (!context || !canvas) {
            return;
        }
        const ratio = window.devicePixelRatio || 1;
        const w = canvas.width / ratio;
        const h = canvas.height / ratio;
        context.clearRect(0, 0, w, h);

        strokeList.forEach((stroke) => {
            if (!stroke.points?.length) {
                return;
            }
            context.beginPath();
            context.lineCap = 'round';
            context.lineJoin = 'round';
            context.lineWidth = stroke.width;
            context.globalCompositeOperation = stroke.tool === 'erase' ? 'destination-out' : 'source-over';
            context.strokeStyle = readOnlyStyle ? 'rgba(15, 23, 42, 0.35)' : stroke.color;
            stroke.points.forEach((pt, i) => {
                if (i === 0) {
                    context.moveTo(pt.x, pt.y);
                } else {
                    context.lineTo(pt.x, pt.y);
                }
            });
            context.stroke();
        });
        context.globalCompositeOperation = 'source-over';
    }

    function redraw(pageEl) {
        const state = pageState.get(pageEl);
        if (!state?.needsCanvas) {
            return;
        }
        drawStrokes(state.studentCtx, state.studentCanvas, state.strokes, isCorrection);
        drawStrokes(state.teacherCtx, state.teacherCanvas, state.teacherStrokes, false);
    }

    function getPoint(e, canvas) {
        const rect = canvas.getBoundingClientRect();
        return { x: e.clientX - rect.left, y: e.clientY - rect.top };
    }

    function onPointerDown(e, pageEl) {
        if (pageEl !== pages[currentIndex] || (activeTool === 'text' && !isCorrection)) {
            return;
        }
        const state = pageState.get(pageEl);
        const canvas = isCorrection ? state.teacherCanvas : state.studentCanvas;
        if (!canvas) {
            return;
        }

        isDrawing = true;
        canvas.setPointerCapture?.(e.pointerId);
        const pt = getPoint(e, canvas);

        const tool = isCorrection ? 'pen' : (activeTool === 'highlight' ? 'highlight' : activeTool === 'erase' ? 'erase' : 'pen');
        currentStroke = {
            tool,
            color: isCorrection ? '#dc2626' : (activeTool === 'highlight' ? 'rgba(250, 204, 21, 0.55)' : '#0f172a'),
            width: isCorrection ? 3 : (activeTool === 'highlight' ? 18 : activeTool === 'erase' ? 24 : 3),
            points: [pt],
        };

        if (isCorrection) {
            state.teacherStrokes.push(currentStroke);
        } else {
            state.strokes.push(currentStroke);
        }
    }

    function onPointerMove(e, pageEl) {
        if (!isDrawing || pageEl !== pages[currentIndex] || !currentStroke) {
            return;
        }
        const state = pageState.get(pageEl);
        const canvas = isCorrection ? state.teacherCanvas : state.studentCanvas;
        currentStroke.points.push(getPoint(e, canvas));
        redraw(pageEl);
    }

    function onPointerUp() {
        if (!isDrawing) {
            return;
        }
        isDrawing = false;
        currentStroke = null;
        markDirty();
    }

    function showPage(index) {
        currentIndex = Math.max(0, Math.min(index, pages.length - 1));
        pages.forEach((pageEl, i) => {
            const hidden = i !== currentIndex;
            pageEl.classList.toggle('hidden', hidden);
            pageEl.toggleAttribute('hidden', hidden);
        });

        const pageEl = pages[currentIndex];
        const state = pageState.get(pageEl);
        const richPanel = pageEl.querySelector('[data-rich-panel]');
        const richDrawMode = richPanel && richPanel.dataset.richMode === 'draw';
        const needsToolbar = state?.needsCanvas && (!isReadonly || isCorrection) && (!richPanel || richDrawMode);

        if (toolbar) {
            toolbar.classList.toggle('hidden', !needsToolbar);
        }

        if (pageIndicator) {
            pageIndicator.textContent = `Page ${currentIndex + 1} / ${totalPages}`;
        }
        if (prevBtn) {
            prevBtn.disabled = currentIndex === 0;
        }

        const isLast = currentIndex >= pages.length - 1;
        if (nextBtn) {
            nextBtn.textContent = isLast ? 'Terminer' : 'Suivant';
            nextBtn.classList.toggle('hidden', isLast && submitBtn);
        }
        if (submitBtn) {
            submitBtn.classList.toggle('hidden', !isLast);
        }

        setTool(activeTool);
    }

    function goToPage(index) {
        if (index < 0 || index >= pages.length) {
            return;
        }
        saveNow().finally(() => showPage(index));
    }

    function collectResponses(pageEl) {
        const responses = {};
        pageEl.querySelectorAll('[data-question-id]').forEach((fieldset) => {
            const qid = fieldset.dataset.questionId;
            const qtype = fieldset.dataset.questionType;

            if (qtype === 'multi_select') {
                responses[qid] = [...fieldset.querySelectorAll(`input[name="responses[${qid}][]"]:checked`)].map((el) => el.value);
            } else if (qtype === 'ordering') {
                const vals = {};
                fieldset.querySelectorAll(`input[name^="responses[${qid}]"]`).forEach((el) => {
                    const m = el.name.match(/\[(\d+)\]$/);
                    if (m) {
                        vals[m[1]] = el.value;
                    }
                });
                responses[qid] = vals;
            } else if (qtype === 'matching') {
                const vals = {};
                fieldset.querySelectorAll(`select[name^="responses[${qid}]"]`).forEach((el) => {
                    const m = el.name.match(/\[(\d+)\]$/);
                    if (m) {
                        vals[m[1]] = el.value;
                    }
                });
                responses[qid] = vals;
            } else if (qtype === 'fill_blank') {
                const vals = {};
                fieldset.querySelectorAll(`input[name^="responses[${qid}]"]`).forEach((el) => {
                    const m = el.name.match(/\[(\d+)\]$/);
                    if (m) {
                        vals[m[1]] = el.value;
                    }
                });
                responses[qid] = vals;
            } else {
                const radio = fieldset.querySelector(`input[name="responses[${qid}]"]:checked`);
                const text = fieldset.querySelector(`input[name="responses[${qid}]"], textarea[name="responses[${qid}]"]`);
                if (radio) {
                    responses[qid] = radio.value;
                } else if (text && text.type !== 'radio') {
                    responses[qid] = text.value;
                }
            }
        });
        return responses;
    }

    function collectCanvas(pageEl) {
        const state = pageState.get(pageEl);
        if (!state?.needsCanvas) {
            return null;
        }
        return {
            strokes: state.strokes ?? [],
            notes: state.notes?.value ?? '',
        };
    }

    function collectTeacherStrokes(pageEl) {
        const state = pageState.get(pageEl);
        return state?.teacherStrokes ?? [];
    }

    function markDirty() {
        isDirty = true;
        scheduleSave();
    }

    function formatSavedTime(date) {
        return date.toLocaleTimeString('fr-CA', { hour: '2-digit', minute: '2-digit' });
    }

    function updateSaveStatus(state, message) {
        if (!saveStatus) {
            return;
        }
        saveStatus.textContent = message;
        saveStatus.dataset.state = state;
        if (saveRetryBtn) {
            saveRetryBtn.classList.toggle('hidden', state !== 'error');
        }
    }

    function buildSaveBody(pageEl) {
        if (isCorrection) {
            return {
                page_id: parseInt(pageEl.dataset.pageId, 10),
                teacher_strokes: collectTeacherStrokes(pageEl),
            };
        }

        const body = {
            page_id: parseInt(pageEl.dataset.pageId, 10),
            page_order: parseInt(pageEl.dataset.pageOrder, 10),
            total_pages: totalPages,
            responses: collectResponses(pageEl),
        };
        const canvas = collectCanvas(pageEl);
        if (canvas) {
            body.canvas = canvas;
        }
        const workspace = collectWorkspaceData(pageEl);
        if (workspace) {
            body.workspace = workspace;
        }
        return body;
    }

    function scheduleSave() {
        if (isPreview) {
            return;
        }
        clearTimeout(saveTimer);
        saveTimer = setTimeout(() => saveNow(), 1000);
        if (!isSaving && !lastError) {
            updateSaveStatus('pending', 'Modifications…');
        }
    }

    async function saveNow(force = false) {
        if (isPreview || isSaving) {
            return;
        }
        if (!force && !isDirty) {
            return;
        }

        const pageEl = pages[currentIndex];
        const url = isCorrection && correctionUrl ? correctionUrl : saveUrl;

        if (!url) {
            return;
        }

        isSaving = true;
        updateSaveStatus('saving', 'Sauvegarde en cours…');

        try {
            const res = await csrfFetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(buildSaveBody(pageEl)),
            });
            if (res.status === 419) {
                throw new Error('session expired');
            }
            if (res.status === 423) {
                throw new Error('locked');
            }
            if (!res.ok) {
                throw new Error(await readErrorMessage(res, 'save failed'));
            }
            isDirty = false;
            lastError = false;
            lastSavedAt = new Date();
            updateSaveStatus('saved', `Sauvegardé · ${formatSavedTime(lastSavedAt)}`);
        } catch (err) {
            lastError = true;
            updateSaveStatus(
                'error',
                err?.message === 'session expired'
                    ? 'Session expirée — recharge la page'
                    : err?.message === 'locked'
                        ? 'Activité déjà soumise'
                        : err?.message && err.message !== 'save failed'
                            ? err.message
                            : 'Erreur de synchronisation',
            );
        } finally {
            isSaving = false;
        }
    }

    async function submitActivity() {
        if (!submitUrl || isPreview || isCorrection) {
            return;
        }
        const message = root.dataset.returned === '1'
            ? 'Resoumettre ton activité ? Le professeur recevra ta nouvelle copie.'
            : 'Es-tu sûr de vouloir soumettre ? Tu ne pourras plus modifier ta copie.';
        if (!window.confirm(message)) {
            return;
        }
        await saveNow(true);
        try {
            const res = await csrfFetch(submitUrl, { method: 'POST' });
            if (res.status === 419) {
                throw new Error('session expired');
            }
            if (res.status === 423) {
                throw new Error('locked');
            }
            if (!res.ok) {
                throw new Error(await readErrorMessage(res, 'submit failed'));
            }
            if (saveStatus) {
                saveStatus.textContent = 'Activité soumise ✓';
            }
            isDirty = false;
            clearInterval(autoSaveTimer);
            window.location.href = root.dataset.homeUrl || '/student';
        } catch (err) {
            updateSaveStatus(
                'error',
                err?.message === 'session expired'
                    ? 'Session expirée — recharge la page'
                    : err?.message === 'locked'
                        ? 'Activité déjà soumise'
                        : err?.message && err.message !== 'submit failed'
                            ? err.message
                            : 'Erreur de soumission',
            );
        }
    }

    root.addEventListener('activity-player:destroy', () => {
        clearInterval(autoSaveTimer);
        clearTimeout(saveTimer);
    });
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('#activity-player').forEach(initActivityPlayer);
});
