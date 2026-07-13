import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['column2', 'column3', 'column4', 'formInput', 'select']

    static values = {
        eventPropertyKey: String,
        mutationTypes: Object,
        prepositionKey: String,
        projectionPropertyKey: String,
        property: String,
    }

    connect() {
        this._formatColumns()
    }

    changeDisplay(event) {
        this._formatColumns()
    }
    _formatColumns() {
        const selectedDataset = this.selectTarget.options[this.selectTarget.selectedIndex].dataset

        const selectedMutationType = this.selectTarget.options[this.selectTarget.selectedIndex].text

        if (!this.mutationTypesValue.hasOwnProperty(selectedMutationType)) {
            return;
        }

        const displayOrder = this.mutationTypesValue[selectedMutationType]
        const prepositionType = selectedDataset.preposition

        const displayPart1 = displayOrder[1] ?? ''
        const displayPart2 = displayOrder[2] ?? ''
        const displayPart3 = displayOrder[3] ?? ''

        this.column2Target.innerHTML = this._displayValue(displayPart1, prepositionType)
        this.column3Target.innerHTML = this._displayValue(displayPart2, prepositionType)
        this.column4Target.innerHTML = this._displayValue(displayPart3, prepositionType)
    }

    _textDiv(content) {
        return `<div class="whitespace-nowrap text-slate-500 font-medium">${content}</div>`
    }

    _displayValue(displayPart, prepositionType) {
        switch (displayPart) {
            case this.eventPropertyKeyValue:
                return this.formInputTarget.innerHTML
            case this.prepositionKeyValue:
                return this._textDiv(prepositionType)
            case this.projectionPropertyKeyValue:
                return this._textDiv(this.propertyValue)
            default:
                return ''
        }
    }
}
