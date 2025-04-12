<?php
include 'includes/config.php';
include 'includes/auth.php';

if (!is_logged_in()) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['id'];
$user_group = '';
$donations = [];
$requests = [];
$error = '';
$success = '';

// Récupérer le groupe sanguin de l'utilisateur
$sql_user = "SELECT groupe_sanguin FROM users WHERE id = ?";
$stmt_user = mysqli_prepare($conn, $sql_user);
mysqli_stmt_bind_param($stmt_user, "i", $user_id);
mysqli_stmt_execute($stmt_user);
mysqli_stmt_bind_result($stmt_user, $user_group);
mysqli_stmt_fetch($stmt_user);
mysqli_stmt_close($stmt_user);

// Récupérer les dons de l'utilisateur
$sql_donations = "SELECT id, date_don, lieu, statut FROM blood_donations WHERE user_id = ? ORDER BY date_don DESC";
$stmt_donations = mysqli_prepare($conn, $sql_donations);
mysqli_stmt_bind_param($stmt_donations, "i", $user_id);
mysqli_stmt_execute($stmt_donations);
$result_donations = mysqli_stmt_get_result($stmt_donations);
while ($row = mysqli_fetch_assoc($result_donations)) {
    $donations[] = $row;
}

// Récupérer les demandes de sang correspondant au groupe de l'utilisateur
if (!empty($user_group)) {
    $sql_requests = "SELECT id, hopital, quantite, date_limite, contact FROM blood_requests 
                     WHERE groupe_sanguin = ? AND date_limite >= CURDATE() 
                     ORDER BY date_limite ASC";
    $stmt_requests = mysqli_prepare($conn, $sql_requests);
    mysqli_stmt_bind_param($stmt_requests, "s", $user_group);
    mysqli_stmt_execute($stmt_requests);
    $result_requests = mysqli_stmt_get_result($stmt_requests);
    while ($row = mysqli_fetch_assoc($result_requests)) {
        $requests[] = $row;
    }
}

