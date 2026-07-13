import { Controller } from '@hotwired/stimulus';
import Toastify from 'toastify-js'
import 'toastify-js/src/toastify.css'

export default class extends Controller {
    static values = {
        listenTo: String,
        modalOpen: Boolean,
        reloadOnClose: Boolean,
    }

    connect() {
        this.eventSource = new EventSource(this.listenToValue)

        this.eventSource.addEventListener('newEvent', (event) => {
            if (this.modalOpenValue) {
                this.reloadOnCloseValue = true

                return
            }

            Turbo.visit(window.location.href)
        })
    }

    modalOpened() {
        this.modalOpenValue = true
    }

    modalClosed() {
        if (this.reloadOnCloseValue) {
            Turbo.visit(window.location.href)
        }
    }

    disconnect() {
        this.eventSource?.close()
    }
}
