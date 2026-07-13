import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['holder', 'select'];
    static values = {
        prototype: String,
        index: Number
    }

    connect() {
        this.initialValues = Array.from(this.holderTarget.querySelectorAll('[data-initial-value]')).map(input => input.value);
        this.updateParameters();
    }

    updateParameters() {
        const selectedOption = this.selectTarget.options[this.selectTarget.selectedIndex];
        if (!selectedOption || !selectedOption.dataset.parameters) {
            this.holderTarget.innerHTML = '';
            return;
        }

        const parameters = JSON.parse(selectedOption.dataset.parameters);
        const currentInputs = Array.from(this.holderTarget.querySelectorAll('input'));
        const currentValues = currentInputs.length > 0 
            ? currentInputs.map(input => input.value)
            : this.initialValues;

        // Capture existing errors before clearing
        const existingErrors = {};
        this.holderTarget.querySelectorAll('[data-parameter-index]').forEach(container => {
            const index = container.dataset.parameterIndex;
            // Support both hidden data-errors (initial load) and rendered errors (subsequent updates)
            const errorDiv = container.querySelector('[data-errors]') || container.querySelector('.text-red-500');
            if (errorDiv) {
                existingErrors[index] = errorDiv.innerHTML;
            }
        });
        
        this.holderTarget.innerHTML = '';
        
        parameters.forEach((placeholder, i) => {
            const container = document.createElement('div');
            container.className = 'flex flex-col';
            container.dataset.parameterIndex = i;

            const prototype = this.prototypeValue.replace(/__name__/g, i);
            container.innerHTML = prototype.trim();
            
            const input = container.querySelector('input');
            if (input) {
                input.placeholder = placeholder;
                if (currentValues && currentValues[i] !== undefined) {
                    let value = currentValues[i];
                    if (typeof value === 'object' && value !== null) {
                        value = JSON.stringify(value);
                    }
                    input.value = value;
                }
                // Ensure the name is correct if the prototype didn't use __name__ in the right place
                input.name = input.name.replace(/\[\d+\]$/, `[${i}]`);
            }

            // Re-apply errors if they exist for this index
            if (existingErrors[i]) {
                const errorDisplay = document.createElement('div');
                errorDisplay.className = 'text-red-500 text-sm mt-1 font-medium bg-red-50 border border-red-200 rounded px-1.5 py-0.5 absolute top-full left-0 z-10 whitespace-nowrap shadow-sm';
                errorDisplay.innerHTML = existingErrors[i];
                container.classList.add('relative');
                container.appendChild(errorDisplay);
            }
            
            this.holderTarget.appendChild(container);
        });
    }
}
