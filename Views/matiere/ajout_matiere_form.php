<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /MCC_taoufiq/Views/auth/connexion_form.php');
    exit();
}

require_once __DIR__ . '/../../BDD/pdo.php';
require_once __DIR__ . '/../../Models/filiere.php';

// Vérifier si l'utilisateur est un professeur responsable
$stmt = $pdo->prepare('SELECT r.role FROM User u JOIN Role r ON u.Id_Role = r.Id_Role WHERE u.Id_user = ?');
$stmt->execute([$_SESSION['user_id']]);
$userRole = $stmt->fetchColumn();

if ($userRole !== 'Professeur responsable') {
    header('Location: /MCC_taoufiq/Views/matiere/grid_matieres.php');
    exit();
}

$erreurs = [];
$success = false;

// Récupérer toutes les filières
$filiere = new Filiere($pdo, null);
$filieres = $filiere->getAllFilieres();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $coefficient = floatval($_POST['coefficient'] ?? 0);
    $id_filiere = $_POST['filiere'] ?? null;

    // Validation
    if (empty($nom)) {
        $erreurs[] = "Le nom de la matière est obligatoire.";
    }

    if ($coefficient <= 0) {
        $erreurs[] = "Le coefficient doit être supérieur à 0.";
    }

    if (empty($id_filiere)) {
        $erreurs[] = "La filière est obligatoire.";
    }

    // Vérifier si la matière existe déjà dans la filière
    if (empty($erreurs)) {
        $stmt = $pdo->prepare('
            SELECT COUNT(*) FROM Matiere m
            JOIN Appartenir a ON m.Id_Matiere = a.Id_Matiere
            WHERE m.nom = ? AND a.Id_Filière = ?
        ');
        $stmt->execute([$nom, $id_filiere]);
        if ($stmt->fetchColumn() > 0) {
            $erreurs[] = "Cette matière existe déjà dans cette filière.";
        }
    }

    if (empty($erreurs)) {
        try {
            $pdo->beginTransaction();

            // Insérer la matière
            $stmt = $pdo->prepare('INSERT INTO Matiere (nom, coefficient) VALUES (?, ?)');
            $stmt->execute([$nom, $coefficient]);
            $id_matiere = $pdo->lastInsertId();

            // Lier la matière à la filière
            $stmt = $pdo->prepare('INSERT INTO Appartenir (Id_Matiere, Id_Filière) VALUES (?, ?)');
            $stmt->execute([$id_matiere, $id_filiere]);

            // Lier la matière au professeur responsable
            $stmt = $pdo->prepare('INSERT INTO gerer (Id_Matiere, Id_user) VALUES (?, ?)');
            $stmt->execute([$id_matiere, $_SESSION['user_id']]);

            $pdo->commit();
            $success = true;
        } catch (Exception $e) {
            $pdo->rollBack();
            $erreurs[] = "Une erreur est survenue lors de l'ajout de la matière.";
        }
    }
}

include_once __DIR__ . '/../partials/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title mb-4">Ajouter une nouvelle matière</h2>

                    <?php if (!empty($erreurs)): ?>
                        <div class="alert alert-danger">
                            <?php foreach ($erreurs as $err): ?>
                                <div><?= htmlspecialchars($err) ?></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            La matière a été ajoutée avec succès !
                        </div>
                        <script>
                            setTimeout(function() {
                                window.location.href = 'grid_matieres.php';
                            }, 2000);
                        </script>
                    <?php endif; ?>

                    <form action="" method="POST">
                        <div class="mb-3">
                            <label for="nom" class="form-label">Nom de la matière</label>
                            <input type="text" class="form-control" id="nom" name="nom" required
                                   value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>">
                        </div>

                        <div class="mb-3">
                            <label for="coefficient" class="form-label">Coefficient</label>
                            <input type="number" class="form-control" id="coefficient" name="coefficient" 
                                   step="0.1" min="0.1" required
                                   value="<?= htmlspecialchars($_POST['coefficient'] ?? '') ?>">
                        </div>

                        <div class="mb-3">
                            <label for="filiere" class="form-label">Filière</label>
                            <select class="form-select" id="filiere" name="filiere" required>
                                <option value="">Sélectionnez une filière</option>
                                <?php foreach ($filieres as $f): ?>
                                    <option value="<?= $f['Id_Filière'] ?>" 
                                            <?= (isset($_POST['filiere']) && $_POST['filiere'] == $f['Id_Filière']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($f['Nom_filière']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Ajouter la matière</button>
                            <a href="grid_matieres.php" class="btn btn-secondary">Retour à la liste</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/../partials/footer.php'; ?>
