<?php
include 'includes/config.php';
include 'includes/auth.php';

if (!is_logged_in()) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['id'];
$appointments = [];
$medecins = [];
$error = '';
$success = '';

// Récupérer tous les rendez-vous
$sql = "SELECT a.id, a.date_rdv, a.heure_rdv, a.statut, a.raison, m.nom as medecin_nom, m.prenom as medecin_prenom, m.specialite 
        FROM appointments a 
        JOIN medecins m ON a.medecin_id = m.id 
        WHERE a.user_id = ? 
        ORDER BY a.date_rdv DESC, a.heure_rdv DESC";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($result)) {
    $appointments[] = $row;
}

// Récupérer les médecins disponibles
$sql_medecins = "SELECT id, nom, prenom, specialite FROM medecins ORDER BY nom, prenom";
$result_medecins = mysqli_query($conn, $sql_medecins);
while ($row = mysqli_fetch_assoc($result_medecins)) {
    $medecins[] = $row;
}

// Gérer les actions (annulation)
if (isset($_GET['action']) && $_GET['action'] == 'cancel' && isset($_GET['id'])) {
    $appointment_id = $_GET['id'];
    
    // Vérifier que le rendez-vous appartient bien à l'utilisateur
    $sql_check = "SELECT id FROM appointments WHERE id = ? AND user_id = ?";
    $stmt_check = mysqli_prepare($conn, $sql_check);
    mysqli_stmt_bind_param($stmt_check, "ii", $appointment_id, $user_id);
    mysqli_stmt_execute($stmt_check);
    mysqli_stmt_store_result($stmt_check);
    
    if (mysqli_stmt_num_rows($stmt_check) == 1) {
        // Annuler le rendez-vous
        $sql_cancel = "UPDATE appointments SET statut = 'annulé' WHERE id = ?";
        $stmt_cancel = mysqli_prepare($conn, $sql_cancel);
        mysqli_stmt_bind_param($stmt_cancel, "i", $appointment_id);
        
        if (mysqli_stmt_execute($stmt_cancel)) {
            $success = "Le rendez-vous a été annulé avec succès.";
            header("Location: appointments.php?success=" . urlencode($success));
            exit();
        } else {
            $error = "Erreur lors de l'annulation du rendez-vous.";
        }
    } else {
        $error = "Rendez-vous non trouvé ou vous n'avez pas la permission de l'annuler.";
    }
}

