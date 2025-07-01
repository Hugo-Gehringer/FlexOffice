import './bootstrap.js';
import './styles/app.css';
import 'flowbite';

import flatpickr from 'flatpickr';
import 'flatpickr/dist/flatpickr.min.css';
import { French } from 'flatpickr/dist/l10n/fr.js';

function initializeDatepickers() {
    console.log('Initializing datepickers...');
    const datepickers = document.querySelectorAll('.datepicker');
    console.log('Found datepicker elements:', datepickers.length);

    datepickers.forEach((el) => {
        // Éviter la double initialisation
        if (el._flatpickr) {
            console.log('Flatpickr already initialized on element:', el);
            return;
        }

        console.log('Initializing flatpickr on element:', el);
        console.log('Element type:', el.type);
        console.log('Element classes:', el.className);

        const fp = flatpickr(el, {
            dateFormat: "Y-m-d",
            allowInput: true,
            clickOpens: true,
            locale: French, // Utilise la locale française
            onReady: function(selectedDates, dateStr, instance) {
                console.log('Flatpickr ready for element:', el);
            },
            onOpen: function(selectedDates, dateStr, instance) {
                console.log('Flatpickr opened for element:', el);
            }
        });

        console.log('Flatpickr instance created:', fp);

        // Test d'ouverture manuelle
        el.addEventListener('click', function() {
            console.log('Input clicked, trying to open flatpickr');
            if (el._flatpickr) {
                el._flatpickr.open();
            }
        });
    });
}

// Initialiser au chargement de la page
document.addEventListener('DOMContentLoaded', initializeDatepickers);

// Réinitialiser après les navigations Turbo
document.addEventListener('turbo:load', initializeDatepickers);



