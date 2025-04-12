<?php
// register.php
require_once 'includes/config.php';
require_once 'includes/auth.php';

if (is_logged_in()) {
    header("Location: dashboard.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Nettoyage des entrées
    $nom = filter_input(INPUT_POST, 'nom', FILTER_SANITIZE_STRING);
    $prenom = filter_input(INPUT_POST, 'prenom', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $telephone = filter_input(INPUT_POST, 'telephone', FILTER_SANITIZE_STRING);
    $date_naissance = filter_input(INPUT_POST, 'date_naissance', FILTER_SANITIZE_STRING);
    $groupe_sanguin = filter_input(INPUT_POST, 'groupe_sanguin', FILTER_SANITIZE_STRING);

    // Validation
    $errors = [];

    if (empty($nom))
        $errors[] = "Le nom est obligatoire.";
    if (empty($prenom))
        $errors[] = "Le prénom est obligatoire.";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL))
        $errors[] = "L'email est invalide.";
    if (empty($password))
        $errors[] = "Le mot de passe est obligatoire.";
    if ($password !== $confirm_password)
        $errors[] = "Les mots de passe ne correspondent pas.";
    if (strlen($password) < 8)
        $errors[] = "Le mot de passe doit contenir au moins 8 caractères.";
    if (!empty($telephone) && !preg_match('/^[0-9]{10,15}$/', $telephone))
        $errors[] = "Le numéro de téléphone est invalide.";

    if (empty($errors)) {
        // Vérifier si l'email existe déjà
        $sql = "SELECT id FROM users WHERE email = ?";

        try {
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $errors[] = "Cet email est déjà utilisé.";
            } else {
                // Hash du mot de passe avec coût élevé
                $hashed_password = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

                // Insertion dans la base de données avec une requête préparée
                $sql = "INSERT INTO users (nom, prenom, email, password, telephone, date_naissance, groupe_sanguin, role, created_at) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, 'patient', NOW())";

                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssssss", $nom, $prenom, $email, $hashed_password, $telephone, $date_naissance, $groupe_sanguin);

                if ($stmt->execute()) {
                    $_SESSION['success_message'] = "Votre compte a été créé avec succès. Vous pouvez maintenant vous connecter.";
                    header("Location: login.php");
                    exit();
                } else {
                    throw new Exception("Erreur lors de la création du compte.");
                }
            }
        } catch (Exception $e) {
            error_log("Erreur d'inscription: " . $e->getMessage());
            $errors[] = "Une erreur s'est produite. Veuillez réessayer plus tard.";
        } finally {
            if (isset($stmt))
                $stmt->close();
        }
    }

    if (!empty($errors)) {
        $error = implode("<br>", $errors);
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Inscription sur SantéTogo - Plateforme de gestion médicale">
    <title>Inscription - SantéTogo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="assets/favicon.ico" type="image/x-icon">
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
        }

        .gradient-bg {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
        }

        .register-container {
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            transition: all 0.3s ease;
            border-radius: 1rem;
        }

        .register-container:hover {
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
        }

        .btn-primary {
            transition: all 0.3s ease;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.3);
        }

        .input-field {
            transition: all 0.3s ease;
            border: 1px solid #e2e8f0;
        }

        .input-field:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .password-strength {
            height: 4px;
            transition: all 0.3s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fadein {
            animation: fadeIn 0.6s ease-out forwards;
        }

        .tooltip {
            visibility: hidden;
            opacity: 0;
            transition: all 0.3s ease;
        }

        .has-tooltip:hover .tooltip {
            visibility: visible;
            opacity: 1;
        }
    </style>
</head>

<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="register-container bg-white p-8 w-full max-w-md animate-fadein">
            <div class="text-center mb-8">
                <div class="flex items-center justify-center mb-4">
                    <i class="fas fa-heartbeat text-4xl gradient-bg bg-clip-text text-transparent mr-2"></i>
                    <span class="text-3xl font-bold text-gray-800">Santé<span class="text-blue-600">Togo</span></span>
                </div>
                <h2 class="text-2xl font-bold text-gray-800">Créer un compte</h2>
                <p class="text-gray-500 mt-2">Rejoignez notre plateforme santé</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-lg mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-red-500 mt-1"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700"><?= htmlspecialchars($error) ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <form id="registerForm" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]) ?>" method="post" novalidate
                class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="prenom" class="block text-sm font-medium text-gray-700 mb-1">Prénom*</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-user text-gray-400"></i>
                            </div>
                            <input type="text" id="prenom" name="prenom" value="<?= htmlspecialchars($prenom ?? '') ?>"
                                class="input-field pl-10 w-full px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-200"
                                required>
                        </div>
                    </div>
                    <div>
                        <label for="nom" class="block text-sm font-medium text-gray-700 mb-1">Nom*</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-user text-gray-400"></i>
                            </div>
                            <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($nom ?? '') ?>"
                                class="input-field pl-10 w-full px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-200"
                                required>
                        </div>
                    </div>
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email*</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-envelope text-gray-400"></i>
                        </div>
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($email ?? '') ?>"
                            class="input-field pl-10 w-full px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-200"
                            required>
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Mot de passe*</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-gray-400"></i>
                        </div>
                        <input type="password" id="password" name="password"
                            class="input-field pl-10 w-full px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-200"
                            required minlength="8">
                        <button type="button"
                            class="absolute right-3 top-3 text-gray-500 hover:text-gray-700 focus:outline-none toggle-password">
                            <i class="far fa-eye"></i>
                        </button>
                    </div>
                    <div class="mt-2 grid grid-cols-4 gap-1">
                        <div id="strength-1" class="password-strength bg-gray-200 rounded"></div>
                        <div id="strength-2" class="password-strength bg-gray-200 rounded"></div>
                        <div id="strength-3" class="password-strength bg-gray-200 rounded"></div>
                        <div id="strength-4" class="password-strength bg-gray-200 rounded"></div>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">8 caractères minimum</p>
                </div>

                <div>
                    <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Confirmer mot de
                        passe*</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-gray-400"></i>
                        </div>
                        <input type="password" id="confirm_password" name="confirm_password"
                            class="input-field pl-10 w-full px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-200"
                            required minlength="8">
                        <button type="button"
                            class="absolute right-3 top-3 text-gray-500 hover:text-gray-700 focus:outline-none toggle-password">
                            <i class="far fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div>
                    <label for="telephone" class="block text-sm font-medium text-gray-700 mb-1">Téléphone</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-phone text-gray-400"></i>
                        </div>
                        <input type="tel" id="telephone" name="telephone"
                            value="<?= htmlspecialchars($telephone ?? '') ?>"
                            class="input-field pl-10 w-full px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-200"
                            pattern="[0-9]{10,15}">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="date_naissance" class="block text-sm font-medium text-gray-700 mb-1">Date de
                            naissance</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-calendar-day text-gray-400"></i>
                            </div>
                            <input type="date" id="date_naissance" name="date_naissance"
                                value="<?= htmlspecialchars($date_naissance ?? '') ?>"
                                class="input-field pl-10 w-full px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-200"
                                max="<?= date('Y-m-d') ?>">
                        </div>
                    </div>
                    <div>
                        <label for="groupe_sanguin" class="block text-sm font-medium text-gray-700 mb-1">Groupe
                            sanguin</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-heartbeat text-gray-400"></i>
                            </div>
                            <select id="groupe_sanguin" name="groupe_sanguin"
                                class="input-field pl-10 w-full px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-200 appearance-none">
                                <option value="">Sélectionner</option>
                                <option value="A+" <?= ($groupe_sanguin ?? '') === 'A+' ? 'selected' : '' ?>>A+</option>
                                <option value="A-" <?= ($groupe_sanguin ?? '') === 'A-' ? 'selected' : '' ?>>A-</option>
                                <option value="B+" <?= ($groupe_sanguin ?? '') === 'B+' ? 'selected' : '' ?>>B+</option>
                                <option value="B-" <?= ($groupe_sanguin ?? '') === 'B-' ? 'selected' : '' ?>>B-</option>
                                <option value="AB+" <?= ($groupe_sanguin ?? '') === 'AB+' ? 'selected' : '' ?>>AB+</option>
                                <option value="AB-" <?= ($groupe_sanguin ?? '') === 'AB-' ? 'selected' : '' ?>>AB-</option>
                                <option value="O+" <?= ($groupe_sanguin ?? '') === 'O+' ? 'selected' : '' ?>>O+</option>
                                <option value="O-" <?= ($groupe_sanguin ?? '') === 'O-' ? 'selected' : '' ?>>O-</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <i class="fas fa-chevron-down text-gray-400"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pt-4">
                    <button type="submit"
                        class="btn-primary w-full text-white font-semibold py-3 px-4 rounded-lg flex items-center justify-center">
                        <i class="fas fa-user-plus mr-2"></i> Créer mon compte
                    </button>
                </div>

                <div class="text-center text-sm text-gray-500 pt-2">
                    <p>Vous avez déjà un compte? <a href="login.php"
                            class="font-medium text-blue-600 hover:text-blue-500 hover:underline">Connectez-vous</a></p>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Fonctionnalité pour afficher/masquer les mots de passe
        document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('click', function () {
                const input = this.previousElementSibling;
                const icon = this.querySelector('i');
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.replace('fa-eye', 'fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.replace('fa-eye-slash', 'fa-eye');
                }
            });
        });

        // Indicateur de force du mot de passe
        document.getElementById('password').addEventListener('input', function (e) {
            const password = e.target.value;
            const strengthBars = [
                document.getElementById('strength-1'),
                document.getElementById('strength-2'),
                document.getElementById('strength-3'),
                document.getElementById('strength-4')
            ];

            // Réinitialiser les barres
            strengthBars.forEach(bar => {
                bar.classList.remove('bg-red-500', 'bg-yellow-500', 'bg-green-500');
                bar.classList.add('bg-gray-200');
            });

            if (password.length === 0) return;

            // Calcul de la force
            let strength = 0;

            // Longueur minimale
            if (password.length >= 8) strength++;

            // Contient des lettres minuscules et majuscules
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;

            // Contient des chiffres
            if (/[0-9]/.test(password)) strength++;

            // Contient des caractères spéciaux
            if (/[^a-zA-Z0-9]/.test(password)) strength++;

            // Mise à jour des barres
            for (let i = 0; i < strength; i++) {
                strengthBars[i].classList.remove('bg-gray-200');

                if (strength <= 2) {
                    strengthBars[i].classList.add('bg-red-500');
                } else if (strength === 3) {
                    strengthBars[i].classList.add('bg-yellow-500');
                } else {
                    strengthBars[i].classList.add('bg-green-500');
                }
            }
        });

        // Validation côté client
        document.getElementById('registerForm').addEventListener('submit', function (e) {
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirm_password');
            let isValid = true;

            if (password.value !== confirmPassword.value) {
                alert('Les mots de passe ne correspondent pas.');
                isValid = false;
                confirmPassword.focus();
            }

            if (password.value.length < 8) {
                alert('Le mot de passe doit contenir au moins 8 caractères.');
                isValid = false;
                password.focus();
            }

            if (!isValid) {
                e.preventDefault();
            }
        });
    </script>
</body>

</html>