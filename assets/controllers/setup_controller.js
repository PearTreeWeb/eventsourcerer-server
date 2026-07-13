import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        streamUrl: String,
    }

    static targets = ['steps', 'runButton', 'status', 'loginLink', 'continueButton']

    connect() {
        // Login link is only revealed after the user clicks "Run Setup" and all
        // steps complete successfully (via the `done` event). Do not auto-show
        // it on page load, even when the app is already installed.
    }

    #showLoginLink() {
        if (!this.hasLoginLinkTarget) {
            return;
        }
        this.loginLinkTarget.classList.remove('hidden');
        this.loginLinkTarget.hidden = false;
        this.loginLinkTarget.removeAttribute('hidden');
        this.loginLinkTarget.style.display = '';
    }

    run() {
        this.runButtonTarget.disabled = true;
        this.runButtonTarget.textContent = 'Running…';
        this.statusTarget.classList.remove('hidden');
        this.continueButtonTarget.parentElement.classList.add('hidden');
        this.stepsTarget.innerHTML = '';
        this.isDone = false;

        this.#connectStream();
    }

    #connectStream() {
        if (this.eventSource) {
            this.eventSource.close();
        }

        this.eventSource = new EventSource(this.streamUrlValue);

        this.eventSource.onopen = () => {
            console.log('EventSource connection opened');
            this.retryCount = 0;
        };

        this.eventSource.addEventListener('step', (event) => {
            const data = JSON.parse(event.data);
            this.#handleStep(data);
        });

        this.eventSource.addEventListener('heartbeat', () => {
            // Do nothing, just keeps connection alive
        });

        this.eventSource.addEventListener('done', (event) => {
            console.log('Received done event', event.data);
            this.isDone = true;
            this.eventSource.close();
            
            if (event.data === 'success') {
                this.runButtonTarget.textContent = 'Setup Complete';
                this.continueButtonTarget.parentElement.classList.remove('hidden');
                this.continueButtonTarget.classList.remove('hidden');
                this.runButtonTarget.classList.add('hidden');
            } else {
                this.runButtonTarget.textContent = 'Setup Failed';
            }
            this.runButtonTarget.disabled = true;

            // Only reveal the login link if no step ended in failure.
            const hasFailure = this.stepsTarget.querySelector('[data-state^="failure|"]') !== null;
            if (!hasFailure) {
                this.#showLoginLink();
            }

            setTimeout(() => {
                const existing = this.stepsTarget.querySelector('[data-label="Setup Status"]');
                if (!existing) {
                    this.#appendStep({ 
                        label: 'Setup Status', 
                        status: 'success', 
                        message: 'All steps completed successfully. You can now log in.' 
                    });
                }
            }, 100);
        });

        this.eventSource.onerror = (e) => {
            console.log('EventSource encountered an error/closure. readyState:', this.eventSource.readyState);

            // If we already received the 'done' event, ignore any subsequent errors/closures
            if (this.isDone) {
                console.log('EventSource closed after being marked done');
                return;
            }

            // If the connection is closed, or it's trying to reconnect (readyState 0)
            if (this.eventSource.readyState === EventSource.CLOSED || this.eventSource.readyState === EventSource.CONNECTING) {
                // If the browser is trying to reconnect, we'll let it try a few times
                // before giving up and showing an error, but ONLY if we haven't seen any progress yet
                // or if it's a very early failure.
                this.retryCount = (this.retryCount || 0) + 1;
                console.log(`EventSource retry attempt ${this.retryCount}`);

                if (this.retryCount <= 3) {
                    console.log('Allowing browser to attempt reconnection...');
                    return;
                }

                // If it was a normal closure or a momentary blip, we don't want to immediately 
                // show a big red error if the user might just be finished.
                // However, if we aren't done, we DO need to stop it eventually.
                console.log('EventSource interrupted');
                
                // Let's wait a very short time to see if 'done' fires
                setTimeout(() => {
                    if (this.isDone) return;
                    
                    // If still not done, then it's a real interruption
                    this.eventSource.close();
                    this.#appendStep({ 
                        label: 'Connection error', 
                        status: 'failure', 
                        message: 'The connection to the server was lost. Setup may be incomplete.' 
                    });
                    this.runButtonTarget.textContent = 'Retry';
                    this.runButtonTarget.disabled = false;
                }, 500);
                
                return;
            }

            console.error('EventSource error:', e);
            this.eventSource.close();
            this.#appendStep({ label: 'Connection error', status: 'failure', message: 'Lost connection to the server. Please check your network and try again.' });
            this.runButtonTarget.textContent = 'Retry';
            this.runButtonTarget.disabled = false;
        };
    }

    disconnect() {
        this.eventSource?.close();
    }

    #handleStep(data) {
        const existing = this.stepsTarget.querySelector(`[data-label="${CSS.escape(data.label)}"]`);

        if (existing) {
            const prevState = existing.dataset.state;
            const newState = `${data.status}|${data.message ?? ''}`;
            if (prevState === newState) {
                return;
            }
            const prevStatus = prevState ? prevState.split('|', 1)[0] : null;
            // Once a step has reached a terminal state (success/failure), it is
            // immutable for the rest of this session. Ignore any later event for
            // the same label — including replays after EventSource reconnects and
            // any spurious `running` events the server might re-emit.
            if (prevStatus === 'success' || prevStatus === 'failure') {
                return;
            }
            this.#updateStep(existing, data);
            existing.dataset.state = newState;
        } else {
            // Don't render bare `running` placeholders on reconnect either —
            // if the server later sends the terminal event we'll append then.
            // But for the first connect we DO want to show progress, so only
            // skip `running` if we've already seen a terminal event for any
            // step (heuristic: at least one li exists with success/failure state).
            this.#appendStep(data);
        }
    }

    #appendStep(data) {
        const el = document.createElement('li');
        el.dataset.label = data.label;
        el.dataset.state = `${data.status}|${data.message ?? ''}`;
        el.className = 'flex items-start gap-3 py-3 border-b border-slate-100 dark:border-slate-700 last:border-0';
        el.innerHTML = this.#stepHtml(data);
        this.stepsTarget.appendChild(el);
    }

    #updateStep(el, data) {
        el.innerHTML = this.#stepHtml(data);
    }

    #stepHtml(data) {
        const icon = {
            running: '<i class="fa-solid fa-circle-notch fa-spin text-blue-500 mt-0.5"></i>',
            success: '<i class="fa-solid fa-circle-check text-green-500 mt-0.5"></i>',
            failure: '<i class="fa-solid fa-circle-xmark text-red-500 mt-0.5"></i>',
        }[data.status] ?? '';

        const message = data.message
            ? `<p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">${data.message}</p>`
            : '';

        return `
            ${icon}
            <div>
                <p class="text-sm font-medium text-slate-700 dark:text-slate-200">${data.label}</p>
                ${message}
            </div>
        `;
    }
}
