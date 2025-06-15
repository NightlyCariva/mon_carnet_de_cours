<?php
// Démarrer la session
session_start();
include_once 'BDD/pdo.php';
include_once 'Models/role.php';
include_once 'Models/filiere.php';


// Initialiser les rôles par défaut
$role = new Role($pdo, null);
$role->initDefaultRoles();

// Initialiser les filières par défaut
$filiere = new Filiere($pdo, null);
$filiere->initFiliereResponsable();

// Vérifier si l'utilisateur est déjà connecté
if (isset($_SESSION['user_id'])) {
    header('Location: Views/profil/user_profil.php');
    exit();
}

// Si non connecté, rediriger vers le formulaire de connexion
header('Location: Views/auth/connexion_form.php');
exit();
?> 