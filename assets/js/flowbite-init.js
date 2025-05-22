// Import and expose Flowbite initialization function
import { initFlowbite } from 'flowbite';

// Expose initFlowbite globally
window.initFlowbite = initFlowbite;

// Initialize Flowbite on page load
document.addEventListener('DOMContentLoaded', function() {
    initFlowbite();
});