// Enregistrer un nouveau don
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_donation'])) {
    $date_don = trim($_POST['date_don']);
    $lieu = trim($_POST['lieu']);
    
    if (empty($date_don) || empty($lieu)) {
        $error = "Veuillez remplir tous les champs.";
    } else {
        $sql = "INSERT INTO blood_donations (user_id, date_don, lieu, statut) VALUES (?, ?, ?, 'planifié')";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "iss", $user_id, $date_don, $lieu);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = "Votre don de sang a été planifié avec succès. Merci pour votre générosité!";
            header("Location: blood-donation.php?success=" . urlencode($success));
            exit();
        } else {
            $error = "Erreur lors de l'enregistrement du don de sang.";
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
    <title>Dossier Médical - SantéTogo</title>
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
        
        .document-card {
            border-left: 4px solid var(--primary);
        }
        
        .document-type {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
        }
        
        .document-type.prescription {
            background-color: #dbeafe;
            color: #1d4ed8;
        }
        
        .document-type.report {
            background-color: #dcfce7;
            color: #166534;
        }
        
        .document-type.scan {
            background-color: #fef3c7;
            color: #92400e;
        }
        
        .document-type.other {
            background-color: #e0e7ff;
            color: #4338ca;
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
                            <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Dossier Médical</h1>
                            <p class="text-gray-600">Tous vos documents médicaux en un seul endroit</p>
                        </div>
                        <button onclick="toggleModal()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-300 flex items-center">
                            <i class="fas fa-plus mr-2"></i> Ajouter un document
                        </button>
                    </div>
                    
                    <?php if (!empty($error)): ?>
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6 animate-fadein">
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Info Card -->
                    <div class="gradient-bg rounded-xl shadow-md p-6 mb-8 text-white animate-fadein">
                        <div class="flex flex-col md:flex-row items-center">
                            <div class="flex-1 mb-4 md:mb-0">
                                <h2 class="text-xl font-bold mb-2">Votre historique médical sécurisé</h2>
                                <p class="opacity-90">Conservez tous vos documents importants pour un suivi optimal</p>
                            </div>
                            <div class="flex items-center space-x-3">
                                <i class="fas fa-file-medical text-3xl opacity-80"></i>
                                <i class="fas fa-lock text-3xl opacity-80"></i>
                            </div>
                        </div>
                    </div>
                    
                    <?php if (empty($records)): ?>
                        <div class="bg-white rounded-xl shadow-md p-8 text-center animate-fadein">
                            <i class="fas fa-file-medical text-4xl text-gray-400 mb-4"></i>
                            <h3 class="text-xl font-semibold text-gray-700 mb-2">Aucun document médical enregistré</h3>
                            <p class="text-gray-600 mb-6">Commencez par ajouter votre premier document médical.</p>
                            <button onclick="toggleModal()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-300 inline-flex items-center">
                                <i class="fas fa-plus mr-2"></i> Ajouter un document
                            </button>
                        </div>
                    <?php else: ?>
                        <!-- Filters -->
                        <div class="bg-white rounded-xl shadow-md p-4 mb-6 animate-fadein">
                            <div class="flex flex-wrap items-center justify-between">
                                <div class="flex space-x-2 mb-2 md:mb-0">
                                    <button class="bg-blue-600 text-white px-3 py-1 rounded-full text-sm">Tous</button>
                                    <button class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-3 py-1 rounded-full text-sm">Ordonnances</button>
                                    <button class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-3 py-1 rounded-full text-sm">Comptes-rendus</button>
                                    <button class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-3 py-1 rounded-full text-sm">Analyses</button>
                                </div>
                                <div class="relative">
                                    <input type="text" placeholder="Rechercher..." class="pl-8 pr-4 py-1 border rounded-full text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <i class="fas fa-search absolute left-3 top-2 text-gray-400"></i>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Documents Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 animate-fadein">
                            <?php foreach ($records as $record): ?>
                                <div class="bg-white rounded-xl shadow-md overflow-hidden card document-card hover:shadow-lg">
                                    <div class="p-6">
                                        <div class="flex items-start mb-4">
                                            <div class="document-type <?php echo getDocumentTypeClass($record['titre']); ?> mr-4">
                                                <i class="<?php echo getDocumentIcon($record['titre']); ?>"></i>
                                            </div>
                                            <div>
                                                <h3 class="text-lg font-semibold text-gray-800"><?php echo htmlspecialchars($record['titre']); ?></h3>
                                                <p class="text-sm text-gray-500"><?php echo date('d/m/Y', strtotime($record['date_creation'])); ?></p>
                                            </div>
                                        </div>
                                        <p class="text-gray-700 mb-4"><?php echo nl2br(htmlspecialchars(substr($record['description'], 0, 100) . (strlen($record['description']) > 100 ? '...' : ''))); ?></p>
                                        <div class="flex justify-between items-center pt-3 border-t border-gray-200">
                                            <a href="medical-record-detail.php?id=<?php echo $record['id']; ?>" class="text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center">
                                                <i class="fas fa-eye mr-1"></i> Voir plus
                                            </a>
                                            <div class="flex space-x-2">
                                                <a href="#" class="text-gray-500 hover:text-blue-600">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                                <a href="#" class="text-gray-500 hover:text-blue-600">
                                                    <i class="fas fa-share-alt"></i>
                                                </a>
                                                <a href="#" class="text-gray-500 hover:text-red-600">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Document Types Info -->
                    <div class="bg-white rounded-xl shadow-md p-6 mt-8 animate-fadein">
                        <h3 class="text-lg font-semibold mb-4">Types de documents à archiver</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div class="flex items-center p-3 bg-blue-50 rounded-lg">
                                <div class="document-type prescription mr-3">
                                    <i class="fas fa-prescription-bottle-alt"></i>
                                </div>
                                <div>
                                    <h4 class="font-medium">Ordonnances</h4>
                                    <p class="text-xs text-gray-600">Médicaments prescrits</p>
                                </div>
                            </div>
                            <div class="flex items-center p-3 bg-green-50 rounded-lg">
                                <div class="document-type report mr-3">
                                    <i class="fas fa-file-medical"></i>
                                </div>
                                <div>
                                    <h4 class="font-medium">Comptes-rendus</h4>
                                    <p class="text-xs text-gray-600">Consultations, opérations</p>
                                </div>
                            </div>
                            <div class="flex items-center p-3 bg-yellow-50 rounded-lg">
                                <div class="document-type scan mr-3">
                                    <i class="fas fa-x-ray"></i>
                                </div>
                                <div>
                                    <h4 class="font-medium">Radiologies</h4>
                                    <p class="text-xs text-gray-600">Scanners, IRM, radios</p>
                                </div>
                            </div>
                            <div class="flex items-center p-3 bg-indigo-50 rounded-lg">
                                <div class="document-type other mr-3">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                                <div>
                                    <h4 class="font-medium">Autres</h4>
                                    <p class="text-xs text-gray-600">Certificats, résultats</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Add Record Modal -->
    <div id="addRecordModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 lg:w-1/3 shadow-lg rounded-md bg-white animate-fadein">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-semibold">Ajouter un document médical</h3>
                <button onclick="toggleModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
                <div class="mb-4">
                    <label for="titre" class="block text-gray-700 font-medium mb-2">Titre*</label>
                    <input type="text" id="titre" name="titre" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                
                <div class="mb-4">
                    <label for="type" class="block text-gray-700 font-medium mb-2">Type de document*</label>
                    <select id="type" name="type" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <option value="">Sélectionner un type</option>
                        <option value="prescription">Ordonnance</option>
                        <option value="report">Compte-rendu</option>
                        <option value="scan">Radiologie/Scan</option>
                        <option value="analysis">Analyse médicale</option>
                        <option value="other">Autre</option>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label for="date_creation" class="block text-gray-700 font-medium mb-2">Date du document</label>
                    <input type="date" id="date_creation" name="date_creation" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div class="mb-4">
                    <label for="description" class="block text-gray-700 font-medium mb-2">Description*</label>
                    <textarea id="description" name="description" rows="3" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required></textarea>
                </div>
                
                <div class="mb-4">
                    <label for="document" class="block text-gray-700 font-medium mb-2">Fichier (optionnel)</label>
                    <div class="flex items-center justify-center w-full">
                        <label for="document" class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                                <p class="mb-2 text-sm text-gray-500">Glissez-déposez ou cliquez pour uploader</p>
                                <p class="text-xs text-gray-500">PDF, JPG, PNG (max. 5MB)</p>
                            </div>
                            <input id="document" name="document" type="file" class="hidden" />
                        </label>
                    </div> 
                </div>
                
                <div class="flex justify-end pt-4 border-t border-gray-200">
                    <button type="button" onclick="toggleModal()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-lg mr-2 transition duration-300">Annuler</button>
                    <button type="submit" name="add_record" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-300 flex items-center">
                        <i class="fas fa-save mr-2"></i> Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleModal() {
            const modal = document.getElementById('addRecordModal');
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

<?php
// Fonctions helpers pour déterminer le type de document
function getDocumentTypeClass($title) {
    $title = strtolower($title);
    if (strpos($title, 'ordonnance') !== false || strpos($title, 'prescription') !== false) {
        return 'prescription';
    } elseif (strpos($title, 'compte-rendu') !== false || strpos($title, 'cr ') !== false) {
        return 'report';
    } elseif (strpos($title, 'radio') !== false || strpos($title, 'scanner') !== false || strpos($title, 'irm') !== false) {
        return 'scan';
    } else {
        return 'other';
    }
}

function getDocumentIcon($title) {
    $type = getDocumentTypeClass($title);
    switch ($type) {
        case 'prescription': return 'fas fa-prescription-bottle-alt';
        case 'report': return 'fas fa-file-medical';
        case 'scan': return 'fas fa-x-ray';
        default: return 'fas fa-file-alt';
    }
}
?>