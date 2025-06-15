<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /MCC_taoufiq/Views/auth/connexion_form.php');
    exit();
}

require_once __DIR__ . '/../../BDD/pdo.php';

// Vérifier l'ID de la matière
if (!isset($_GET['id'])) {
    header('Location: grid_matieres.php');
    exit();
}
$id_matiere = $_GET['id'];

// Vérifier que l'utilisateur est le responsable de la matière
$stmt = $pdo->prepare('
    SELECT u.Id_user, r.role, f.Id_Filière
    FROM gerer g
    JOIN User u ON g.Id_user = u.Id_user
    JOIN Role r ON u.Id_Role = r.Id_Role
    JOIN Appartenir a ON g.Id_Matiere = a.Id_Matiere
    JOIN Filière f ON a.Id_Filière = f.Id_Filière
    WHERE g.Id_Matiere = ?
');
$stmt->execute([$id_matiere]);
$responsable = $stmt->fetch();

if (!$responsable || $responsable['role'] !== 'Professeur responsable' || $_SESSION['user_id'] != $responsable['Id_user']) {
    header('Location: matiere_details.php?id=' . $id_matiere);
    exit();
}
$id_filiere = $responsable['Id_Filière'];

// Récupérer la liste des étudiants de la filière
$stmt = $pdo->prepare('
    SELECT Id_user, nom, prenom, numéro_étudiant
    FROM User
    WHERE Id_Filière = ? AND Id_Role = (SELECT Id_Role FROM Role WHERE role = "etudiant")
');
$stmt->execute([$id_filiere]);
$etudiants = $stmt->fetchAll();

$erreurs = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_etudiant = $_POST['etudiant'] ?? '';
    $type = trim($_POST['type'] ?? '');
    $note = trim($_POST['note'] ?? '');

    if (empty($id_etudiant)) {
        $erreurs[] = "Veuillez sélectionner un étudiant.";
    }
    if (empty($type)) {
        $erreurs[] = "Veuillez indiquer le type de note.";
    }
    if ($note === '' || !is_numeric($note) || $note < 0 || $note > 20) {
        $erreurs[] = "Veuillez saisir une note valide (entre 0 et 20).";
    }

    if (empty($erreurs)) {
        $stmt = $pdo->prepare('INSERT INTO Note (Type, Note, Id_user, Id_Matiere, Id_user_1) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$type, $note, $id_etudiant, $id_matiere, $_SESSION['user_id']]);
        $success = true;
    }
}

include_once __DIR__ . '/../partials/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title mb-4">Ajouter une note</h2>

                    <?php if (!empty($erreurs)): ?>
                        <div class="alert alert-danger">
                            <?php foreach ($erreurs as $err): ?>
                                <div><?= htmlspecialchars($err) ?></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            La note a été ajoutée avec succès !
                        </div>
                        <script>
                            setTimeout(function() {
                                window.location.href = 'matiere_details.php?id=<?= $id_matiere ?>';
                            }, 2000);
                        </script>
                    <?php endif; ?>

                    <form action="" method="POST">
                        <div class="mb-3">
                            <label for="etudiant" class="form-label">Étudiant</label>
                            <select class="form-select" id="etudiant" name="etudiant" required>
                                <option value="">Sélectionnez un étudiant</option>
                                <?php foreach ($etudiants as $etu): ?>
                                    <option value="<?= $etu['Id_user'] ?>" <?= (isset($_POST['etudiant']) && $_POST['etudiant'] == $etu['Id_user']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($etu['nom'] . ' ' . $etu['prenom'] . ' (' . $etu['numéro_étudiant'] . ')') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="type" class="form-label">Type de note</label>
                            <input type="text" class="form-control" id="type" name="type" required value="<?= htmlspecialchars($_POST['type'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label for="note" class="form-label">Note</label>
                            <input type="number" class="form-control" id="note" name="note" min="0" max="20" step="0.01" required value="<?= htmlspecialchars($_POST['note'] ?? '') ?>">
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Ajouter la note</button>
                            <a href="matiere_details.php?id=<?= $id_matiere ?>" class="btn btn-secondary">Retour</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/../partials/footer.php'; ?>