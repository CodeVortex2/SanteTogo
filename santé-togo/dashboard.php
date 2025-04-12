<?php
include 'includes/config.php';
include 'includes/auth.php';

if (!is_logged_in()) {
    header("Location: login.php");
    exit();
}

// Récupérer les informations de l'utilisateur
$user_id = $_SESSION['id'];
$sql = "SELECT nom, prenom, email, telephone, date_naissance, groupe_sanguin FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

// Récupérer les prochains rendez-vous
$appointments = [];
$sql_appointments = "SELECT a.id, a.date_rdv, a.heure_rdv, a.statut, m.nom as medecin_nom, m.prenom as medecin_prenom, m.specialite 
                     FROM appointments a 
                     JOIN medecins m ON a.medecin_id = m.id 
                     WHERE a.user_id = ? AND a.date_rdv >= CURDATE() 
                     ORDER BY a.date_rdv, a.heure_rdv LIMIT 3";
$stmt_appointments = mysqli_prepare($conn, $sql_appointments);
mysqli_stmt_bind_param($stmt_appointments, "i", $user_id);
mysqli_stmt_execute($stmt_appointments);
$result_appointments = mysqli_stmt_get_result($stmt_appointments);
while ($row = mysqli_fetch_assoc($result_appointments)) {
    $appointments[] = $row;
}

// Récupérer les prochains médicaments
$medications = [];
$sql_medications = "SELECT id, nom_medicament, dosage, frequence, prochaine_prise 
                    FROM medications 
                    WHERE user_id = ? AND prochaine_prise >= CURDATE() 
                    ORDER BY prochaine_prise LIMIT 3";
