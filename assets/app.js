/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.css';

// start the Stimulus application
import './bootstrap.js';

const confirmMethod = async (message) => {
    return new Promise((resolve, reject) => {
        const dialog = document.createElement('dialog')

        const continueButton = document.createElement('button')
        continueButton.classList.add('button', 'button-primary', 'mt-5')
        continueButton.innerText = 'Continue'
        continueButton.onclick = () => {
            resolve(true)
        }

        const cancelButton = document.createElement('button')
        cancelButton.classList.add('button', 'button-danger', 'mt-5')
        cancelButton.innerText = 'Cancel'
        cancelButton.onclick = () => {
            resolve(false)

            dialog.close()

            document.body.removeChild(dialog)
        }

        const messageElement = document.createElement('p')
        messageElement.innerText = message

        const actions = document.createElement('div')
        actions.classList.add('flex', 'space-x-5')
        actions.append(continueButton)
        actions.append(cancelButton)

        const container = document.createElement('div')
        container.append(messageElement)
        container.append(actions)
        container.classList.add('p-5')

        dialog.append(container)

        document.body.append(dialog)
        dialog.showModal()
    });
}

Turbo.config.forms.confirm = confirmMethod
