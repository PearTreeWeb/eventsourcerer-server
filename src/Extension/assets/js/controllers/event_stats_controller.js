import { Controller } from '@hotwired/stimulus';
import { Chart, registerables } from 'chart.js';
Chart.register(...registerables);

export default class extends Controller {
    static values = {
        name: String,
        state: Object
    }

    connect() {
        const element = this.element

        new Chart(
            element,
            {
                type: 'bar',
                data: {
                    datasets: [
                        {
                            label: this.nameValue,
                            data: this.stateValue,
                            backgroundColor: '#c4a8ff',
                            borderColor: '#c4a8ff',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            }
        );
    }
}
