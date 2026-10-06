import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    static targets = ["checkbox"];
    static values = {
        urlTemplate: String,
        frameId: String
    };

    filter() {
        const selected = this.checkboxTargets
            .filter(cb => cb.checked)
            .map(cb => cb.value)
            .join(',');

        let url = this.urlTemplateValue
            .replace('AUTHORS_PLACEHOLDER', selected || 'EMPTY_AUTHORS')
            .replace('EMPTY_SEARCH', 'null')
            .replace('EMPTY_AUTHORS', 'null');

        const frame = document.getElementById(this.frameIdValue);
        if (frame) {
            frame.src = url;
        }
    }
}
