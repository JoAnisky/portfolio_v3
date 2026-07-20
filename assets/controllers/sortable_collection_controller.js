import { Controller } from '@hotwired/stimulus';
import Sortable from 'sortablejs';

export default class extends Controller {
    connect() {
        this.observer = new MutationObserver(() => this.sync());
        this.observer.observe(this.element, { childList: true, subtree: true });
        this.sync();
    }

    disconnect() {
        this.observer?.disconnect();
    }

    sync() {
        const items = this.element.querySelectorAll('.accordion-item');

        items.forEach((item, index) => {
            const input = item.querySelector('.js-position-input');
            if (input) {
                input.value = index;
            }
        });

        // Sortable itself moves items around (live, during a drag) which re-triggers this
        // MutationObserver callback. Never destroy/recreate an existing instance in response
        // to that, or an in-progress drag gets corrupted (item snaps back on drop). Only
        // initialize once per container element (identified via a marker attribute); a brand
        // new container (e.g. the empty-collection placeholder being replaced by the first
        // real item) simply won't have that marker yet.
        if (items.length === 0 || Sortable.active) {
            return;
        }

        // Actual rendered nesting: .accordion > .form-widget-compound > [collection container,
        // one div holding the empty-state badge OR the real entries] > .field-collection-item
        // (xN, siblings) > .accordion-item. Sortable must bind to that innermost flat container —
        // anything higher up has only one child (the whole block) and drags everything at once.
        // Walk up from the item's own `.field-collection-item` wrapper to ITS parent, so this
        // stays correct regardless of how many wrapper divs EasyAdmin/Symfony stack above it.
        const container = items[0].closest('.field-collection-item').parentElement;
        if (!container || container.dataset.sortableInitialized) {
            return;
        }

        container.dataset.sortableInitialized = 'true';
        Sortable.create(container, {
            handle: '.accordion-button',
            animation: 150,
            // native HTML5 drag often fails to start when the handle is a real <button>
            // (Bootstrap's accordion toggle button intercepts the interaction)
            forceFallback: true,
        });
    }
}
