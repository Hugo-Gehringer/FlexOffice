import { Controller } from '@hotwired/stimulus';
import { initFlowbite } from 'flowbite';

export default class extends Controller {
    static values = { bookedDates: Array }
    connect() {
        setTimeout(() => {
            this.initCalendar();
        }, 100);
        this.form = this.element.querySelector('form');
        this.deskId = this.element.id.replace('reservationModal-', '');

        if (this.form) {
            this.form.addEventListener('submit', this.handleFormSubmit.bind(this));
        } else {
            console.error('Form not found in modal');
        }
    }

    handleFormSubmit(event) {
        // Validate that a date is selected
        const dateInput = this.element.querySelector(`#reservation_date_${this.deskId}`);
        if (!dateInput || !dateInput.value) {
            event.preventDefault();
            console.error('Please select a date for your reservation');
            alert('Please select a date for your reservation');
            return false;
        }
    }

    initCalendar() {
        const calendarContainer = this.element.querySelector(`#calendar-container-${this.deskId}`);
        const dateInput = this.element.querySelector(`#reservation_date_${this.deskId}`);

        if (!calendarContainer || !dateInput) {
            console.error('Calendar container or date input not found');
            return;
        }

        // Get availability data from data attributes
        const availableDays = {
            0: dateInput.dataset.sunday === '1',
            1: dateInput.dataset.monday === '1',
            2: dateInput.dataset.tuesday === '1',
            3: dateInput.dataset.wednesday === '1',
            4: dateInput.dataset.thursday === '1',
            5: dateInput.dataset.friday === '1',
            6: dateInput.dataset.saturday === '1'
        };

        const bookedDates = this.bookedDatesValue || window.bookedDates?.[this.deskId] || [];

        const isDateDisabled = (date) => {
            // Create today's date without timezone issues
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            // Format date as YYYY-MM-DD without timezone conversion
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            const dateStr = `${year}-${month}-${day}`;

            const isPast = date < today;
            const isBooked = bookedDates.includes(dateStr);
            const isUnavailableDay = !availableDays[date.getDay()];


            return isPast || isBooked || isUnavailableDay;
        };

        // Initialize flatpickr
        calendarContainer.innerHTML = `<div id="flatpickr-calendar-${this.deskId}"></div>`;
        flatpickr(`#flatpickr-calendar-${this.deskId}`, {
            dateFormat: 'Y-m-d',
            minDate: 'today',
            inline: true,
            static: true,
            disableMobile: true,
            enableTime: false,
            time_24hr: false,
            disable: [isDateDisabled],
            onChange: (selectedDates, dateStr, instance) => {
                // Ensure we use the dateStr directly to avoid timezone issues
                dateInput.value = dateStr;
            }
        });
    }
}
