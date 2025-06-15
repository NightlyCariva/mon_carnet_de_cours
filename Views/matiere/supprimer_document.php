<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /MCC_taoufiq/Views/auth/connexion_form.php');
    exit();
}

require_once __DIR__ . '/../../BDD/pdo.php';

// Vérifier les paramètres
if (!isset($_GET['id']) || !isset($_GET['matiere'])) {
    header('Location: grid_matieres.php');
    exit();
}

$id_document = $_GET['id'];
$id_matiere = $_GET['matiere'];

// Récupérer le document
$stmt = $pdo->prepare('SELECT * FROM Document WHERE Id_Document = ? AND Id_Matiere = ?');
$stmt->execute([$id_document, $id_matiere]);
$document = $stmt->fetch();

if (!$document) {
    header('Location: matiere_details.php?id=' . $id_matiere);
    exit();
}

// Vérifier que l'utilisateur est le responsable de la matière
$stmt = $pdo->prepare('
    SELECT u.Id_user, r.role
    FROM gerer g
    JOIN User u ON g.Id_user = u.Id_user
    JOIN Role r ON u.Id_Role = r.Id_Role
    WHERE g.Id_Matiere = ?
');
$stmt->execute([$id_matiere]);
$responsable = $stmt->fetch();

if (!$responsable || $responsable['role'] !== 'Professeur responsable' || $_SESSION['user_id'] != $responsable['Id_user']) {
    header('Location: matiere_details.php?id=' . $id_matiere);
    exit();
}

// Supprimer le fichier du serveur
$upload_dir = __DIR__ . '/../../Views/documents/';
$old_file = $upload_dir . basename($document['path']);
if (file_exists($old_file)) {
    @unlink($old_file);
}

// Supprimer l'entrée en base
$stmt = $pdo->prepare('DELETE FROM Document WHERE Id_Document = ?');
$stmt->execute([$id_document]);

header('Location: matiere_details.php?id=' . $id_matiere);
exit(); 