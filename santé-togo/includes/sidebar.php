<?php
require_once 'includes/auth.php';
?>

<!-- Sidebar -->
<div class="md:w-1/4 lg:w-1/5 xl:w-1/6 hidden md:block">
    <div class="flex flex-col h-full border-r border-gray-200 bg-white">
        <!-- Header with Logo -->
        <div class="flex items-center justify-center h-20 px-4 gradient-bg">
            <div class="flex items-center">
                <i class="fas fa-heartbeat text-3xl text-white mr-2"></i>
                <span class="text-xl font-bold text-white">Santé<span class="text-green-300">Togo</span></span>
            </div>
        </div>
        
        <!-- Profile Section -->
        <div class="flex flex-col items-center px-6 py-8 border-b border-gray-200">
            <div class="relative mb-4">
                <div class="avatar w-16 h-16 rounded-full bg-blue-500 flex items-center justify-center text-white text-2xl">
                    <?php echo strtoupper(substr($_SESSION['prenom'], 0, 1) . substr($_SESSION['nom'], 0, 1)); ?>
                </div>
                <span class="absolute bottom-0 right-0 block h-3 w-3 rounded-full ring-2 ring-white bg-green-500"></span>
            </div>
            <h3 class="text-lg font-semibold text-center"><?php echo htmlspecialchars($_SESSION['prenom'] . ' ' . $_SESSION['nom']); ?></h3>
            <p class="text-sm text-gray-500 text-center"><?php echo htmlspecialchars($_SESSION['email']); ?></p>
            
            <div class="mt-6 w-full">
                <h4 class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">Informations personnelles</h4>
                <ul class="space-y-2">
                    <?php if (isset($_SESSION['telephone']) && !empty($_SESSION['telephone'])): ?>
                        <li class="flex items-center text-sm">
                            <i class="fas fa-phone-alt text-blue-500 mr-3 w-5"></i>
                            <span><?php echo htmlspecialchars($_SESSION['telephone']); ?></span>
                        </li>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['date_naissance']) && !empty($_SESSION['date_naissance'])): ?>
                        <li class="flex items-center text-sm">
                            <i class="fas fa-birthday-cake text-blue-500 mr-3 w-5"></i>
                            <span><?php echo date('d/m/Y', strtotime($_SESSION['date_naissance'])); ?></span>
                        </li>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['groupe_sanguin']) && !empty($_SESSION['groupe_sanguin'])): ?>
                        <li class="flex items-center text-sm">
                            <i class="fas fa-heartbeat text-blue-500 mr-3 w-5"></i>
                            <span>Groupe sanguin: <?php echo htmlspecialchars($_SESSION['groupe_sanguin']); ?></span>
                        </li>
                    <?php else: ?>
                        <li class="flex items-center text-sm text-blue-600">
                            <i class="fas fa-plus-circle text-blue-500 mr-3 w-5"></i>
                            <a href="profile.php" class="hover:underline">Compléter mon profil</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
        
        <!-- Navigation -->
        <nav class="flex-1 px-4 py-4 overflow-y-auto">
            <ul class="space-y-1">
                <li>
                    <a href="dashboard.php" class="flex items-center px-4 py-3 text-sm font-medium text-gray-900 bg-blue-50 rounded-lg border-l-4 border-blue-500">
                        <i class="fas fa-tachometer-alt text-blue-600 mr-3 w-5"></i>
                        <span>Tableau de bord</span>
                        <span class="ml-auto bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">Nouveau</span>
                    </a>
                </li>
                <li>
                    <a href="medical-records.php" class="flex items-center px-4 py-3 text-sm font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg">
                        <i class="fas fa-file-medical text-blue-500 mr-3 w-5"></i>
                        <span>Dossier médical</span>
                    </a>
                </li>
                <li>
                    <a href="appointments.php" class="flex items-center px-4 py-3 text-sm font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg">
                        <i class="fas fa-calendar-check text-blue-500 mr-3 w-5"></i>
                        <span>Rendez-vous</span>
                    </a>
                </li>
                <li>
                    <a href="medications.php" class="flex items-center px-4 py-3 text-sm font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg">
                        <i class="fas fa-pills text-blue-500 mr-3 w-5"></i>
                        <span>Médicaments</span>
                        <span class="ml-auto bg-red-100 text-red-800 text-xs px-2 py-1 rounded-full">3 alertes</span>
                    </a>
                </li>
                <li>
                    <a href="blood-donation.php" class="flex items-center px-4 py-3 text-sm font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg">
                        <i class="fas fa-heart text-blue-500 mr-3 w-5"></i>
                        <span>Don de sang</span>
                    </a>
                </li>
                <li>
                    <a href="teleconsultation.php" class="flex items-center px-4 py-3 text-sm font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg">
                        <i class="fas fa-video text-blue-500 mr-3 w-5"></i>
                        <span>Téléconsultation</span>
                    </a>
                </li>
            </ul>
        </nav>
        
        <!-- Secondary Navigation -->
        <div class="p-3 border-t border-gray-200">
            <ul class="space-y-1">
                <li>
                    <a href="profile.php" class="flex items-center px-4 py-3 text-sm font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg">
                        <i class="fas fa-user-cog text-gray-500 mr-3 w-5"></i>
                        <span>Mon profil</span>
                    </a>
                </li>
                <li>
                    <a href="settings.php" class="flex items-center px-4 py-3 text-sm font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg">
                        <i class="fas fa-cog text-gray-500 mr-3 w-5"></i>
                        <span>Paramètres</span>
                    </a>
                </li>
                <li>
                    <a href="contact.php" class="flex items-center px-4 py-3 text-sm font-medium text-blue-600 hover:text-blue-900 hover:bg-blue-50 rounded-lg">
                        <i class="fas fa-headset text-blue-500 mr-3 w-5"></i>
                        <span>Aide & Support</span>
                    </a>
                </li>
            </ul>
        </div>
        
        <!-- Footer with Logout -->
        <div class="p-2 border-t border-gray-200 mt-auto">
            <a href="logout.php" class="flex items-center px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50 rounded-lg">
                <i class="fas fa-sign-out-alt mr-3"></i>
                Déconnexion
            </a>
        </div>
    </div>
</div>
