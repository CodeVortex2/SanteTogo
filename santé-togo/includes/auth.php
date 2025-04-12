<?php
// Vérifie si les fonctions existent déjà
if (!function_exists('is_logged_in')) {
    function is_logged_in() {
        return isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
    }

    function require_login() {
        if (!is_logged_in()) {
            header("Location: login.php");
            exit();
        }
    }

    function is_admin() {
        return is_logged_in() && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }

    function require_admin() {
        if (!is_admin()) {
            header("Location: dashboard.php");
            exit();
        }
    }
}
?>