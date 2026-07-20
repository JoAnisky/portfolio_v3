import { Controller } from '@hotwired/stimulus';
import Sortable from 'sortablejs';

export default class extends Controller {
    static values = { url: String, token: String };

    connect() {
        const tbody = this.element.querySelector('tbody');
        if (!tbody) {
            return;
        }

        const rows = [...tbody.querySelectorAll('tr[data-id]')];
        const projectIds = new Set(rows.map((row) => row.dataset.projectId));
        if (projectIds.size !== 1) {
            return;
        }

        this.tbody = tbody;
        this.sortable = Sortable.create(tbody, {
            handle: '.sortable-handle',
            animation: 150,
            // native HTML5 drag can be swallowed by EasyAdmin's own row click-to-edit handler
            forceFallback: true,
            onEnd: () => this.persist(),
        });
    }

    disconnect() {
        this.sortable?.destroy();
    }

    async persist() {
        const ids = [...this.tbody.querySelectorAll('tr[data-id]')].map((row) => row.dataset.id);

        await fetch(this.urlValue, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': this.tokenValue,
            },
            body: JSON.stringify({ ids }),
        });
    }
}
