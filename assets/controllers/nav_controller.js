import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['nav', 'submenu']

    toggle(event) {
        this.navTarget.classList.toggle('hidden')
    }

    toggleSubmenu(event) {
        event.preventDefault()
        const submenu = event.currentTarget.nextElementSibling
        if (submenu) {
            submenu.classList.toggle('hidden')
        }
    }

    openSubmenu(event) {
        const submenu = event.currentTarget.querySelector('[data-nav-target="submenu"]')
        if (submenu) {
            submenu.classList.remove('hidden')
        }
    }

    closeSubmenu(event) {
        const submenu = event.currentTarget.querySelector('[data-nav-target="submenu"]')
        if (submenu) {
            submenu.classList.add('hidden')
        }
    }
}
