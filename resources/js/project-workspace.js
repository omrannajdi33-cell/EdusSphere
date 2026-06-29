function projectWorkspace(config) {
    return {
        tab: config.allowsWrite ? 'brief' : (config.allowsUpload ? 'files' : 'sources'),
        content: config.content || '',
        sources: config.sources || [],
        bibliography: config.bibliography || [],
        files: config.files || [],
        canEdit: config.canEdit,
        saveState: 'idle',
        saveLabel: '',
        submitting: false,

        init() {
            this.saveLabel = this.canEdit ? 'Sauvegarde automatique' : '';
        },

        addSource() {
            this.sources.push({ type: 'website', title: '', author: '', url: '', notes: '', accessed_at: '' });
        },

        addBiblio() {
            this.bibliography.push({ type: 'book', title: '', author: '', year: '', publisher: '', url: '', notes: '' });
        },

        async save() {
            if (!this.canEdit) return;

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
                        sources: this.sources,
                        bibliography: this.bibliography,
                    }),
                });

                if (!res.ok) throw new Error('save failed');

                this.saveState = 'saved';
                this.saveLabel = 'Enregistré ✓';
                setTimeout(() => {
                    if (this.saveState === 'saved') {
                        this.saveLabel = 'Sauvegarde automatique';
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
            if (!file || !this.canEdit) return;

            const form = new FormData();
            form.append('file', file);

            try {
                const res = await fetch(config.uploadUrl, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': config.csrf, Accept: 'application/json' },
                    body: form,
                });
                const data = await res.json();
                if (!res.ok) throw new Error(data.message || 'upload failed');
                this.files.push(data.file);
                event.target.value = '';
            } catch (e) {
                alert(e.message || 'Impossible de téléverser le fichier.');
            }
        },

        async removeFile(id) {
            if (!this.canEdit || !confirm('Supprimer ce fichier ?')) return;

            const url = `${config.deleteFileUrl}/${id}`;

            try {
                const res = await fetch(url, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': config.csrf, Accept: 'application/json' },
                });
                if (!res.ok) throw new Error('delete failed');
                this.files = this.files.filter(f => f.id !== id);
            } catch {
                alert('Impossible de supprimer le fichier.');
            }
        },

        async submitProject() {
            if (!this.canEdit || this.submitting) return;
            if (!confirm('Soumettre ton projet ? Tu ne pourras plus le modifier sans renvoi du professeur.')) return;

            this.submitting = true;
            await this.save();

            try {
                const res = await fetch(config.submitUrl, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': config.csrf, Accept: 'application/json' },
                });
                const data = await res.json();
                if (!res.ok) throw new Error(data.message || 'submit failed');
                window.location.href = data.url;
            } catch (e) {
                alert(e.message || 'Impossible de soumettre.');
                this.submitting = false;
            }
        },
    };
}

window.projectWorkspace = projectWorkspace;

export default projectWorkspace;
