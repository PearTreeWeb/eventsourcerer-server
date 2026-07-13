import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static outlets = ['new-events-listener']

    static targets = ['dialog']

    show() {
        this.dialogTarget.showModal()
        console.log(this.newEventsListenerOutlets)
        this.newEventsListenerOutlets.forEach(child => child.modalOpened())
    }

    close() {
        this.dialogTarget.close()
        this.newEventsListenerOutlets.forEach(child => child.modalClosed())
    }
}
