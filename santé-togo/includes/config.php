<?php
// Configuration de la base de données
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'sante_togo');

// Connexion à la base de données
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Vérifier la connexion
if($conn === false){
    die("ERREUR : Impossible de se connecter. " . mysqli_connect_error());
}

// Définir le charset
mysqli_set_charset($conn, "utf8mb4");

// Démarrer la session
session_start();
?>