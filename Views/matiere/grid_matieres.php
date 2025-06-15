<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /MCC_taoufiq/Views/auth/connexion_form.php');
    exit();
}

require_once __DIR__ . '/../../BDD/pdo.php';

// Récupérer le rôle de l'utilisateur
$stmt = $pdo->prepare('SELECT r.role FROM User u JOIN Role r ON u.Id_Role = r.Id_Role WHERE u.Id_user = ?');
$stmt->execute([$_SESSION['user_id']]);
$userRole = $stmt->fetchColumn();

// Requête différente selon le rôle
if (strtolower($userRole) === 'etudiant' || strtolower($userRole) === 'étudiant') {
    // Pour les étudiants : matières de leur filière
    $stmt = $pdo->prepare('
        SELECT DISTINCT m.*, f.Nom_filière, 
               CONCAT(u.prenom, " ", u.nom) as responsable_nom
        FROM Matiere m
        JOIN Appartenir a ON m.Id_Matiere = a.Id_Matiere
        JOIN Filière f ON a.Id_Filière = f.Id_Filière
        JOIN gerer g ON m.Id_Matiere = g.Id_Matiere
        JOIN User u ON g.Id_user = u.Id_user
        WHERE f.Id_Filière = (
            SELECT Id_Filière FROM User WHERE Id_user = ?
        )
        ORDER BY m.nom
    ');
    $stmt->execute([$_SESSION['user_id']]);
} else {
    // Pour les professeurs : matières dont ils sont responsables
    $stmt = $pdo->prepare('
        SELECT DISTINCT m.*, f.Nom_filière, 
               CONCAT(u.prenom, " ", u.nom) as responsable_nom
        FROM Matiere m
        JOIN Appartenir a ON m.Id_Matiere = a.Id_Matiere
        JOIN Filière f ON a.Id_Filière = f.Id_Filière
        JOIN gerer g ON m.Id_Matiere = g.Id_Matiere
        JOIN User u ON g.Id_user = u.Id_user
        WHERE g.Id_user = ?
        ORDER BY m.nom
    ');
    $stmt->execute([$_SESSION['user_id']]);
}

$matieres = $stmt->fetchAll();

include_once __DIR__ . '/../partials/header.php';
?>

<div class="container mt-4">
    <h2 class="mb-4"><?= (strtolower($userRole) === 'etudiant' || strtolower($userRole) === 'étudiant') ? 'Mes Matières' : 'Matières dont je suis responsable' ?></h2>
    <?php if ($userRole === 'Professeur responsable'): ?>
        <a href="ajout_matiere_form.php" class="btn btn-primary mb-3">Ajouter une matière</a>
    <?php endif; ?>

    <?php if (empty($matieres)): ?>
        <div class="alert alert-info">
            <?= (strtolower($userRole) === 'etudiant' || strtolower($userRole) === 'étudiant') ? 'Aucune matière n\'est disponible pour votre filière.' : 'Vous n\'êtes responsable d\'aucune matière.' ?>
        </div>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php foreach ($matieres as $matiere): ?>
                <div class="col">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($matiere['nom']) ?></h5>
                            <div class="card-text">
                                <p class="mb-2">
                                    <strong>Coefficient:</strong> <?= htmlspecialchars($matiere['coefficient']) ?>
                                </p>
                                <p class="mb-2">
                                    <strong>Filière:</strong> <?= htmlspecialchars($matiere['Nom_filière']) ?>
                                </p>
                                <p class="mb-0">
                                    <strong>Responsable:</strong> <?= htmlspecialchars($matiere['responsable_nom']) ?>
                                </p>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent">
                            <a href="matiere_details.php?id=<?= $matiere['Id_Matiere'] ?>" 
                               class="btn btn-primary btn-sm">Voir détails</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include_once __DIR__ . '/../partials/footer.php'; ?>
