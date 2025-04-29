import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        // Optional: Auto-hide after 5 seconds
        setTimeout(() => {
            this.close();
        }, 5000);
    }

    close() {
        this.element.remove();
    }
}