document.addEventListener('alpine:init', () => {
    Alpine.data('projectWorkspace', (config) => ({
        stepIndex: 0,
        steps: [],
        content: config.content || '',
        researchNotes: config.researchNotes || '',
        bibliography: config.bibliography || [],
        files: config.files || [],
        canEdit: config.canEdit,
        saveState: 'idle',
        saveLabel: '',
        submitting: false,
        guide: config.bibliographyGuide || { styles: {}, documents: {}, fallback: {} },
        bibGuideStyle: config.bibliographyGuide?.default_style || 'dionne',
        bibGuideDoc: 'books',
        bibGuideCase: 'book_whole',
        activeBiblioIndex: 0,

        init() {
            this.steps = this.buildSteps(config);
            this.saveLabel = this.canEdit ? 'Sauvegarde auto' : '';
            this.bibliography.forEach((entry) => this.normalizeBiblioEntry(entry));
            if (this.bibliography.length > 0) {
                this.syncGuideFromEntry(this.bibliography[0]);
            }
        },

        buildSteps(cfg) {
            const steps = [
                { id: 'brief', label: 'Consignes', icon: '📋' },
                { id: 'research', label: 'Recherche', icon: '🔍' },
            ];

            if (cfg.allowsWrite) {
                steps.push({ id: 'write', label: 'Rédaction', icon: '✍️' });
            }

            if (cfg.allowsUpload) {
                steps.push({ id: 'final', label: 'Produit final', icon: '📄' });
            }

            if (cfg.requireBibliography) {
                steps.push({ id: 'biblio', label: 'Bibliographie', icon: '📚' });
            }

            steps.push({ id: 'review', label: 'Révision', icon: '✅' });

            return steps;
        },

        get currentStep() {
            return this.steps[this.stepIndex] || this.steps[0];
        },

        get isFirstStep() {
            return this.stepIndex === 0;
        },

        get isLastStep() {
            return this.stepIndex >= this.steps.length - 1;
        },

        get progressPercent() {
            if (this.steps.length <= 1) return 100;
            return Math.round((this.stepIndex / (this.steps.length - 1)) * 100);
        },

        get bibGuideContent() {
            const doc = this.guide.documents?.[this.bibGuideDoc];
            const cases = doc?.cases ?? {};
            const caseMeta = cases[this.bibGuideCase] ?? Object.values(cases)[0];
            const format = caseMeta?.formats?.[this.bibGuideStyle];

            if (format) {
                return format;
            }

            return this.guide.fallback?.[this.bibGuideStyle] ?? {
                title: 'Aide-mémoire',
                structure: '',
                example: '',
                tips: [],
            };
        },

        isStep(id) {
            return this.currentStep?.id === id;
        },

        goNext() {
            if (this.isLastStep) return;
            this.save();
            this.stepIndex += 1;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },

        goPrev() {
            if (this.isFirstStep) return;
            this.stepIndex -= 1;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },

        goToStep(index) {
            if (index >= 0 && index < this.steps.length) {
                this.stepIndex = index;
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        },

        documentOptions() {
            return Object.entries(this.guide.documents ?? {}).map(([key, meta]) => ({
                key,
                label: meta.label,
            }));
        },

        caseOptions(docKey) {
            const cases = this.guide.documents?.[docKey]?.cases ?? {};

            return Object.entries(cases).map(([key, meta]) => ({
                key,
                label: meta.label,
            }));
        },

        styleOptions() {
            return Object.entries(this.guide.styles ?? {}).map(([key, meta]) => ({
                key,
                label: meta.label,
                description: meta.description,
            }));
        },

        firstCaseForDoc(docKey) {
            const cases = this.caseOptions(docKey);
            return cases[0]?.key ?? '';
        },

        normalizeBiblioEntry(entry) {
            if (! entry.style) {
                entry.style = this.guide.default_style || 'dionne';
            }

            if (! entry.document_type) {
                entry.document_type = this.legacyDocumentType(entry.type);
            }

            if (! entry.document_case || ! this.guide.documents?.[entry.document_type]?.cases?.[entry.document_case]) {
                entry.document_case = this.firstCaseForDoc(entry.document_type);
            }
        },

        legacyDocumentType(type) {
            const map = {
                book: 'books',
                article: 'journals',
                website: 'web',
                video: 'audio_video',
                thesis: 'academic',
            };

            return map[type] ?? 'books';
        },

        setActiveBiblio(index) {
            this.activeBiblioIndex = index;
            const entry = this.bibliography[index];
            if (entry) {
                this.syncGuideFromEntry(entry);
            }
        },

        syncGuideFromEntry(entry) {
            this.bibGuideStyle = entry.style || this.guide.default_style || 'dionne';
            this.bibGuideDoc = entry.document_type || 'books';
            this.bibGuideCase = entry.document_case || this.firstCaseForDoc(this.bibGuideDoc);
        },

        applyGuideToActiveEntry() {
            const entry = this.bibliography[this.activeBiblioIndex];
            if (! entry) return;

            entry.style = this.bibGuideStyle;
            entry.document_type = this.bibGuideDoc;
            entry.document_case = this.bibGuideCase;
            this.save();
        },

        onGuideChange() {
            if (! this.guide.documents?.[this.bibGuideDoc]?.cases?.[this.bibGuideCase]) {
                this.bibGuideCase = this.firstCaseForDoc(this.bibGuideDoc);
            }

            this.applyGuideToActiveEntry();
        },

        onEntryDocChange(entry) {
            entry.document_case = this.firstCaseForDoc(entry.document_type);
            this.syncGuideFromEntry(entry);
            this.save();
        },

        onEntryCaseChange(entry) {
            this.syncGuideFromEntry(entry);
            this.save();
        },

        onEntryStyleChange(entry) {
            this.syncGuideFromEntry(entry);
            this.save();
        },

        addBiblio() {
            const entry = {
                style: this.bibGuideStyle,
                document_type: this.bibGuideDoc,
                document_case: this.bibGuideCase,
                title: '',
                author: '',
                year: '',
                publisher: '',
                url: '',
                notes: '',
                citation: '',
            };

            this.bibliography.push(entry);
            this.activeBiblioIndex = this.bibliography.length - 1;
            this.save();
        },

        filledBibliography() {
            return this.bibliography.filter(b => (b.citation || '').trim() !== '');
        },

        hasWorkContent() {
            return (this.content || '').trim().length > 0;
        },

        async save() {
            if (! this.canEdit) return;

            this.saveState = 'saving';
            this.saveLabel = 'Enregistrement…';

            try {
                const res = await fetch(config.saveUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': config.csrf,
                        Accept: 'application/json',
                    },
                    body: JSON.stringify({
                        content: this.content,
                        research_notes: this.researchNotes,
                        bibliography: this.bibliography,
                    }),
                });

                if (! res.ok) throw new Error('save failed');

                this.saveState = 'saved';
                this.saveLabel = 'Enregistré ✓';
                setTimeout(() => {
                    if (this.saveState === 'saved') {
                        this.saveLabel = 'Sauvegarde auto';
                        this.saveState = 'idle';
                    }
                }, 2000);
            } catch {
                this.saveState = 'error';
                this.saveLabel = 'Erreur — réessaye';
            }
        },

        async uploadFile(event) {
            const file = event.target.files?.[0];
            if (! file || ! this.canEdit) return;

            const form = new FormData();
            form.append('file', file);

            try {
                const res = await fetch(config.uploadUrl, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': config.csrf, Accept: 'application/json' },
                    body: form,
                });
                const data = await res.json();
                if (! res.ok) throw new Error(data.message || 'upload failed');
                this.files.push(data.file);
                event.target.value = '';
                await this.save();
            } catch (e) {
                alert(e.message || 'Impossible de déposer le fichier.');
            }
        },

        async removeFile(id) {
            if (! this.canEdit || ! confirm('Supprimer ce fichier ?')) return;

            const url = `${config.deleteFileUrl}/${id}`;

            try {
                const res = await fetch(url, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': config.csrf, Accept: 'application/json' },
                });
                if (! res.ok) throw new Error('delete failed');
                this.files = this.files.filter(f => f.id !== id);
            } catch {
                alert('Impossible de supprimer le fichier.');
            }
        },

        async submitProject() {
            if (! this.canEdit || this.submitting) return;
            if (! confirm('Soumettre ton projet ? Tu ne pourras plus le modifier sans renvoi du professeur.')) return;

            this.submitting = true;
            await this.save();

            try {
                const res = await fetch(config.submitUrl, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': config.csrf, Accept: 'application/json' },
                });
                const data = await res.json();
                if (! res.ok) throw new Error(data.message || 'submit failed');
                window.location.href = data.url;
            } catch (e) {
                alert(e.message || 'Impossible de soumettre.');
                this.submitting = false;
            }
        },
    }));
});