$stmt_medications = mysqli_prepare($conn, $sql_medications);
mysqli_stmt_bind_param($stmt_medications, "i", $user_id);
mysqli_stmt_execute($stmt_medications);
$result_medications = mysqli_stmt_get_result($stmt_medications);
while ($row = mysqli_fetch_assoc($result_medications)) {
    $medications[] = $row;
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord - SantéTogo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        
        :root {
            --primary: #2563eb;
            --primary-light: #3b82f6;
            --secondary: #10b981;
            --dark: #1e293b;
            --light: #f8fafc;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
        }
        
        .sidebar {
            transition: all 0.3s ease;
            box-shadow: 4px 0 6px -1px rgba(0, 0, 0, 0.1);
        }
        
        .sidebar-item {
            transition: all 0.2s ease;
            border-left: 3px solid transparent;
        }
        
        .sidebar-item:hover {
            transform: translateX(5px);
            border-left-color: var(--primary);
        }
        
        .active-sidebar-item {
            background-color: #e0f2fe;
            border-left: 3px solid var(--primary);
        }
        
        .card {
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .stat-card {
            border-left: 4px solid;
        }
        
        .stat-card.appointments {
            border-left-color: var(--primary);
        }
        
        .stat-card.medications {
            border-left-color: var(--secondary);
        }
        
        .stat-card.donations {
            border-left-color: #8b5cf6;
        }
        
        .badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
        }
        
        .badge.confirmed {
            background-color: #dcfce7;
            color: #166534;
        }
        
        .badge.pending {
            background-color: #fef9c3;
            color: #854d0e;
        }
        
        .badge.cancelled {
            background-color: #fee2e2;
            color: #991b1b;
        }
        
        .avatar {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .animate-fadein {
            animation: fadeIn 0.5s ease-out forwards;
        }
        
        .delay-100 {
            animation-delay: 0.1s;
        }
        
        .delay-200 {
            animation-delay: 0.2s;
        }
        
        .delay-300 {
            animation-delay: 0.3s;
        }
    </style>
</head>
<body class="text-gray-800">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <div class="sidebar hidden md:flex md:flex-shrink-0">
            <div class="flex flex-col w-64 border-r border-gray-200 bg-white">
                <div class="flex items-center justify-center h-20 px-4 gradient-bg">
                    <div class="flex items-center">
                        <i class="fas fa-heartbeat text-3xl text-white mr-2"></i>
                        <span class="text-xl font-bold text-white">Santé<span class="text-green-300">Togo</span></span>
                    </div>
                </div>
                
                <!-- Profile Section -->
                <div class="flex flex-col items-center px-6 py-8 border-b border-gray-200">
                    <div class="relative mb-4">
                        <div class="avatar w-16 h-16 rounded-full flex items-center justify-center text-white text-2xl">
                            <?php echo strtoupper(substr($user['prenom'], 0, 1) . substr($user['nom'], 0, 1)); ?>
                        </div>
                        <span class="absolute bottom-0 right-0 block h-3 w-3 rounded-full ring-2 ring-white bg-green-500"></span>
                    </div>
                    <h3 class="text-lg font-semibold text-center"><?php echo htmlspecialchars($user['prenom'] . ' ' . $user['nom']); ?></h3>
                    <p class="text-sm text-gray-500 text-center"><?php echo htmlspecialchars($user['email']); ?></p>
                    
                    <div class="mt-6 w-full">
                        <h4 class="text-sm font-medium text-gray-500 mb-2">INFORMATIONS PERSONNELLES</h4>
                        <ul class="space-y-2">
                            <?php if ($user['telephone']): ?>
                                <li class="flex items-center text-sm">
                                    <i class="fas fa-phone-alt text-blue-500 mr-3 w-5"></i>
                                    <span><?php echo htmlspecialchars($user['telephone']); ?></span>
                                </li>
                            <?php endif; ?>
                            
                            <?php if ($user['date_naissance']): ?>
                                <li class="flex items-center text-sm">
                                    <i class="fas fa-birthday-cake text-blue-500 mr-3 w-5"></i>
                                    <span><?php echo date('d/m/Y', strtotime($user['date_naissance'])); ?></span>
                                </li>
                            <?php endif; ?>
                            
                            <?php if ($user['groupe_sanguin']): ?>
                                <li class="flex items-center text-sm">
                                    <i class="fas fa-heartbeat text-blue-500 mr-3 w-5"></i>
                                    <span>Groupe sanguin: <?php echo htmlspecialchars($user['groupe_sanguin']); ?></span>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
                
                <!-- Navigation -->
                <nav class="flex-1 px-4 py-4 overflow-y-auto">
                    <ul class="space-y-1">
                        <li>
                            <a href="dashboard.php" class="flex items-center sidebar-item active-sidebar-item px-4 py-3 text-sm font-medium text-gray-900 rounded-lg">
                                <i class="fas fa-tachometer-alt text-blue-600 mr-3 w-5"></i>
                                <span>Tableau de bord</span>
                            </a>
                        </li>
                        <li>
                            <a href="medical-records.php" class="flex items-center sidebar-item px-4 py-3 text-sm font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg">
                                <i class="fas fa-file-medical text-blue-500 mr-3 w-5"></i>
                                <span>Dossier médical</span>
                            </a>
                        </li>
                        <li>
                            <a href="appointments.php" class="flex items-center sidebar-item px-4 py-3 text-sm font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg">
                                <i class="fas fa-calendar-check text-blue-500 mr-3 w-5"></i>
                                <span>Rendez-vous</span>
                            </a>
                        </li>
                        <li>
                            <a href="medications.php" class="flex items-center sidebar-item px-4 py-3 text-sm font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg">
                                <i class="fas fa-pills text-blue-500 mr-3 w-5"></i>
                                <span>Médicaments</span>
                            </a>
                        </li>
                        <li>
                            <a href="blood-donation.php" class="flex items-center sidebar-item px-4 py-3 text-sm font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg">
                                <i class="fas fa-heart text-blue-500 mr-3 w-5"></i>
                                <span>Don de sang</span>
                            </a>
                        </li>
                        <li>
                            <a href="teleconsultation.php" class="flex items-center sidebar-item px-4 py-3 text-sm font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg">
                                <i class="fas fa-video text-blue-500 mr-3 w-5"></i>
                                <span>Téléconsultation</span>
                            </a>
                        </li>
                    </ul>
                </nav>
                
                <!-- Footer -->
                <div class="p-4 border-t border-gray-200">
                    <a href="/includes/logout.php" class="flex items-center px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50 rounded-lg">
                        <i class="fas fa-sign-out-alt mr-3"></i>
                        Déconnexion
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Mobile header -->
            <header class="md:hidden bg-white shadow-sm">
                <div class="flex items-center justify-between px-4 py-3">
                    <div class="flex items-center">
                        <button id="mobile-menu-button" class="text-gray-500 focus:outline-none">
                            <i class="fas fa-bars text-xl"></i>
                        </button>
                        <div class="ml-4 flex items-center">
                            <i class="fas fa-heartbeat text-2xl text-blue-600 mr-2"></i>
                            <span class="text-xl font-bold text-blue-600">Santé<span class="text-green-500">Togo</span></span>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <div class="relative">
                            <div class="avatar w-10 h-10 rounded-full flex items-center justify-center text-white">
                                <?php echo strtoupper(substr($user['prenom'], 0, 1) . substr($user['nom'], 0, 1)); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- Main content area -->
            <main class="flex-1 overflow-y-auto p-4 md:p-8 bg-gray-50">
                <div class="max-w-7xl mx-auto">
                    <!-- Header -->
                    <div class="flex justify-between items-center mb-8 animate-fadein">
                        <div>
                            <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Bonjour, <?php echo htmlspecialchars($user['prenom']); ?>!</h1>
                            <p class="text-gray-600">Voici votre tableau de bord santé</p>
                        </div>
                        <div class="flex items-center space-x-4">
                            <button class="p-2 text-gray-500 hover:text-gray-700 focus:outline-none relative">
                                <i class="fas fa-bell text-xl"></i>
                                <span class="absolute top-0 right-0 block h-2 w-2 rounded-full bg-red-500 ring-2 ring-white"></span>
                            </button>
                            <button class="p-2 text-gray-500 hover:text-gray-700 focus:outline-none">
                                <i class="fas fa-question-circle text-xl"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Welcome Card -->
                    <div class="gradient-bg rounded-xl shadow-md p-6 mb-8 text-white animate-fadein">
                        <div class="flex flex-col md:flex-row items-center">
                            <div class="flex-1 mb-4 md:mb-0">
                                <h2 class="text-xl font-bold mb-2">Votre santé en un clic</h2>
                                <p class="opacity-90">Gérez facilement tous vos besoins médicaux au quotidien</p>
                            </div>
                            <div class="flex flex-col sm:flex-row gap-3">
                                <a href="appointments.php" class="bg-white/20 hover:bg-white/30 px-4 py-2 rounded-lg font-medium transition text-center">
                                    <i class="fas fa-calendar-plus mr-2"></i> Prendre RDV
                                </a>
                                <a href="medical-records.php" class="bg-white text-blue-600 hover:bg-gray-100 px-4 py-2 rounded-lg font-medium transition text-center">
                                    <i class="fas fa-file-upload mr-2"></i> Ajouter document
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Stats Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        <!-- Appointments Card -->
                        <div class="stat-card appointments bg-white rounded-lg p-6 animate-fadein delay-100">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Prochains RDV</p>
                                    <h3 class="text-2xl font-bold mt-1"><?php echo count($appointments); ?></h3>
                                </div>
                                <div class="bg-blue-100 p-3 rounded-full text-blue-600">
                                    <i class="fas fa-calendar-check text-xl"></i>
                                </div>
                            </div>
                            <a href="appointments.php" class="inline-flex items-center text-blue-600 text-sm font-medium mt-4">
                                Voir tous <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                        
                        <!-- Medications Card -->
                        <div class="stat-card medications bg-white rounded-lg p-6 animate-fadein delay-200">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Médicaments</p>
                                    <h3 class="text-2xl font-bold mt-1"><?php echo count($medications); ?></h3>
                                </div>
                                <div class="bg-green-100 p-3 rounded-full text-green-600">
                                    <i class="fas fa-pills text-xl"></i>
                                </div>
                            </div>
                            <a href="medications.php" class="inline-flex items-center text-green-600 text-sm font-medium mt-4">
                                Voir tous <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                        
                        <!-- Blood Donations Card -->
                        <div class="stat-card donations bg-white rounded-lg p-6 animate-fadein delay-300">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Dons de sang</p>
                                    <h3 class="text-2xl font-bold mt-1">0</h3>
                                </div>
                                <div class="bg-purple-100 p-3 rounded-full text-purple-600">
                                    <i class="fas fa-heart text-xl"></i>
                                </div>
                            </div>
                            <a href="blood-donation.php" class="inline-flex items-center text-purple-600 text-sm font-medium mt-4">
                                Voir tous <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                    </div>
                    
                    <!-- Upcoming Appointments -->
                    <div class="bg-white rounded-xl shadow-md overflow-hidden mb-8 animate-fadein">
                        <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                            <h3 class="text-lg font-semibold">Prochains rendez-vous</h3>
                            <a href="appointments.php" class="text-sm text-blue-600 hover:underline">Voir tous</a>
                        </div>
                        
                        <?php if (empty($appointments)): ?>
                            <div class="p-6 text-center text-gray-500">
                                <i class="fas fa-calendar-times text-4xl mb-3 text-gray-300"></i>
                                <p>Vous n'avez aucun rendez-vous à venir</p>
                                <a href="appointments.php" class="inline-block mt-4 text-blue-600 hover:underline">
                                    <i class="fas fa-plus mr-1"></i> Prendre rendez-vous
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="divide-y divide-gray-200">
                                <?php foreach ($appointments as $appointment): ?>
                                    <div class="p-6 hover:bg-gray-50 transition">
                                        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                                            <div class="mb-4 md:mb-0">
                                                <div class="flex items-center">
                                                    <div class="bg-blue-100 text-blue-800 p-3 rounded-lg mr-4">
                                                        <i class="fas fa-calendar-day text-lg"></i>
                                                    </div>
                                                    <div>
                                                        <h4 class="font-medium"><?php echo date('l d F Y', strtotime($appointment['date_rdv'])); ?></h4>
                                                        <p class="text-sm text-gray-500"><?php echo $appointment['heure_rdv']; ?></p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mb-4 md:mb-0">
                                                <h4 class="font-medium">Dr. <?php echo htmlspecialchars($appointment['medecin_prenom'] . ' ' . $appointment['medecin_nom']); ?></h4>
                                                <p class="text-sm text-gray-500"><?php echo htmlspecialchars($appointment['specialite']); ?></p>
                                            </div>
                                            <div class="flex items-center space-x-3">
                                                <?php if ($appointment['statut'] == 'confirmé'): ?>
                                                    <span class="badge confirmed">Confirmé</span>
                                                <?php elseif ($appointment['statut'] == 'en attente'): ?>
                                                    <span class="badge pending">En attente</span>
                                                <?php else: ?>
                                                    <span class="badge cancelled">Annulé</span>
                                                <?php endif; ?>
                                                <a href="appointments.php?action=view&id=<?php echo $appointment['id']; ?>" class="text-blue-600 hover:text-blue-800">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Medications and Quick Actions -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- Medications -->
                        <div class="bg-white rounded-xl shadow-md overflow-hidden animate-fadein delay-100">
                            <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                                <h3 class="text-lg font-semibold">Médicaments à prendre</h3>
                                <a href="medications.php" class="text-sm text-blue-600 hover:underline">Voir tous</a>
                            </div>
                            
                            <?php if (empty($medications)): ?>
                                <div class="p-6 text-center text-gray-500">
                                    <i class="fas fa-pills text-4xl mb-3 text-gray-300"></i>
                                    <p>Aucun médicament à prendre prochainement</p>
                                    <a href="medications.php" class="inline-block mt-4 text-blue-600 hover:underline">
                                        <i class="fas fa-plus mr-1"></i> Ajouter un médicament
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="divide-y divide-gray-200">
                                    <?php foreach ($medications as $medication): ?>
                                        <div class="p-6 hover:bg-gray-50 transition">
                                            <div class="flex items-start">
                                                <div class="bg-green-100 text-green-800 p-3 rounded-lg mr-4">
                                                    <i class="fas fa-pills text-lg"></i>
                                                </div>
                                                <div class="flex-1">
                                                    <h4 class="font-medium"><?php echo htmlspecialchars($medication['nom_medicament']); ?></h4>
                                                    <p class="text-sm text-gray-500"><?php echo htmlspecialchars($medication['dosage']); ?></p>
                                                    <div class="mt-2 flex items-center text-sm">
                                                        <i class="fas fa-clock text-gray-400 mr-2"></i>
                                                        <span class="text-gray-600">Prochaine prise: <?php echo date('d/m/Y H:i', strtotime($medication['prochaine_prise'])); ?></span>
                                                    </div>
                                                    <div class="mt-1 flex items-center text-sm">
                                                        <i class="fas fa-sync-alt text-gray-400 mr-2"></i>
                                                        <span class="text-gray-600"><?php echo htmlspecialchars($medication['frequence']); ?></span>
                                                    </div>
                                                </div>
                                                <div>
                                                    <a href="medications.php?action=edit&id=<?php echo $medication['id']; ?>" class="text-blue-600 hover:text-blue-800">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Quick Actions -->
                        <div class="space-y-6">
                            <div class="bg-white rounded-xl shadow-md overflow-hidden animate-fadein delay-200">
                                <div class="p-6 border-b border-gray-200">
                                    <h3 class="text-lg font-semibold">Actions rapides</h3>
                                </div>
                                <div class="p-6 grid grid-cols-2 gap-4">
                                    <a href="appointments.php" class="p-4 border border-gray-200 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition text-center">
                                        <div class="bg-blue-100 text-blue-600 p-3 rounded-full inline-block mb-2">
                                            <i class="fas fa-calendar-plus text-lg"></i>
                                        </div>
                                        <p class="font-medium">Prendre RDV</p>
                                    </a>
                                    <a href="medical-records.php" class="p-4 border border-gray-200 rounded-lg hover:border-green-500 hover:bg-green-50 transition text-center">
                                        <div class="bg-green-100 text-green-600 p-3 rounded-full inline-block mb-2">
                                            <i class="fas fa-file-upload text-lg"></i>
                                        </div>
                                        <p class="font-medium">Ajouter document</p>
                                    </a>
                                    <a href="medications.php" class="p-4 border border-gray-200 rounded-lg hover:border-purple-500 hover:bg-purple-50 transition text-center">
                                        <div class="bg-purple-100 text-purple-600 p-3 rounded-full inline-block mb-2">
                                            <i class="fas fa-pills text-lg"></i>
                                        </div>
                                        <p class="font-medium">Ajouter médicament</p>
                                    </a>
                                    <a href="blood-donation.php" class="p-4 border border-gray-200 rounded-lg hover:border-red-500 hover:bg-red-50 transition text-center">
                                        <div class="bg-red-100 text-red-600 p-3 rounded-full inline-block mb-2">
                                            <i class="fas fa-heart text-lg"></i>
                                        </div>
                                        <p class="font-medium">Don de sang</p>
                                    </a>
                                </div>
                            </div>
                            
                            <!-- Blood Requests -->
                            <div class="bg-white rounded-xl shadow-md overflow-hidden animate-fadein delay-300">
                                <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                                    <h3 class="text-lg font-semibold">Demandes de sang urgentes</h3>
                                    <a href="blood-donation.php" class="text-sm text-blue-600 hover:underline">Voir toutes</a>
                                </div>
                                <div class="p-6">
                                    <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-lg mb-4">
                                        <div class="flex">
                                            <div class="flex-shrink-0">
                                                <i class="fas fa-exclamation-triangle text-red-500"></i>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm text-red-700">
                                                    <span class="font-medium">CHU Lomé</span> a besoin de <span class="font-bold">10 poches</span> de sang <span class="font-bold">A+</span> avant le 17/04/2025
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
                                        <div class="flex">
                                            <div class="flex-shrink-0">
                                                <i class="fas fa-exclamation-triangle text-red-500"></i>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm text-red-700">
                                                    <span class="font-medium">Hôpital Baptiste</span> a besoin de <span class="font-bold">5 poches</span> de sang <span class="font-bold">O-</span> avant le 13/04/2025
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <a href="blood-donation.php" class="mt-4 inline-flex items-center text-red-600 text-sm font-medium">
                                        <i class="fas fa-heart mr-1"></i> Je veux aider
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Mobile menu toggle
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileSidebar = document.createElement('div');
        
        mobileMenuButton.addEventListener('click', () => {
            // Créer et afficher le menu mobile
            // (implémentation similaire à la version desktop mais adaptée pour mobile)
        });
        
        // Animations au chargement
        document.addEventListener('DOMContentLoaded', () => {
            const elements = document.querySelectorAll('.animate-fadein');
            elements.forEach(el => {
                el.style.opacity = '0';
            });
            
            setTimeout(() => {
                elements.forEach(el => {
                    el.style.opacity = '1';
                });
            }, 100);
        });
    </script>
</body>
</html>