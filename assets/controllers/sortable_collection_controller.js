import { Controller } from '@hotwired/stimulus';
import Sortable from 'sortablejs';

export default class extends Controller {
    connect() {
        this.observer = new MutationObserver(() => this.sync());
        this.observer.observe(this.element, { childList: true, subtree: true });
        this.sync();
    }

    disconnect() {
        this.sortable?.destroy();
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

        if (items.length === 0) {
            return;
        }

        const container = items[0].parentElement;
        if (container !== this.sortableContainer) {
            this.sortable?.destroy();
            this.sortableContainer = container;
            this.sortable = Sortable.create(container, {
                handle: '.accordion-button',
                animation: 150,
            });
        }
    }
}
