import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        // Get the form
        this.form = this.element.querySelector('#desk-form');

        // Add event listener to the form
        if (this.form) {
            this.form.addEventListener('submit', this.handleFormSubmit.bind(this));
        }
    }

    handleFormSubmit(event) {
        // Don't prevent default - let the form submit
        // But make sure the modal doesn't close immediately
        const modal = document.getElementById('deskModal');
        if (modal) {
            // Prevent Flowbite from closing the modal
            event.stopPropagation();
        }
    }
}
