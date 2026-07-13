import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = [
        'condition',
        'maxApplicableAllSequence',
        'processedUpToAllSequence',
        'percentageComplete',
    ]

    static values = {
        listenTo: String,
        resettingText: String,
        runningText: String,
        failedText: String,
        finishedText: String,
    }

    connect() {
        this.eventSource = new EventSource(this.listenToValue)

        this.eventSource.addEventListener('projectionCondition', (event) => {
            const data = JSON.parse(event.data)

            this.conditionTarget.classList.remove('text-amber-600')
            this.conditionTarget.classList.remove('text-green-600')
            this.conditionTarget.classList.remove('text-red-400')

            const current = parseInt(data.currentAllSequence) || 0;
            const max = parseInt(data.maxAllSequence) || 0;
            const percentage = max > 0 ? Math.floor((current / max) * 100) : 0;

            this.conditionTarget.innerText = data.condition
            this.maxApplicableAllSequenceTarget.innerText = data.maxAllSequence
            this.processedUpToAllSequenceTarget.innerText = data.currentAllSequence
            this.percentageCompleteTarget.innerText = `${percentage}%`

            if (this.resettingTextValue === data.condition) {
                this.conditionTarget.classList.add('text-amber-600')
            }

            if (this.runningTextValue === data.condition) {
                this.conditionTarget.classList.add('text-green-600')
            }

            if (this.failedTextValue === data.condition) {
                this.conditionTarget.classList.add('text-red-400')
            }

            if (this.finishedTextValue === data.condition) {
                this.conditionTarget.classList.add('text-green-400')
            }
        })
    }

    disconnect() {
        this.eventSource?.close()
    }
}
