import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        listenTo: String,
    }

    connect() {
        this.eventSource = new EventSource(this.listenToValue)

        this.eventSource.addEventListener('acknowledgement', (event) => {
            try {
                const data = JSON.parse(event.data)
                const selector = `[data-checkpoint="${data.stream}"]`
                const element = this.element.querySelector(selector)

                if (element) {
                    element.innerText = `${data.checkpoint} / ${data.maxSequence}`
                }
            } catch (e) {
                // Silently ignore malformed events
                // console.error('Failed to process acknowledgement event', e)
            }
        })
    }

    disconnect() {
        this.eventSource?.close()
    }
}
