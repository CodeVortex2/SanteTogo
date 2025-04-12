<?php
require_once 'includes/auth.php';
?>

<div class="md:w-1/4 lg:w-1/5 xl:w-1/6">
    <!-- Carte profil utilisateur -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden mb-6 transition-all duration-300 hover:shadow-lg">
        <div class="bg-gradient-to-r from-blue-500 to-teal-400 p-4 text-center">
            <div class="inline-block bg-white/20 p-3 rounded-full backdrop-blur-sm">
                <i class="fas fa-user-md text-white text-3xl"></i>
            </div>
        </div>
        
        <div class="p-6 text-center">
            <h3 class="text-xl font-semibold text-gray-800 mb-1"><?php echo htmlspecialchars($_SESSION['prenom'] . ' ' . $_SESSION['nom']); ?></h3>
            <p class="text-gray-600 text-sm mb-4"><?php echo htmlspecialchars($_SESSION['email']); ?></p>
            
            <?php if (isset($_SESSION['groupe_sanguin'])): ?>
                <span class="inline-block px-3 py-1 bg-red-100 text-red-800 rounded-full text-xs font-medium">
                    Groupe sanguin: <?php echo htmlspecialchars($_SESSION['groupe_sanguin']); ?>
                </span>
            <?php else: ?>
                <a href="profile.php" class="inline-block text-blue-600 hover:text-blue-800 text-xs mt-2">
                    <i class="fas fa-plus-circle mr-1"></i> Compléter mon profil
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Navigation -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <div class="p-4 border-b border-gray-200">
            <h4 class="font-medium text-gray-700 flex items-center">
                <i class="fas fa-bars mr-2 text-blue-500"></i>
                Menu principal
            </h4>
        </div>
        
        <nav class="p-2">
            <ul class="space-y-1">
                <li>
                    <a href="dashboard.php" class="flex items-center p-3 text-gray-600 hover:bg-blue-50 rounded-lg group transition-all">
                        <div class="bg-blue-100 text-blue-600 p-2 rounded-lg mr-3 group-hover:bg-blue-200 transition-all">
                            <i class="fas fa-tachometer-alt w-5 text-center"></i>
                        </div>
                        <span class="group-hover:text-blue-600 transition-all">Tableau de bord</span>
                        <span class="ml-auto bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">Nouveau</span>
                    </a>
                </li>
                
                <li>
                    <a href="medical-records.php" class="flex items-center p-3 text-blue-600 bg-blue-50 rounded-lg group border-l-4 border-blue-500">
                        <div class="bg-blue-200 text-blue-700 p-2 rounded-lg mr-3">
                            <i class="fas fa-file-medical w-5 text-center"></i>
                        </div>
                        <span class="font-medium">Dossier médical</span>
                    </a>
                </li>
                
                <li>
                    <a href="appointments.php" class="flex items-center p-3 text-gray-600 hover:bg-blue-50 rounded-lg group transition-all">
                        <div class="bg-blue-100 text-blue-600 p-2 rounded-lg mr-3 group-hover:bg-blue-200 transition-all">
                            <i class="fas fa-calendar-check w-5 text-center"></i>
                        </div>
                        <span class="group-hover:text-blue-600 transition-all">Rendez-vous</span>
                    </a>
                </li>
                
                <li>
                    <a href="medications.php" class="flex items-center p-3 text-gray-600 hover:bg-blue-50 rounded-lg group transition-all">
                        <div class="bg-blue-100 text-blue-600 p-2 rounded-lg mr-3 group-hover:bg-blue-200 transition-all">
                            <i class="fas fa-pills w-5 text-center"></i>
                        </div>
                        <span class="group-hover:text-blue-600 transition-all">Médicaments</span>
                        <span class="ml-auto bg-red-100 text-red-800 text-xs px-2 py-1 rounded-full">3 alertes</span>
                    </a>
                </li>
                
                <li>
                    <a href="blood-donation.php" class="flex items-center p-3 text-gray-600 hover:bg-blue-50 rounded-lg group transition-all">
                        <div class="bg-blue-100 text-blue-600 p-2 rounded-lg mr-3 group-hover:bg-blue-200 transition-all">
                            <i class="fas fa-heart w-5 text-center"></i>
                        </div>
                        <span class="group-hover:text-blue-600 transition-all">Don de sang</span>
                    </a>
                </li>
                
                <li>
                    <a href="teleconsultation.php" class="flex items-center p-3 text-gray-600 hover:bg-blue-50 rounded-lg group transition-all">
                        <div class="bg-blue-100 text-blue-600 p-2 rounded-lg mr-3 group-hover:bg-blue-200 transition-all">
                            <i class="fas fa-video w-5 text-center"></i>
                        </div>
                        <span class="group-hover:text-blue-600 transition-all">Téléconsultation</span>
                    </a>
                </li>
            </ul>
        </nav>
        
        <!-- Section secondaire -->
        <div class="p-4 border-t border-gray-200">
            <ul class="space-y-1">
                <li>
                    <a href="profile.php" class="flex items-center p-3 text-gray-600 hover:bg-gray-50 rounded-lg group transition-all">
                        <div class="bg-gray-100 text-gray-600 p-2 rounded-lg mr-3 group-hover:bg-gray-200 transition-all">
                            <i class="fas fa-user-cog w-5 text-center"></i>
                        </div>
                        <span class="group-hover:text-gray-800 transition-all">Mon profil</span>
                    </a>
                </li>
                <li>
                    <a href="settings.php" class="flex items-center p-3 text-gray-600 hover:bg-gray-50 rounded-lg group transition-all">
                        <div class="bg-gray-100 text-gray-600 p-2 rounded-lg mr-3 group-hover:bg-gray-200 transition-all">
                            <i class="fas fa-cog w-5 text-center"></i>
                        </div>
                        <span class="group-hover:text-gray-800 transition-all">Paramètres</span>
                    </a>
                </li>
                <li>
                    <a href="logout.php" class="flex items-center p-3 text-red-600 hover:bg-red-50 rounded-lg group transition-all">
                        <div class="bg-red-100 text-red-600 p-2 rounded-lg mr-3 group-hover:bg-red-200 transition-all">
                            <i class="fas fa-sign-out-alt w-5 text-center"></i>
                        </div>
                        <span class="group-hover:text-red-800 transition-all">Déconnexion</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Carte d'information -->
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mt-6 text-center">
        <i class="fas fa-info-circle text-blue-500 text-2xl mb-2"></i>
        <h4 class="font-medium text-blue-800 mb-1">Besoin d'aide ?</h4>
        <p class="text-sm text-blue-600 mb-3">Notre équipe est disponible 24/7</p>
        <a href="contact.php" class="inline-block bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2 px-4 rounded-lg transition-all">
            <i class="fas fa-headset mr-2"></i> Contactez-nous
        </a>
    </div>
</div>