// Ajouter un nouveau rendez-vous
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_appointment'])) {
    $medecin_id = trim($_POST['medecin_id']);
    $date_rdv = trim($_POST['date_rdv']);
    $heure_rdv = trim($_POST['heure_rdv']);
    $raison = trim($_POST['raison']);
    
    if (empty($medecin_id) || empty($date_rdv) || empty($heure_rdv) || empty($raison)) {
        $error = "Veuillez remplir tous les champs.";
    } else {
        // Vérifier la disponibilité du médecin
        $sql_check = "SELECT id FROM appointments WHERE medecin_id = ? AND date_rdv = ? AND heure_rdv = ? AND statut != 'annulé'";
        $stmt_check = mysqli_prepare($conn, $sql_check);
        mysqli_stmt_bind_param($stmt_check, "iss", $medecin_id, $date_rdv, $heure_rdv);
        mysqli_stmt_execute($stmt_check);
        mysqli_stmt_store_result($stmt_check);
        
        if (mysqli_stmt_num_rows($stmt_check) > 0) {
            $error = "Le médecin n'est pas disponible à cette date et heure. Veuillez choisir un autre créneau.";
        } else {
            // Insérer le nouveau rendez-vous
            $sql_insert = "INSERT INTO appointments (user_id, medecin_id, date_rdv, heure_rdv, raison, statut) VALUES (?, ?, ?, ?, ?, 'en attente')";
            $stmt_insert = mysqli_prepare($conn, $sql_insert);
            mysqli_stmt_bind_param($stmt_insert, "iisss", $user_id, $medecin_id, $date_rdv, $heure_rdv, $raison);
            
            if (mysqli_stmt_execute($stmt_insert)) {
                $success = "Rendez-vous pris avec succès. Il est maintenant en attente de confirmation.";
                header("Location: appointments.php?success=" . urlencode($success));
                exit();
            } else {
                $error = "Erreur lors de la prise de rendez-vous.";
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
    <title>Rendez-vous - SantéTogo</title>
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
        
        .appointment-card {
            border-left: 4px solid;
        }
        
        .appointment-card.confirmed {
            border-left-color: #10b981;
        }
        
        .appointment-card.pending {
            border-left-color: #f59e0b;
        }
        
        .appointment-card.cancelled {
            border-left-color: #ef4444;
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
                            <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Rendez-vous médicaux</h1>
                            <p class="text-gray-600">Gérez vos consultations avec les professionnels de santé</p>
                        </div>
                        <button onclick="toggleModal()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-300 flex items-center">
                            <i class="fas fa-plus mr-2"></i> Prendre RDV
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
                    
                    <!-- Stats Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        <div class="bg-white rounded-lg p-6 animate-fadein delay-100">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Total RDV</p>
                                    <h3 class="text-2xl font-bold mt-1"><?php echo count($appointments); ?></h3>
                                </div>
                                <div class="bg-blue-100 p-3 rounded-full text-blue-600">
                                    <i class="fas fa-calendar-alt text-xl"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-white rounded-lg p-6 animate-fadein delay-200">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Confirmés</p>
                                    <h3 class="text-2xl font-bold mt-1">
                                        <?php 
                                            $confirmed = 0;
                                            foreach ($appointments as $appt) {
                                                if ($appt['statut'] == 'confirmé') {
                                                    $confirmed++;
                                                }
                                            }
                                            echo $confirmed;
                                        ?>
                                    </h3>
                                </div>
                                <div class="bg-green-100 p-3 rounded-full text-green-600">
                                    <i class="fas fa-check-circle text-xl"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-white rounded-lg p-6 animate-fadein delay-300">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-500">En attente</p>
                                    <h3 class="text-2xl font-bold mt-1">
                                        <?php 
                                            $pending = 0;
                                            foreach ($appointments as $appt) {
                                                if ($appt['statut'] == 'en attente') {
                                                    $pending++;
                                                }
                                            }
                                            echo $pending;
                                        ?>
                                    </h3>
                                </div>
                                <div class="bg-yellow-100 p-3 rounded-full text-yellow-600">
                                    <i class="fas fa-hourglass-half text-xl"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <?php if (empty($appointments)): ?>
                        <div class="bg-white rounded-xl shadow-md p-8 text-center animate-fadein">
                            <i class="fas fa-calendar-times text-4xl text-gray-400 mb-4"></i>
                            <h3 class="text-xl font-semibold text-gray-700 mb-2">Aucun rendez-vous trouvé</h3>
                            <p class="text-gray-600 mb-6">Prenez votre premier rendez-vous avec un professionnel de santé.</p>
                            <button onclick="toggleModal()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-300 inline-flex items-center">
                                <i class="fas fa-plus mr-2"></i> Prendre RDV
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="bg-white rounded-xl shadow-md overflow-hidden mb-8 animate-fadein">
                            <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                                <h3 class="text-lg font-semibold">Mes rendez-vous</h3>
                                <div class="flex space-x-2">
                                    <button class="text-sm text-blue-600 hover:text-blue-800">Tous</button>
                                    <button class="text-sm text-gray-500 hover:text-gray-800">À venir</button>
                                    <button class="text-sm text-gray-500 hover:text-gray-800">Passés</button>
                                </div>
                            </div>
                            
                            <div class="divide-y divide-gray-200">
                                <?php foreach ($appointments as $appointment): ?>
                                    <div class="p-6 hover:bg-gray-50 transition appointment-card <?php echo $appointment['statut'] == 'confirmé' ? 'confirmed' : ($appointment['statut'] == 'en attente' ? 'pending' : 'cancelled'); ?>">
                                        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                                            <div class="mb-4 md:mb-0">
                                                <div class="flex items-center">
                                                    <div class="bg-blue-100 text-blue-800 p-3 rounded-lg mr-4">
                                                        <i class="fas fa-calendar-day text-lg"></i>
                                                    </div>
                                                    <div>
                                                        <h4 class="font-medium"><?php echo date('l d F Y', strtotime($appointment['date_rdv'])); ?></h4>
                                                        <p class="text-sm text-gray-500"><?php echo $appointment['heure_rdv']; ?></p>
                                                        <div class="mt-2">
                                                            <span class="text-sm text-gray-600">
                                                                <i class="fas fa-user-md mr-1"></i> <?php echo htmlspecialchars($appointment['raison']); ?>
                                                            </span>
                                                        </div>
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
                                                
                                                <div class="relative group">
                                                    <button class="text-gray-500 hover:text-gray-700 focus:outline-none p-2">
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </button>
                                                    <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10 hidden group-hover:block">
                                                        <div class="py-1">
                                                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><i class="fas fa-info-circle mr-2"></i> Détails</a>
                                                            <?php if ($appointment['statut'] == 'en attente' || $appointment['statut'] == 'confirmé'): ?>
                                                                <a href="appointments.php?action=cancel&id=<?php echo $appointment['id']; ?>" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100"><i class="fas fa-times mr-2"></i> Annuler</a>
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
                    
                    <!-- Doctors List -->
                    <div class="bg-white rounded-xl shadow-md p-6 animate-fadein">
                        <h3 class="text-lg font-semibold mb-4">Nos spécialistes</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <?php 
                            $specialties = [];
                            foreach ($medecins as $medecin) {
                                if (!isset($specialties[$medecin['specialite']])) {
                                    $specialties[$medecin['specialite']] = [];
                                }
                                $specialties[$medecin['specialite']][] = $medecin;
                            }
                            
                            foreach ($specialties as $specialty => $doctors): 
                                $randomDoctor = $doctors[array_rand($doctors)];
                            ?>
                                <div class="border rounded-lg p-4 hover:shadow-md transition">
                                    <div class="flex items-center mb-3">
                                        <div class="bg-blue-100 text-blue-600 p-2 rounded-full mr-3">
                                            <i class="fas fa-user-md"></i>
                                        </div>
                                        <h4 class="font-medium"><?php echo htmlspecialchars($specialty); ?></h4>
                                    </div>
                                    <p class="text-sm text-gray-600 mb-3"><?php echo count($doctors); ?> médecin(s) disponible(s)</p>
                                    <button onclick="toggleModalWithSpecialty('<?php echo htmlspecialchars($specialty); ?>')" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-300 text-sm">
                                        Prendre RDV avec un <?php echo htmlspecialchars($specialty); ?>
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Add Appointment Modal -->
    <div id="addAppointmentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 lg:w-1/3 shadow-lg rounded-md bg-white animate-fadein">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-semibold">Prendre un rendez-vous</h3>
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
                            <option value="<?php echo $medecin['id']; ?>" data-specialty="<?php echo htmlspecialchars($medecin['specialite']); ?>">
                                Dr. <?php echo htmlspecialchars($medecin['prenom'] . ' ' . $medecin['nom'] . ' - ' . $medecin['specialite']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="date_rdv" class="block text-gray-700 font-medium mb-2">Date*</label>
                        <input type="date" id="date_rdv" name="date_rdv" min="<?php echo date('Y-m-d'); ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    <div>
                        <label for="heure_rdv" class="block text-gray-700 font-medium mb-2">Heure*</label>
                        <select id="heure_rdv" name="heure_rdv" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            <option value="">Sélectionner une heure</option>
                            <?php
                            // Générer les heures de rendez-vous (8h-17h par exemple)
                            for ($h = 8; $h <= 17; $h++) {
                                echo '<option value="' . str_pad($h, 2, '0', STR_PAD_LEFT) . ':00">' . str_pad($h, 2, '0', STR_PAD_LEFT) . ':00</option>';
                                if ($h < 17) {
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
                    <button type="submit" name="add_appointment" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-300 flex items-center">
                        <i class="fas fa-calendar-check mr-2"></i> Prendre RDV
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleModal() {
            const modal = document.getElementById('addAppointmentModal');
            modal.classList.toggle('hidden');
        }
        
        function toggleModalWithSpecialty(specialty) {
            const modal = document.getElementById('addAppointmentModal');
            const doctorSelect = document.getElementById('medecin_id');
            
            // Ouvrir la modal
            modal.classList.toggle('hidden');
            
            // Filtrer les médecins par spécialité
            for (let i = 0; i < doctorSelect.options.length; i++) {
                if (doctorSelect.options[i].dataset.specialty === specialty) {
                    doctorSelect.selectedIndex = i;
                    break;
                }
            }
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