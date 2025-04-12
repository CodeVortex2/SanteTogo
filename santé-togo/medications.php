<?php
include 'includes/config.php';
include 'includes/auth.php';

if (!is_logged_in()) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['id'];
$medications = [];
$error = '';
$success = '';

// Récupérer tous les médicaments
$sql = "SELECT id, nom_medicament, dosage, frequence, prochaine_prise, notes FROM medications WHERE user_id = ? ORDER BY prochaine_prise";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($result)) {
    $medications[] = $row;
}

// Ajouter un nouveau médicament
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_medication'])) {
    $nom_medicament = trim($_POST['nom_medicament']);
    $dosage = trim($_POST['dosage']);
    $frequence = trim($_POST['frequence']);
    $prochaine_prise = trim($_POST['prochaine_prise']);
    $notes = trim($_POST['notes']);
    
    if (empty($nom_medicament) || empty($dosage) || empty($frequence) || empty($prochaine_prise)) {
        $error = "Veuillez remplir tous les champs obligatoires.";
    } else {
        $sql = "INSERT INTO medications (user_id, nom_medicament, dosage, frequence, prochaine_prise, notes) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "isssss", $user_id, $nom_medicament, $dosage, $frequence, $prochaine_prise, $notes);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = "Médicament ajouté avec succès. Les rappels ont été configurés.";
            header("Location: medications.php?success=" . urlencode($success));
            exit();
        } else {
            $error = "Erreur lors de l'ajout du médicament.";
        }
    }
}

