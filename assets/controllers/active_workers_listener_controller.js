import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        listenTo: String,
    }

    connect() {
        this.eventSource = new EventSource(this.listenToValue)

        this.eventSource.addEventListener('activeWorkersUpdated', (event) => {
            this.element.querySelectorAll('tr').forEach(element => element.remove())

            const workers = JSON.parse(event.data)

            const tbody = this.element

            for (let applicationName in workers) {
                for (let worker in workers[applicationName]) {
                    const tr = document.createElement('tr')
                    tr.classList.add('even:bg-gray-50', 'hover:bg-gray-200')
                    tr.innerHTML = `<td class="p-2">${applicationName}</td><td>${worker}</td>`
                    tbody.appendChild(tr)
                }
            }
        })
    }

    disconnect() {
        this.eventSource?.close()
    }
}
