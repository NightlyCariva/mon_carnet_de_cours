<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /MCC_taoufiq/Views/auth/connexion_form.php');
    exit();
}
require_once __DIR__ . '/../../BDD/pdo.php';

// Vérifier que l'utilisateur est responsable
$stmt = $pdo->prepare('SELECT r.role FROM User u JOIN Role r ON u.Id_Role = r.Id_Role WHERE u.Id_user = ?');
$stmt->execute([$_SESSION['user_id']]);
$role = $stmt->fetchColumn();
if (strtolower($role) !== 'professeur responsable') {
    header('Location: affichage_calendrier.php');
    exit();
}

// Récupérer les matières dont il est responsable
$stmt = $pdo->prepare('
    SELECT m.Id_Matiere, m.nom
    FROM Matiere m
    JOIN gerer g ON m.Id_Matiere = g.Id_Matiere
    WHERE g.Id_user = ?
');
$stmt->execute([$_SESSION['user_id']]);
$matieres = $stmt->fetchAll();

$erreurs = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_matiere = $_POST['matiere'] ?? '';
    $rdv = $_POST['rdv'] ?? '';
    $duree = $_POST['duree'] ?? '';

    if (empty($id_matiere) || empty($rdv) || empty($duree)) {
        $erreurs[] = "Tous les champs sont obligatoires.";
    } elseif (!is_numeric($duree) || $duree <= 0) {
        $erreurs[] = "La durée doit être un nombre positif.";
    } else {
        $stmt = $pdo->prepare('INSERT INTO Cours (rdv, duree, Id_Matiere, Id_user) VALUES (?, ?, ?, ?)');
        $stmt->execute([$rdv, $duree, $id_matiere, $_SESSION['user_id']]);
        $success = true;
    }
}

include_once __DIR__ . '/../partials/header.php';
?>

<div class="container mt-4">
    <h2 class="mb-4">Ajouter un cours</h2>
    <?php if (!empty($erreurs)): ?>
        <div class="alert alert-danger">
            <?php foreach ($erreurs as $err): ?>
                <div><?= htmlspecialchars($err) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success">Cours ajouté avec succès !</div>
        <script>
            setTimeout(function() {
                window.location.href = 'affichage_calendrier.php';
            }, 2000);
        </script>
    <?php endif; ?>
    <form method="POST">
        <div class="mb-3">
            <label for="matiere" class="form-label">Matière</label>
            <select name="matiere" id="matiere" class="form-select" required>
                <option value="">Sélectionnez une matière</option>
                <?php foreach ($matieres as $m): ?>
                    <option value="<?= $m['Id_Matiere'] ?>"><?= htmlspecialchars($m['nom']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="rdv" class="form-label">Date et heure</label>
            <input type="datetime-local" name="rdv" id="rdv" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="duree" class="form-label">Durée (en minutes)</label>
            <input type="number" name="duree" id="duree" class="form-control" min="1" required>
        </div>
        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary">Ajouter le cours</button>
            <a href="affichage_calendrier.php" class="btn btn-secondary">Retour</a>
        </div>
    </form>
</div>

<?php include_once __DIR__ . '/../partials/footer.php'; ?>