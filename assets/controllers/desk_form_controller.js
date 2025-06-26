import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        // Get the form and submit button
        this.form = this.element;
        this.submitButton = this.element.querySelector('#desk-submit-button');

        // Add event listener to the form
        this.form.addEventListener('submit', this.handleSubmit.bind(this));
    }

    handleSubmit(event) {
        // Don't prevent default - let the form submit normally
        console.log('Form is being submitted');

        // Validate form data before submission
        const nameField = this.form.querySelector('#desk_form_name');
        if (!nameField || !nameField.value.trim()) {
            event.preventDefault();
            console.error('Name field is required');
            return false;
        }

        // Close the modal after a short delay to allow the form to submit
        setTimeout(() => {
            const modal = document.getElementById('deskModal');
            if (modal && typeof modal.hide === 'function') {
                modal.hide();
            }
        }, 100);
    }
}
