<?php
include 'includes/config.php';
include 'includes/auth.php';

if (!is_logged_in()) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['id'];
$consultations = [];
$medecins = [];
$error = '';
$success = '';

// Récupérer les téléconsultations
$sql = "SELECT c.id, c.date_consultation, c.heure, c.statut, c.lien, m.nom as medecin_nom, m.prenom as medecin_prenom, m.specialite 
        FROM teleconsultations c 
        JOIN medecins m ON c.medecin_id = m.id 
        WHERE c.user_id = ? 
        ORDER BY c.date_consultation DESC, c.heure DESC";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($result)) {
    $consultations[] = $row;
}

// Récupérer les médecins disponibles pour téléconsultation
$sql_medecins = "SELECT id, nom, prenom, specialite FROM medecins WHERE teleconsultation = 1 ORDER BY nom, prenom";
$result_medecins = mysqli_query($conn, $sql_medecins);
while ($row = mysqli_fetch_assoc($result_medecins)) {
    $medecins[] = $row;
}

// Demander une nouvelle téléconsultation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['request_consultation'])) {
    $medecin_id = trim($_POST['medecin_id']);
    $date_consultation = trim($_POST['date_consultation']);
    $heure = trim($_POST['heure']);
    $raison = trim($_POST['raison']);
    
    if (empty($medecin_id) || empty($date_consultation) || empty($heure) || empty($raison)) {
        $error = "Veuillez remplir tous les champs.";
    } else {
        // Vérifier la disponibilité du médecin
        $sql_check = "SELECT id FROM teleconsultations WHERE medecin_id = ? AND date_consultation = ? AND heure = ? AND statut != 'annulée'";
        $stmt_check = mysqli_prepare($conn, $sql_check);
        mysqli_stmt_bind_param($stmt_check, "iss", $medecin_id, $date_consultation, $heure);
        mysqli_stmt_execute($stmt_check);
        mysqli_stmt_store_result($stmt_check);
        
        if (mysqli_stmt_num_rows($stmt_check) > 0) {
            $error = "Le médecin n'est pas disponible à cette date et heure. Veuillez choisir un autre créneau.";
        } else {
            // Générer un lien unique pour la consultation
            $lien = "https://meet.sante-togo.tg/" . bin2hex(random_bytes(8));
            
            // Insérer la nouvelle téléconsultation
            $sql_insert = "INSERT INTO teleconsultations (user_id, medecin_id, date_consultation, heure, raison, lien, statut) 
                        VALUES (?, ?, ?, ?, ?, ?, 'en attente')";
            $stmt_insert = mysqli_prepare($conn, $sql_insert);
            mysqli_stmt_bind_param($stmt_insert, "iissss", $user_id, $medecin_id, $date_consultation, $heure, $raison, $lien);
            
            if (mysqli_stmt_execute($stmt_insert)) {
                $success = "Demande de téléconsultation envoyée avec succès. Vous recevrez une confirmation par email.";
                header("Location: teleconsultation.php?success=" . urlencode($success));
                exit();
            } else {
                $error = "Erreur lors de la demande de téléconsultation.";
            }
        }
    }
}

mysqli_close($conn);

