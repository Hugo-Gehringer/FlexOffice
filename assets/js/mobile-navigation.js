// Mobile navigation functionality
document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggle = document.querySelector('[data-drawer-toggle="drawer-navigation"]');
    const sidebar = document.getElementById('drawer-navigation');
    const body = document.body;
    
    // Create backdrop element
    const backdrop = document.createElement('div');
    backdrop.className = 'sidebar-backdrop';
    backdrop.id = 'sidebar-backdrop';
    body.appendChild(backdrop);
    
    // Toggle sidebar function
    function toggleSidebar() {
        const isOpen = !sidebar.classList.contains('-translate-x-full');
        
        if (isOpen) {
            // Close sidebar
            sidebar.classList.add('-translate-x-full');
            backdrop.classList.remove('show');
            body.classList.remove('overflow-hidden');
        } else {
            // Open sidebar
            sidebar.classList.remove('-translate-x-full');
            backdrop.classList.add('show');
            body.classList.add('overflow-hidden');
        }
    }
    
    // Close sidebar function
    function closeSidebar() {
        sidebar.classList.add('-translate-x-full');
        backdrop.classList.remove('show');
        body.classList.remove('overflow-hidden');
    }
    
    // Event listeners
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', toggleSidebar);
    }
    
    // Close sidebar when clicking backdrop
    backdrop.addEventListener('click', closeSidebar);
    
    // Close sidebar when clicking a link (mobile only)
    const sidebarLinks = sidebar.querySelectorAll('a');
    sidebarLinks.forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth < 768) {
                closeSidebar();
            }
        });
    });
    
    // Handle window resize
    window.addEventListener('resize', () => {
        if (window.innerWidth >= 768) {
            closeSidebar();
        }
    });
    
    // Handle escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeSidebar();
        }
    });
});

// Responsive table functionality
document.addEventListener('DOMContentLoaded', function() {
    const tables = document.querySelectorAll('table');
    
    tables.forEach(table => {
        // Wrap tables in responsive container if not already wrapped
        if (!table.parentElement.classList.contains('table-responsive')) {
            const wrapper = document.createElement('div');
            wrapper.className = 'table-responsive overflow-x-auto';
            table.parentNode.insertBefore(wrapper, table);
            wrapper.appendChild(table);
        }
    });
});

// Mobile form improvements
document.addEventListener('DOMContentLoaded', function() {
    // Add responsive classes to forms
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        // Add responsive classes to form elements
        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            if (!input.classList.contains('form-input') && 
                !input.classList.contains('form-select') && 
                !input.classList.contains('form-textarea')) {
                input.classList.add('w-full');
            }
        });
    });
});
