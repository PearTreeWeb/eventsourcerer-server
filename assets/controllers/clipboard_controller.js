import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        value: String,
    };

    async copy(event) {
        event.preventDefault();

        const params = event.params || {};
        const text = params.value ?? this.valueValue ?? this.element.textContent.trim();

        try {
            if (navigator.clipboard && window.isSecureContext) {
                await navigator.clipboard.writeText(text);
            } else {
                const textarea = document.createElement('textarea');
                textarea.value = text;
                textarea.style.position = 'fixed';
                textarea.style.opacity = '0';
                document.body.appendChild(textarea);
                textarea.select();
                document.execCommand('copy');
                document.body.removeChild(textarea);
            }
            this.#flashIcon(true);
        } catch (e) {
            console.error('Clipboard copy failed', e);
            this.#flashIcon(false);
        }
    }

    #flashIcon(success) {
        const icon = this.element.querySelector('i');
        if (!icon) return;

        const original = icon.className;
        icon.className = success
            ? 'fa-solid fa-check text-emerald-600'
            : 'fa-solid fa-xmark text-red-600';

        setTimeout(() => {
            icon.className = original;
        }, 1200);
    }
}
