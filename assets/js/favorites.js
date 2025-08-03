// Favorites functionality
document.addEventListener('DOMContentLoaded', function() {
    const favoriteButtons = document.querySelectorAll('.favorite-btn');
    
    favoriteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const spaceId = this.dataset.spaceId;
            const isFavorited = this.dataset.favorited === 'true';
            
            // Disable button during request
            this.disabled = true;
            this.style.opacity = '0.6';
            
            // Make AJAX request to toggle favorite
            fetch(`/favorites/toggle/${spaceId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update button state
                    const newFavorited = data.action === 'added';
                    this.dataset.favorited = newFavorited.toString();
                    
                    // Update button appearance
                    if (newFavorited) {
                        this.classList.remove('text-gray-400', 'hover:text-red-500');
                        this.classList.add('text-red-500', 'hover:text-red-600');
                        this.title = 'Retirer des favoris';
                    } else {
                        this.classList.remove('text-red-500', 'hover:text-red-600');
                        this.classList.add('text-gray-400', 'hover:text-red-500');
                        this.title = 'Ajouter aux favoris';
                        
                        // If we're on the favorites page, remove the card
                        if (window.location.pathname.includes('/favorites')) {
                            const card = this.closest('.bg-white, .dark\\:bg-gray-800');
                            if (card) {
                                card.style.transition = 'opacity 0.3s ease-out';
                                card.style.opacity = '0';
                                setTimeout(() => {
                                    card.remove();
                                    
                                    // Check if there are no more favorites
                                    const remainingCards = document.querySelectorAll('.bg-white.dark\\:bg-gray-800, .bg-white');
                                    if (remainingCards.length === 0) {
                                        // Reload page to show empty state
                                        window.location.reload();
                                    }
                                }, 300);
                            }
                        }
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
            })
            .finally(() => {
                // Re-enable button
                this.disabled = false;
                this.style.opacity = '1';
            });
        });
    });


    

});
