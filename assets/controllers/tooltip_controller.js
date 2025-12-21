import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        text: String,
        position: { type: String, default: 'top' }
    }

    connect() {
        this.tooltip = null;
        this.element.classList.add('relative', 'cursor-help', 'border-b', 'border-dotted', 'border-slate-400');
    }

    show() {
        if (this.tooltip) return;

        this.tooltip = document.createElement('div');
        this.tooltip.className = 'absolute z-50 px-3 py-2 text-sm font-medium text-white bg-slate-900 dark:bg-slate-700 rounded-lg shadow-lg whitespace-nowrap pointer-events-none';
        this.tooltip.textContent = this.textValue;

        // Position tooltip
        if (this.positionValue === 'top') {
            this.tooltip.classList.add('bottom-full', 'left-1/2', '-translate-x-1/2', 'mb-2');
        } else if (this.positionValue === 'bottom') {
            this.tooltip.classList.add('top-full', 'left-1/2', '-translate-x-1/2', 'mt-2');
        }

        // Add arrow
        const arrow = document.createElement('div');
        arrow.className = 'absolute w-2 h-2 bg-slate-900 dark:bg-slate-700 rotate-45';
        if (this.positionValue === 'top') {
            arrow.classList.add('bottom-0', 'left-1/2', '-translate-x-1/2', 'translate-y-1');
        } else {
            arrow.classList.add('top-0', 'left-1/2', '-translate-x-1/2', '-translate-y-1');
        }
        this.tooltip.appendChild(arrow);

        this.element.appendChild(this.tooltip);

        // Animate in
        requestAnimationFrame(() => {
            this.tooltip.classList.add('opacity-100', 'transition-opacity', 'duration-200');
        });
    }

    hide() {
        if (!this.tooltip) return;

        this.tooltip.classList.remove('opacity-100');
        this.tooltip.classList.add('opacity-0');

        setTimeout(() => {
            if (this.tooltip) {
                this.tooltip.remove();
                this.tooltip = null;
            }
        }, 200);
    }
}
