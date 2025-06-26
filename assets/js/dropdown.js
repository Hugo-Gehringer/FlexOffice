// Initialize dropdown functionality
document.addEventListener('DOMContentLoaded', function() {
    // Get all dropdown triggers
    const dropdownTriggers = document.querySelectorAll('[data-dropdown-toggle]');
    
    dropdownTriggers.forEach(trigger => {
        const targetId = trigger.getAttribute('data-dropdown-toggle');
        const target = document.getElementById(targetId);
        
        if (target) {
            // Add click event listener to toggle dropdown
            trigger.addEventListener('click', function(event) {
                event.stopPropagation();
                target.classList.toggle('hidden');
                
                // Set aria-expanded attribute
                const isExpanded = !target.classList.contains('hidden');
                trigger.setAttribute('aria-expanded', isExpanded);
                
                // Position the dropdown
                positionDropdown(trigger, target);
            });
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function(event) {
                if (!target.contains(event.target) && !trigger.contains(event.target)) {
                    target.classList.add('hidden');
                    trigger.setAttribute('aria-expanded', 'false');
                }
            });
        }
    });
    
    // Position dropdown relative to trigger
    function positionDropdown(trigger, dropdown) {
        const triggerRect = trigger.getBoundingClientRect();
        
        dropdown.style.position = 'absolute';
        dropdown.style.top = `${triggerRect.bottom + window.scrollY}px`;
        dropdown.style.right = `${window.innerWidth - triggerRect.right}px`;
    }
});
