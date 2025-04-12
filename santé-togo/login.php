<?php
// login.php
include 'includes/config.php';
include 'includes/auth.php';

if (is_logged_in()) {
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    
    if (empty($email) || empty($password)) {
        $error = "Veuillez remplir tous les champs.";
    } else {
        $sql = "SELECT id, nom, prenom, email, password, role FROM users WHERE email = ? or SELECT id, nom, prenom, email, password, role FROM medecins WHERE email = ?";
        
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $email);
            
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);
                
                if (mysqli_stmt_num_rows($stmt) == 1) {
                    mysqli_stmt_bind_result($stmt, $id, $nom, $prenom, $email, $hashed_password, $role);
                    if (mysqli_stmt_fetch($stmt)) {
                        if (password_verify($password, $hashed_password)) {
                            // Stocker les données dans la session
                            $_SESSION['loggedin'] = true;
                            $_SESSION['id'] = $id;
                            $_SESSION['email'] = $email;
                            $_SESSION['nom'] = $nom;
                            $_SESSION['prenom'] = $prenom;
                            $_SESSION['role'] = $role;
                            
                            // Redirection
                            header("Location: dashboard.php");
                            exit();
                        } else {
                            $error = "Mot de passe incorrect.";
                        }
                    }
                } else {
                    $error = "Aucun compte trouvé avec cet email.";
                }
            } else {
                $error = "Oops! Quelque chose s'est mal passé. Veuillez réessayer plus tard.";
            }
            mysqli_stmt_close($stmt);
        }
    }
    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - SantéTogo</title>
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
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
        }
        
        .login-container {
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }
        
        .login-container:hover {
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.12);
        }
        
        .btn-primary {
            transition: all 0.3s ease;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(37, 99, 235, 0.3);
        }
        
        .input-field {
            transition: all 0.3s ease;
            border: 1px solid #e2e8f0;
        }
        
        .input-field:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
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
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="login-container bg-white rounded-xl p-8 w-full max-w-md animate-fadein">
            <div class="text-center mb-8">
                <div class="flex items-center justify-center mb-4">
                    <i class="fas fa-heartbeat text-4xl gradient-bg bg-clip-text text-transparent mr-2"></i>
                    <span class="text-3xl font-bold text-gray-800">Santé<span class="text-blue-600">Togo</span></span>
                </div>
                <h2 class="text-2xl font-bold text-gray-800">Connectez-vous</h2>
                <p class="text-gray-500 mt-2">Accédez à votre espace santé personnel</p>
            </div>
            
            <?php if (!empty($error)): ?>
                <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-lg mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-red-500 mt-1"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700"><?php echo $error; ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="space-y-6">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Adresse email</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-envelope text-gray-400"></i>
                        </div>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            class="input-field pl-10 w-full px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-200" 
                            placeholder="votre@email.com"
                            required
                        >
                    </div>
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Mot de passe</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-gray-400"></i>
                        </div>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="input-field pl-10 w-full px-4 py-3 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-200" 
                            placeholder="••••••••"
                            required
                        >
                    </div>
                    <div class="mt-2 text-right">
                        <a href="forgot-password.php" class="text-sm text-blue-600 hover:underline">Mot de passe oublié?</a>
                    </div>
                </div>
                
                <div>
                    <button 
                        type="submit" 
                        class="btn-primary w-full text-white font-semibold py-3 px-4 rounded-lg flex items-center justify-center"
                    >
                        <i class="fas fa-sign-in-alt mr-2"></i> Se connecter
                    </button>
                </div>
                
                <div class="text-center text-sm text-gray-500">
                    <p>Vous n'avez pas de compte? 
                        <a href="register.php" class="font-medium text-blue-600 hover:text-blue-500 hover:underline">S'inscrire</a>
                    </p>
                </div>
            </form>
            
            <div class="mt-8 pt-6 border-t border-gray-200">
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-white text-gray-500">Ou continuez avec</span>
                    </div>
                </div>
                
                <div class="mt-6 grid grid-cols-2 gap-3">
                    <a href="#" class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 rounded-lg shadow-sm bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                        <i class="fab fa-google text-red-500 mr-2 mt-0.5"></i> Google
                    </a>
                    <a href="#" class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 rounded-lg shadow-sm bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                        <i class="fab fa-facebook-f text-blue-600 mr-2 mt-0.5"></i> Facebook
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>