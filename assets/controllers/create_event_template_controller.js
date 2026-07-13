import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['input']

    formatName(event) {
        this.inputTarget.value = event.target.value.replace(' ', '-').toLowerCase()
    }
}