// Marquer un médicament comme pris
if (isset($_GET['action']) && $_GET['action'] == 'taken' && isset($_GET['id'])) {
    $medication_id = $_GET['id'];
    
    // Vérifier que le médicament appartient bien à l'utilisateur
    $sql_check = "SELECT id, frequence FROM medications WHERE id = ? AND user_id = ?";
    $stmt_check = mysqli_prepare($conn, $sql_check);
    mysqli_stmt_bind_param($stmt_check, "ii", $medication_id, $user_id);
    mysqli_stmt_execute($stmt_check);
    mysqli_stmt_store_result($stmt_check);
    
    if (mysqli_stmt_num_rows($stmt_check) == 1) {
        mysqli_stmt_bind_result($stmt_check, $id, $frequence);
        mysqli_stmt_fetch($stmt_check);
        
        // Calculer la prochaine prise en fonction de la fréquence
        $next_date = date('Y-m-d H:i:s');
        switch ($frequence) {
            case 'quotidien':
                $next_date = date('Y-m-d H:i:s', strtotime('+1 day'));
                break;
            case 'hebdomadaire':
                $next_date = date('Y-m-d H:i:s', strtotime('+1 week'));
                break;
            case 'mensuel':
                $next_date = date('Y-m-d H:i:s', strtotime('+1 month'));
                break;
            case '2 fois/jour':
                $next_date = date('Y-m-d H:i:s', strtotime('+12 hours'));
                break;
            case '3 fois/jour':
                $next_date = date('Y-m-d H:i:s', strtotime('+8 hours'));
                break;
        }
        
        // Mettre à jour la date de prochaine prise
        $sql_update = "UPDATE medications SET prochaine_prise = ? WHERE id = ?";
        $stmt_update = mysqli_prepare($conn, $sql_update);
        mysqli_stmt_bind_param($stmt_update, "si", $next_date, $medication_id);
        
        if (mysqli_stmt_execute($stmt_update)) {
            $success = "Médicament marqué comme pris. Prochain rappel programmé.";
            header("Location: medications.php?success=" . urlencode($success));
            exit();
        } else {
            $error = "Erreur lors de la mise à jour du médicament.";
        }
    } else {
        $error = "Médicament non trouvé ou vous n'avez pas la permission de le modifier.";
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
    <title>Médicaments - SantéTogo</title>
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
        
        .badge.urgent {
            background-color: #fee2e2;
            color: #991b1b;
        }
        
        .badge.upcoming {
            background-color: #fef9c3;
            color: #854d0e;
        }
        
        .badge.taken {
            background-color: #dcfce7;
            color: #166534;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .animate-fadein {
            animation: fadeIn 0.5s ease-out forwards;
        }
        
        .medication-card {
            border-left: 4px solid;
        }
        
        .medication-card.urgent {
            border-left-color: #ef4444;
        }
        
        .medication-card.upcoming {
            border-left-color: #f59e0b;
        }
        
        .medication-card.taken {
            border-left-color: #10b981;
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
                            <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Gestion des médicaments</h1>
                            <p class="text-gray-600">Suivez vos traitements et recevez des rappels</p>
                        </div>
                        <button onclick="toggleModal()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-300 flex items-center">
                            <i class="fas fa-plus mr-2"></i> Ajouter un médicament
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
                                    <p class="text-sm font-medium text-gray-500">Médicaments en cours</p>
                                    <h3 class="text-2xl font-bold mt-1"><?php echo count($medications); ?></h3>
                                </div>
                                <div class="bg-blue-100 p-3 rounded-full text-blue-600">
                                    <i class="fas fa-pills text-xl"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-white rounded-lg p-6 animate-fadein delay-200">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-500">À prendre aujourd'hui</p>
                                    <h3 class="text-2xl font-bold mt-1">
                                        <?php 
                                            $today = 0;
                                            foreach ($medications as $med) {
                                                if (date('Y-m-d', strtotime($med['prochaine_prise'])) == date('Y-m-d')) {
                                                    $today++;
                                                }
                                            }
                                            echo $today;
                                        ?>
                                    </h3>
                                </div>
                                <div class="bg-yellow-100 p-3 rounded-full text-yellow-600">
                                    <i class="fas fa-bell text-xl"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-white rounded-lg p-6 animate-fadein delay-300">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-500">En retard</p>
                                    <h3 class="text-2xl font-bold mt-1">
                                        <?php 
                                            $late = 0;
                                            foreach ($medications as $med) {
                                                if (strtotime($med['prochaine_prise']) < time()) {
                                                    $late++;
                                                }
                                            }
                                            echo $late;
                                        ?>
                                    </h3>
                                </div>
                                <div class="bg-red-100 p-3 rounded-full text-red-600">
                                    <i class="fas fa-exclamation-triangle text-xl"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <?php if (empty($medications)): ?>
                        <div class="bg-white rounded-xl shadow-md p-8 text-center animate-fadein">
                            <i class="fas fa-pills text-4xl text-gray-400 mb-4"></i>
                            <h3 class="text-xl font-semibold text-gray-700 mb-2">Aucun médicament enregistré</h3>
                            <p class="text-gray-600 mb-6">Ajoutez vos médicaments pour configurer des rappels de prise.</p>
                            <button onclick="toggleModal()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-300 inline-flex items-center">
                                <i class="fas fa-plus mr-2"></i> Ajouter un médicament
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="bg-white rounded-xl shadow-md overflow-hidden mb-8 animate-fadein">
                            <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                                <h3 class="text-lg font-semibold">Mes médicaments</h3>
                                <div class="flex space-x-2">
                                    <button class="text-sm text-blue-600 hover:text-blue-800">Tous</button>
                                    <button class="text-sm text-gray-500 hover:text-gray-800">À prendre</button>
                                    <button class="text-sm text-gray-500 hover:text-gray-800">En retard</button>
                                </div>
                            </div>
                            
                            <div class="divide-y divide-gray-200">
                                <?php foreach ($medications as $medication): 
                                    $isLate = strtotime($medication['prochaine_prise']) < time();
                                    $isToday = date('Y-m-d', strtotime($medication['prochaine_prise'])) == date('Y-m-d');
                                ?>
                                    <div class="p-6 hover:bg-gray-50 transition medication-card <?php echo $isLate ? 'urgent' : ($isToday ? 'upcoming' : 'taken'); ?>">
                                        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                                            <div class="mb-4 md:mb-0">
                                                <div class="flex items-start">
                                                    <div class="bg-blue-100 text-blue-800 p-3 rounded-lg mr-4">
                                                        <i class="fas fa-pills text-lg"></i>
                                                    </div>
                                                    <div>
                                                        <h4 class="font-medium"><?php echo htmlspecialchars($medication['nom_medicament']); ?></h4>
                                                        <p class="text-sm text-gray-500"><?php echo htmlspecialchars($medication['dosage']); ?></p>
                                                        <div class="mt-2 flex items-center">
                                                            <?php if ($isLate): ?>
                                                                <span class="badge urgent mr-2">En retard</span>
                                                            <?php elseif ($isToday): ?>
                                                                <span class="badge upcoming mr-2">À prendre aujourd'hui</span>
                                                            <?php endif; ?>
                                                            <span class="text-sm text-gray-600">
                                                                <i class="fas fa-clock mr-1"></i> Prochaine prise: <?php echo date('d/m/Y H:i', strtotime($medication['prochaine_prise'])); ?>
                                                            </span>
                                                        </div>
                                                        <?php if (!empty($medication['notes'])): ?>
                                                            <p class="text-sm text-gray-600 mt-1"><i class="fas fa-info-circle mr-1"></i> <?php echo htmlspecialchars($medication['notes']); ?></p>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="flex items-center space-x-2">
                                                <a href="medications.php?action=taken&id=<?php echo $medication['id']; ?>" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-3 rounded-lg text-sm transition duration-300 flex items-center">
                                                    <i class="fas fa-check mr-1"></i> Pris
                                                </a>
                                                <div class="relative group">
                                                    <button class="text-gray-500 hover:text-gray-700 focus:outline-none p-2">
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </button>
                                                    <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10 hidden group-hover:block">
                                                        <div class="py-1">
                                                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"><i class="fas fa-edit mr-2"></i> Modifier</a>
                                                            <a href="#" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100"><i class="fas fa-trash mr-2"></i> Supprimer</a>
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
                    
                    <!-- Medication Tips -->
                    <div class="bg-white rounded-xl shadow-md p-6 animate-fadein">
                        <h3 class="text-lg font-semibold mb-4">Conseils pour la prise de médicaments</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="flex items-start">
                                <div class="bg-blue-100 text-blue-600 p-2 rounded-full mr-3 mt-1">
                                    <i class="fas fa-check-circle text-sm"></i>
                                </div>
                                <div>
                                    <h4 class="font-medium mb-1">Respectez les horaires</h4>
                                    <p class="text-sm text-gray-600">Prenez vos médicaments à heures fixes pour maintenir un taux constant dans votre organisme.</p>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <div class="bg-green-100 text-green-600 p-2 rounded-full mr-3 mt-1">
                                    <i class="fas fa-glass-water text-sm"></i>
                                </div>
                                <div>
                                    <h4 class="font-medium mb-1">Avec de l'eau</h4>
                                    <p class="text-sm text-gray-600">Prenez toujours vos médicaments avec un grand verre d'eau, sauf indication contraire.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Add Medication Modal -->
    <div id="addMedicationModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 lg:w-1/3 shadow-lg rounded-md bg-white animate-fadein">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-semibold">Ajouter un médicament</h3>
                <button onclick="toggleModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="mb-4">
                    <label for="nom_medicament" class="block text-gray-700 font-medium mb-2">Nom du médicament*</label>
                    <input type="text" id="nom_medicament" name="nom_medicament" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="dosage" class="block text-gray-700 font-medium mb-2">Dosage*</label>
                        <input type="text" id="dosage" name="dosage" placeholder="Ex: 500mg" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    <div>
                        <label for="frequence" class="block text-gray-700 font-medium mb-2">Fréquence*</label>
                        <select id="frequence" name="frequence" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            <option value="">Sélectionner</option>
                            <option value="quotidien">Quotidien</option>
                            <option value="hebdomadaire">Hebdomadaire</option>
                            <option value="mensuel">Mensuel</option>
                            <option value="2 fois/jour">2 fois/jour</option>
                            <option value="3 fois/jour">3 fois/jour</option>
                            <option value="autre">Autre</option>
                        </select>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="prochaine_prise" class="block text-gray-700 font-medium mb-2">Prochaine prise*</label>
                    <input type="datetime-local" id="prochaine_prise" name="prochaine_prise" min="<?php echo date('Y-m-d\TH:i'); ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                
                <div class="mb-4">
                    <label for="notes" class="block text-gray-700 font-medium mb-2">Notes supplémentaires</label>
                    <textarea id="notes" name="notes" rows="3" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Instructions spéciales, effets secondaires, etc."></textarea>
                </div>
                
                <div class="flex justify-end pt-4 border-t border-gray-200">
                    <button type="button" onclick="toggleModal()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-lg mr-2 transition duration-300">Annuler</button>
                    <button type="submit" name="add_medication" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-300 flex items-center">
                        <i class="fas fa-save mr-2"></i> Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleModal() {
            const modal = document.getElementById('addMedicationModal');
            modal.classList.toggle('hidden');
        }
        
        // Définir la date/heure minimale pour la prochaine prise
        document.getElementById('prochaine_prise').min = new Date().toISOString().slice(0, 16);
        
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