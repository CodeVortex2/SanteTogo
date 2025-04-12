// Gestion des rappels de médicaments
document.addEventListener('DOMContentLoaded', function() {
    // Vérifier si des médicaments doivent être pris maintenant
    const medications = document.querySelectorAll('.text-red-600.font-bold');
    if (medications.length > 0) {
        showNotification(`Vous avez ${medications.length} médicament(s) à prendre maintenant.`, 'error');
    }
    
    // Configurer les rappels
    if ('Notification' in window) {
        Notification.requestPermission().then(permission => {
            if (permission === 'granted') {
                // Planifier les rappels
                setInterval(() => {
                    checkMedicationReminders();
                }, 60000); // Vérifier toutes les minutes
            }
        });
    }
});

function checkMedicationReminders() {
    // Envoyer une requête AJAX pour vérifier les médicaments à prendre
    fetch('api/check-medications.php')
        .then(response => response.json())
        .then(data => {
            if (data.length > 0) {
                // Afficher une notification
                if ('Notification' in window && Notification.permission === 'granted') {
                    new Notification('Rappel de médicament', {
                        body: `Vous avez ${data.length} médicament(s) à prendre: ${data.map(m => m.nom_medicament).join(', ')}`,
                        icon: '/assets/images/pill-icon.png'
                    });
                }
                
                // Afficher une alerte dans l'interface
                showNotification(`Rappel: Vous avez ${data.length} médicament(s) à prendre.`, 'error');
            }
        });
}