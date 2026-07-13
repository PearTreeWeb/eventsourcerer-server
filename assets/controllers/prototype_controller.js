import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['collectionHolder', 'item']
    static values = {
        collectionHolderClass: String,
    }

    addProperty(e) {
        e.preventDefault();

        const collectionHolderClass = this.element.dataset.collectionHolderClass || e.currentTarget.dataset.collectionHolderClass;
        const collectionHolder = document.getElementById(collectionHolderClass);
        const index = this.element.dataset.index || e.currentTarget.dataset.index;

        if (!this.collectionHolderClassValue) {
            this.collectionHolderClassValue = collectionHolderClass
        }

        const item = document.createElement('li');
        item.id = `${collectionHolderClass}${index}`

        const prototype = this.element.dataset.prototype || e.currentTarget.dataset.prototype;

        item.innerHTML = prototype
            .replace(
                /__name__/g,
                index
            );

        item.dataset.prototypeTarget = 'item'

        collectionHolder.appendChild(item);

        if (this.element.dataset.index) {
            this.element.dataset.index++;
        } else if (e.currentTarget.dataset.index) {
            e.currentTarget.dataset.index++;
        }
    }
    removeProperty(e) {
        const index = e.target.dataset.index || e.currentTarget.dataset.index;
        const collectionHolderClass = this.element.dataset.collectionHolderClass || this.collectionHolderClassValue;
        const removeItem = this.itemTargets.find(item => item.id === `${collectionHolderClass}${index}`)

        if (removeItem) {
            this.collectionHolderTarget.removeChild(removeItem)
        }
    }
}
