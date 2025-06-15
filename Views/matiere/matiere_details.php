<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /MCC_taoufiq/Views/auth/connexion_form.php');
    exit();
}

require_once __DIR__ . '/../../BDD/pdo.php';

// Vérifier si l'ID de la matière est fourni
if (!isset($_GET['id'])) {
    header('Location: grid_matieres.php');
    exit();
}

$id_matiere = $_GET['id'];

// Récupérer les informations de la matière
$stmt = $pdo->prepare('
    SELECT m.*, f.Nom_filière, 
           CONCAT(u.prenom, " ", u.nom) as responsable_nom,
           r.role as user_role,
           u.Id_user as Id_user
    FROM Matiere m
    JOIN Appartenir a ON m.Id_Matiere = a.Id_Matiere
    JOIN Filière f ON a.Id_Filière = f.Id_Filière
    JOIN gerer g ON m.Id_Matiere = g.Id_Matiere
    JOIN User u ON g.Id_user = u.Id_user
    JOIN Role r ON u.Id_Role = r.Id_Role
    WHERE m.Id_Matiere = ?
');
$stmt->execute([$id_matiere]);
$matiere = $stmt->fetch();

if (!$matiere) {
    header('Location: grid_matieres.php');
    exit();
}

// Récupérer tous les documents de la matière
$stmt = $pdo->prepare('SELECT * FROM Document WHERE Id_Matiere = ?');
$stmt->execute([$id_matiere]);
$documents = $stmt->fetchAll();

// Vérifier si l'utilisateur est le responsable de la matière
$isResponsable = $matiere['user_role'] === 'Professeur responsable' && $_SESSION['user_id'] === $matiere['Id_user'];

include_once __DIR__ . '/../partials/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <!-- En-tête de la matière -->
            <div class="card mb-4">
                <div class="card-body">
                    <h2 class="card-title"><?= htmlspecialchars($matiere['nom']) ?></h2>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <p><strong>Coefficient:</strong> <?= htmlspecialchars($matiere['coefficient']) ?></p>
                            <p><strong>Filière:</strong> <?= htmlspecialchars($matiere['Nom_filière']) ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Responsable:</strong> <?= htmlspecialchars($matiere['responsable_nom']) ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section Documents -->
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3 class="card-title">Documents</h3>
                        <?php if ($isResponsable): ?>
                            <a href="ajout_document.php?id=<?= $id_matiere ?>" class="btn btn-primary">
                                Ajouter un document
                            </a>
                            <a href="ajout_note_form.php?id=<?= $id_matiere ?>" class="btn btn-primary">
                                Ajouter une note
                            </a>
                        <?php endif; ?>
                    </div>

                    <?php if (empty($documents)): ?>
                        <div class="alert alert-info">
                            Aucun document n'est disponible pour cette matière pour le moment.
                        </div>
                    <?php else: ?>
                        <ul class="list-group">
                            <?php foreach ($documents as $doc): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>
                                        Document #<?= $doc['Id_Document'] ?>
                                    </span>
                                    <div>
                                        <a href="/MCC_taoufiq/Views/documents/<?= htmlspecialchars($doc['path']) ?>" class="btn btn-primary btn-sm">
                                            Télécharger
                                        </a>
                                        <?php if ($isResponsable): ?>
                                            <a href="supprimer_document.php?id=<?= $doc['Id_Document'] ?>&matiere=<?= $id_matiere ?>" class="btn btn-danger btn-sm ms-2" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce document ?')">
                                                Supprimer
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Section Notes pour l'étudiant connecté -->
            <?php
            $stmt = $pdo->prepare('SELECT r.role FROM User u JOIN Role r ON u.Id_Role = r.Id_Role WHERE u.Id_user = ?');
            $stmt->execute([$_SESSION['user_id']]);
            $role_user = $stmt->fetchColumn();
            if (strtolower($role_user) === 'etudiant' || strtolower($role_user) === 'étudiant') {
                // Récupérer les notes de l'étudiant pour cette matière
                $stmt = $pdo->prepare('SELECT Type, Note FROM Note WHERE Id_user = ? AND Id_Matiere = ?');
                $stmt->execute([$_SESSION['user_id'], $id_matiere]);
                $notes = $stmt->fetchAll();
                $moyenne = null;
                if ($notes && count($notes) > 0) {
                    $somme = 0;
                    foreach ($notes as $n) {
                        $somme += $n['Note'];
                    }
                    $moyenne = $somme / count($notes);
                }
            }
            ?>
            <div class="card mt-4">
                <div class="card-body">
                    <h3 class="card-title">Mes notes pour cette matière</h3>
                    <?php if (empty($notes)): ?>
                        <div class="alert alert-info">Vous n'avez pas encore de note pour cette matière.</div>
                    <?php else: ?>
                        <ul class="list-group mb-3">
                            <?php foreach ($notes as $note): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><?= htmlspecialchars($note['Type']) ?></span>
                                    <span class="badge bg-primary rounded-pill"><?= htmlspecialchars($note['Note']) ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <div class="fw-bold">Moyenne : <?= number_format($moyenne, 2) ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($isResponsable): ?>
                <div class="card mt-4">
                    <div class="card-body">
                        <h3 class="card-title">Notes des étudiants</h3>
                        <?php
                        // Récupérer toutes les notes pour cette matière avec les infos étudiants
                        $stmt = $pdo->prepare('
                            SELECT n.Type, n.Note, u.nom, u.prenom, u.numéro_étudiant
                            FROM Note n
                            JOIN User u ON n.Id_user = u.Id_user
                            WHERE n.Id_Matiere = ?
                            ORDER BY u.nom, u.prenom, n.Type
                        ');
                        $stmt->execute([$id_matiere]);
                        $notes_etudiants = $stmt->fetchAll();
                        if (empty($notes_etudiants)) : ?>
                            <div class="alert alert-info">Aucune note enregistrée pour cette matière.</div>
                        <?php else :
                            // Grouper par étudiant
                            $groupes = [];
                            foreach ($notes_etudiants as $n) {
                                $key = $n['numéro_étudiant'] . '|' . $n['nom'] . '|' . $n['prenom'];
                                $groupes[$key][] = $n;
                            }
                        ?>
                            <?php foreach ($groupes as $identite => $notes) :
                                list($num, $nom, $prenom) = explode('|', $identite);
                                $moyenne = array_sum(array_column($notes, 'Note')) / count($notes);
                            ?>
                                <div class="mb-4">
                                    <div class="fw-bold mb-2">
                                        <?= htmlspecialchars($nom) ?> <?= htmlspecialchars($prenom) ?> (<?= htmlspecialchars($num) ?>)
                                    </div>
                                    <ul class="list-group mb-2">
                                        <?php foreach ($notes as $note) : ?>
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                <span><?= htmlspecialchars($note['Type']) ?></span>
                                                <span class="badge bg-primary rounded-pill"><?= htmlspecialchars($note['Note']) ?></span>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                    <div class="fw-bold">Moyenne : <?= number_format($moyenne, 2) ?></div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="d-grid gap-2 mt-3 mb-3">
                <a href="matiere_forum.php?id=<?= $id_matiere ?>" class="btn btn-info">Accéder au forum</a>
            </div>

            <div class="mt-4">
                <a href="grid_matieres.php" class="btn btn-secondary">Retour à la liste des matières</a>
            </div>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/../partials/footer.php'; ?>
