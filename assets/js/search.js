// Search functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('topbar-search');
    const sidebarSearchInput = document.getElementById('sidebar-search');
    const searchResults = document.getElementById('search-results');
    
    let searchTimeout;
    let currentRequest;
    
    // Debounced search function
    function debounceSearch(callback, delay) {
        return function(query) {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => callback(query), delay);
        };
    }
    
    // Perform search API call
    async function performSearch(query) {
        if (currentRequest) {
            currentRequest.abort();
        }
        
        if (query.length < 2) {
            hideSearchResults();
            return;
        }
        
        try {
            currentRequest = new AbortController();
            const response = await fetch(`/space/search?q=${encodeURIComponent(query)}`, {
                signal: currentRequest.signal
            });
            
            if (!response.ok) {
                throw new Error('Search failed');
            }
            
            const results = await response.json();
            displaySearchResults(results);
        } catch (error) {
            if (error.name !== 'AbortError') {
                console.error('Search error:', error);
                hideSearchResults();
            }
        }
    }
    
    // Display search results
    function displaySearchResults(results) {
        if (!searchResults) return;
        
        if (results.length === 0) {
            searchResults.innerHTML = `
                <div class="p-4 text-center text-gray-500 dark:text-gray-400">
                    <svg class="w-8 h-8 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <p class="text-sm">Aucun espace trouvé</p>
                </div>
            `;
        } else {
            searchResults.innerHTML = results.map(space => `
                <a href="${space.url}" class="block p-4 hover:bg-gray-50 dark:hover:bg-gray-700 border-b border-gray-200 dark:border-gray-600 last:border-b-0">
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-sm font-medium text-gray-900 dark:text-white truncate">${space.name}</h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">${space.description}</p>
                            <div class="flex items-center mt-2 text-xs text-gray-400 dark:text-gray-500">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="mr-3">${space.host}</span>
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                                </svg>
                                <span>${space.city}</span>
                            </div>
                        </div>
                    </div>
                </a>
            `).join('');
        }
        
        searchResults.classList.remove('hidden');
    }
    
    // Hide search results
    function hideSearchResults() {
        if (searchResults) {
            searchResults.classList.add('hidden');
        }
    }
    
    // Debounced search function
    const debouncedSearch = debounceSearch(performSearch, 300);
    
    // Event listeners for search inputs
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            const query = e.target.value.trim();
            debouncedSearch(query);
        });
        
        searchInput.addEventListener('focus', function(e) {
            const query = e.target.value.trim();
            if (query.length >= 2) {
                debouncedSearch(query);
            }
        });
        
        searchInput.addEventListener('blur', function() {
            // Delay hiding to allow clicking on results
            setTimeout(hideSearchResults, 200);
        });
    }
    
    // Sync sidebar search with header search
    if (sidebarSearchInput && searchInput) {
        sidebarSearchInput.addEventListener('input', function(e) {
            searchInput.value = e.target.value;
        });
        
        searchInput.addEventListener('input', function(e) {
            sidebarSearchInput.value = e.target.value;
        });
    }
    
    // Close search results when clicking outside
    document.addEventListener('click', function(e) {
        if (searchResults && !searchResults.contains(e.target) && 
            !searchInput?.contains(e.target) && !sidebarSearchInput?.contains(e.target)) {
            hideSearchResults();
        }
    });
    
    // Handle keyboard navigation
    if (searchInput) {
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                hideSearchResults();
                searchInput.blur();
            }
        });
    }
});

// Clear search when navigating away from spaces page
document.addEventListener('DOMContentLoaded', function() {
    const currentPath = window.location.pathname;
    const searchInput = document.getElementById('topbar-search');
    const sidebarSearchInput = document.getElementById('sidebar-search');
    
    // Clear search inputs if not on spaces page
    if (!currentPath.includes('/space') && searchInput) {
        searchInput.value = '';
    }
    
    if (!currentPath.includes('/space') && sidebarSearchInput) {
        sidebarSearchInput.value = '';
    }
});
