import { Controller } from '@hotwired/stimulus';
import tippy from 'tippy.js';
import 'tippy.js/dist/tippy.css'

export default class extends Controller {
    static targets = ['children']

    connect() {
        tippy(this.childrenTargets)
    }
}
