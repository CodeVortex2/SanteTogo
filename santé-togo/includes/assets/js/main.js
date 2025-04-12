// Gestion du menu mobile
document.getElementById('mobile-menu-button').addEventListener('click', function() {
    const menu = document.getElementById('mobile-menu');
    menu.classList.toggle('hidden');
});

// Menu mobile
document.getElementById('mobile-menu-button').addEventListener('click', function() {
    document.getElementById('mobile-menu').classList.toggle('hidden');
});

// Dropdown profil
document.addEventListener('click', function(e) {
    if (!e.target.closest('.profile-dropdown')) {
        document.querySelectorAll('.dropdown-menu').forEach(function(menu) {
            menu.classList.add('hidden');
        });
    }
});

// Animation au scroll
window.addEventListener('scroll', function() {
    const elements = document.querySelectorAll('.animate-on-scroll');
    
    elements.forEach(function(element) {
        const elementPosition = element.getBoundingClientRect().top;
        const screenPosition = window.innerHeight / 1.2;
        
        if (elementPosition < screenPosition) {
            element.classList.add('animated');
        }
    });
});

// Confirmation avant déconnexion
document.querySelectorAll('[href="logout.php"]').forEach(function(link) {
    link.addEventListener('click', function(e) {
        if (!confirm('Êtes-vous sûr de vouloir vous déconnecter ?')) {
            e.preventDefault();
        }
    });
});

// Gestion des notifications
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg text-white ${type === 'success' ? 'bg-green-500' : 'bg-red-500'}`;
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.classList.add('opacity-0', 'transition', 'duration-500');
        setTimeout(() => {
            notification.remove();
        }, 500);
    }, 3000);
}

// Gestion des erreurs de formulaire
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function(e) {
        const requiredFields = this.querySelectorAll('[required]');
        let isValid = true;
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.classList.add('border-red-500');
                isValid = false;
            } else {
                field.classList.remove('border-red-500');
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            showNotification('Veuillez remplir tous les champs obligatoires.', 'error');
        }
    });
});

// Initialisation des tooltips
document.querySelectorAll('[data-tooltip]').forEach(element => {
    element.addEventListener('mouseenter', function() {
        const tooltip = document.createElement('div');
        tooltip.className = 'absolute z-10 bg-gray-800 text-white text-xs rounded py-1 px-2';
        tooltip.textContent = this.getAttribute('data-tooltip');
        
        const rect = this.getBoundingClientRect();
        tooltip.style.top = `${rect.top - 30}px`;
        tooltip.style.left = `${rect.left + rect.width / 2}px`;
        tooltip.style.transform = 'translateX(-50%)';
        
        this.appendChild(tooltip);
        
        this.addEventListener('mouseleave', function() {
            tooltip.remove();
        });
    });
});