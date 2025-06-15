<?php
// Démarrer la session
session_start();

// Détruire toutes les variables de session
$_SESSION = array();

// Détruire la session
session_destroy();

// Rediriger vers la page de connexion
header('Location: /MCC_taoufiq/Views/auth/connexion_form.php');
exit();