// Afficher les messages de succès/erreur
if (isset($_GET['success'])) {
    $success = $_GET['success'];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Téléconsultation - SantéTogo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Utilisez les mêmes styles que dans dashboard.php */
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
        
        .card {
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
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
        
        .badge.completed {
            background-color: #e0f2fe;
            color: #075985;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .animate-fadein {
            animation: fadeIn 0.5s ease-out forwards;
        }
    </style>
</head>
<body class="text-gray-800">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            
            <!-- Main content area -->
            <main class="flex-1 overflow-y-auto p-4 md:p-8 bg-gray-50">
                <div class="max-w-7xl mx-auto">
                    <!-- Header -->
                    <div class="flex justify-between items-center mb-8 animate-fadein">
                        <div>
                            <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Téléconsultation</h1>
                            <p class="text-gray-600">Consultez un médecin à distance</p>
                        </div>
                        <button onclick="toggleModal()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-300 flex items-center">
                            <i class="fas fa-video mr-2"></i> Nouvelle consultation
                        </button>
                    </div>
                    
                    <?php if (!empty($error)): ?>
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6 animate-fadein">
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($success)): ?>
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6 animate-fadein">
                            <?php echo $success; ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Info Card -->
                    <div class="gradient-bg rounded-xl shadow-md p-6 mb-8 text-white animate-fadein">
                        <div class="flex flex-col md:flex-row items-center">
                            <div class="flex-1 mb-4 md:mb-0">
                                <h2 class="text-xl font-bold mb-2">Consultation médicale en ligne</h2>
                                <p class="opacity-90">Obtenez des soins médicaux de qualité sans vous déplacer</p>
                            </div>
                            <div class="flex items-center space-x-3">
                                <i class="fas fa-laptop-medical text-3xl opacity-80"></i>
                                <i class="fas fa-user-md text-3xl opacity-80"></i>
                                <i class="fas fa-stethoscope text-3xl opacity-80"></i>
                            </div>
                        </div>
                    </div>
                    
                    <?php if (empty($consultations)): ?>
                        <div class="bg-white rounded-xl shadow-md p-8 text-center animate-fadein">
                            <i class="fas fa-video-slash text-4xl text-gray-400 mb-4"></i>
                            <h3 class="text-xl font-semibold text-gray-700 mb-2">Aucune téléconsultation planifiée</h3>
                            <p class="text-gray-600 mb-6">Demandez une consultation en ligne avec un professionnel de santé.</p>
                            <button onclick="toggleModal()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-300 inline-flex items-center">
                                <i class="fas fa-video mr-2"></i> Demander une consultation
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="bg-white rounded-xl shadow-md overflow-hidden mb-8 animate-fadein">
                            <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                                <h3 class="text-lg font-semibold">Mes téléconsultations</h3>
                                <span class="text-sm text-gray-500"><?php echo count($consultations); ?> consultation(s)</span>
                            </div>
                            
                            <div class="divide-y divide-gray-200">
                                <?php foreach ($consultations as $consultation): ?>
                                    <div class="p-6 hover:bg-gray-50 transition">
                                        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                                            <div class="mb-4 md:mb-0">
                                                <div class="flex items-center">
                                                    <div class="bg-blue-100 text-blue-800 p-3 rounded-lg mr-4">
                                                        <i class="fas fa-calendar-day text-lg"></i>
                                                    </div>
                                                    <div>
                                                        <h4 class="font-medium"><?php echo date('l d F Y', strtotime($consultation['date_consultation'])); ?></h4>
                                                        <p class="text-sm text-gray-500"><?php echo $consultation['heure']; ?></p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mb-4 md:mb-0">
                                                <h4 class="font-medium">Dr. <?php echo htmlspecialchars($consultation['medecin_prenom'] . ' ' . $consultation['medecin_nom']); ?></h4>
                                                <p class="text-sm text-gray-500"><?php echo htmlspecialchars($consultation['specialite']); ?></p>
                                            </div>
                                            <div class="flex items-center space-x-3">
                                                <?php if ($consultation['statut'] == 'confirmée'): ?>
                                                    <span class="badge confirmed">Confirmée</span>
                                                <?php elseif ($consultation['statut'] == 'en attente'): ?>
                                                    <span class="badge pending">En attente</span>
                                                <?php elseif ($consultation['statut'] == 'annulée'): ?>
                                                    <span class="badge cancelled">Annulée</span>
                                                <?php else: ?>
                                                    <span class="badge completed">Terminée</span>
                                                <?php endif; ?>
                                                
                                                <?php if ($consultation['statut'] == 'confirmée'): ?>
                                                    <a href="<?php echo htmlspecialchars($consultation['lien']); ?>" target="_blank" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-3 rounded-lg text-sm transition duration-300">
                                                        <i class="fas fa-video mr-1"></i> Rejoindre
                                                    </a>
                                                <?php endif; ?>
                                                
                                                <div class="relative group">
                                                    <button class="text-gray-500 hover:text-gray-700 focus:outline-none">
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </button>
                                                    <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10 hidden group-hover:block">
                                                        <div class="py-1">
                                                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Détails</a>
                                                            <?php if ($consultation['statut'] == 'en attente' || $consultation['statut'] == 'confirmée'): ?>
                                                                <a href="#" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100">Annuler</a>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- How It Works Section -->
                    <div class="bg-white rounded-xl shadow-md p-6 mb-8 animate-fadein">
                        <h3 class="text-lg font-semibold mb-4">Comment fonctionne la téléconsultation ?</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <div class="bg-blue-100 text-blue-600 p-3 rounded-full inline-block mb-3">
                                    <i class="fas fa-calendar-check"></i>
                                </div>
                                <h4 class="font-medium mb-2">1. Prenez rendez-vous</h4>
                                <p class="text-sm text-gray-600">Choisissez un créneau qui vous convient avec un médecin disponible.</p>
                            </div>
                            <div class="bg-green-50 p-4 rounded-lg">
                                <div class="bg-green-100 text-green-600 p-3 rounded-full inline-block mb-3">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <h4 class="font-medium mb-2">2. Recevez confirmation</h4>
                                <p class="text-sm text-gray-600">Vous recevrez un email avec le lien de consultation une fois confirmé.</p>
                            </div>
                            <div class="bg-purple-50 p-4 rounded-lg">
                                <div class="bg-purple-100 text-purple-600 p-3 rounded-full inline-block mb-3">
                                    <i class="fas fa-video"></i>
                                </div>
                                <h4 class="font-medium mb-2">3. Consultez en ligne</h4>
                                <p class="text-sm text-gray-600">Connectez-vous au moment du rendez-vous pour votre consultation vidéo.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Request Consultation Modal -->
    <div id="requestConsultationModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 lg:w-1/3 shadow-lg rounded-md bg-white animate-fadein">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-semibold">Nouvelle téléconsultation</h3>
                <button onclick="toggleModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="mb-4">
                    <label for="medecin_id" class="block text-gray-700 font-medium mb-2">Médecin*</label>
                    <select id="medecin_id" name="medecin_id" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <option value="">Sélectionner un médecin</option>
                        <?php foreach ($medecins as $medecin): ?>
                            <option value="<?php echo $medecin['id']; ?>">Dr. <?php echo htmlspecialchars($medecin['prenom'] . ' ' . $medecin['nom'] . ' - ' . $medecin['specialite']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="date_consultation" class="block text-gray-700 font-medium mb-2">Date*</label>
                        <input type="date" id="date_consultation" name="date_consultation" min="<?php echo date('Y-m-d'); ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    <div>
                        <label for="heure" class="block text-gray-700 font-medium mb-2">Heure*</label>
                        <select id="heure" name="heure" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            <option value="">Sélectionner une heure</option>
                            <?php
                            // Générer les heures de consultation (8h-18h)
                            for ($h = 8; $h <= 18; $h++) {
                                echo '<option value="' . str_pad($h, 2, '0', STR_PAD_LEFT) . ':00">' . str_pad($h, 2, '0', STR_PAD_LEFT) . ':00</option>';
                                if ($h < 18) {
                                    echo '<option value="' . str_pad($h, 2, '0', STR_PAD_LEFT) . ':30">' . str_pad($h, 2, '0', STR_PAD_LEFT) . ':30</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="raison" class="block text-gray-700 font-medium mb-2">Motif de consultation*</label>
                    <textarea id="raison" name="raison" rows="3" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Décrivez brièvement la raison de votre consultation" required></textarea>
                </div>
                
                <div class="flex justify-end pt-4 border-t border-gray-200">
                    <button type="button" onclick="toggleModal()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-lg mr-2 transition duration-300">Annuler</button>
                    <button type="submit" name="request_consultation" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-300 flex items-center">
                        <i class="fas fa-paper-plane mr-2"></i> Envoyer la demande
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleModal() {
            const modal = document.getElementById('requestConsultationModal');
            modal.classList.toggle('hidden');
        }
        
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