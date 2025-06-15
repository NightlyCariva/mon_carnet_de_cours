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

// Vérifier si l'utilisateur est le responsable de la matière
$stmt = $pdo->prepare('
    SELECT m.*, r.role as user_role, u.Id_user
    FROM Matiere m
    JOIN gerer g ON m.Id_Matiere = g.Id_Matiere
    JOIN User u ON g.Id_user = u.Id_user
    JOIN Role r ON u.Id_Role = r.Id_Role
    WHERE m.Id_Matiere = ?
');
$stmt->execute([$id_matiere]);
$matiere = $stmt->fetch();

if (!$matiere || $matiere['user_role'] !== 'Professeur responsable' || $_SESSION['user_id'] !== $matiere['Id_user']) {
    header('Location: grid_matieres.php');
    exit();
}

$erreurs = [];
$success = false;

// Vérifier si un document existe déjà
$stmt = $pdo->prepare('SELECT * FROM Document WHERE Id_Matiere = ?');
$stmt->execute([$id_matiere]);
$document_existant = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier si un fichier a été uploadé
    if (!isset($_FILES['document']) || $_FILES['document']['error'] === UPLOAD_ERR_NO_FILE) {
        $erreurs[] = "Veuillez sélectionner un document à uploader.";
    } else {
        $file = $_FILES['document'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx'];
        
        if (!in_array($ext, $allowed_extensions)) {
            $erreurs[] = "Format de fichier non autorisé. Utilisez PDF, DOC, DOCX, PPT, PPTX, XLS ou XLSX.";
        } elseif ($file['size'] > 10 * 1024 * 1024) { // 10MB max
            $erreurs[] = "Le fichier est trop volumineux. Taille maximum : 10MB.";
        } else {
            // Nettoyer le nom de la matière pour le nom de fichier
            $nom_matiere = preg_replace('/[^a-zA-Z0-9_-]/', '_', $matiere['nom']);
            $timestamp = time();
            $filename = $nom_matiere . '_' . $timestamp . '.' . $ext;
            $upload_dir = __DIR__ . '/../../Views/documents/';
            $filepath = $upload_dir . $filename;

            if (!file_exists($upload_dir)) {
                if (!mkdir($upload_dir, 0777, true)) {
                    $erreurs[] = "Impossible de créer le dossier de destination.";
                }
            }

            if (!is_writable($upload_dir)) {
                $erreurs[] = "Le dossier de destination n'a pas les permissions d'écriture.";
            } else {
                if (move_uploaded_file($file['tmp_name'], $filepath)) {
                    // Vérification que l'extension est bien présente
                    if (strtolower(pathinfo($filename, PATHINFO_EXTENSION)) !== $ext) {
                        $erreurs[] = "Erreur lors de la génération du nom de fichier (extension manquante).";
                        @unlink($filepath);
                    } else {
                        try {
                            $pdo->beginTransaction();
                            // Toujours insérer un nouveau document
                            $stmt = $pdo->prepare('INSERT INTO Document (path, Id_Matiere) VALUES (?, ?)');
                            $stmt->execute([$filename, $id_matiere]);
                            $pdo->commit();
                            $success = true;
                        } catch (Exception $e) {
                            $pdo->rollBack();
                            @unlink($filepath); // Supprimer le fichier en cas d'erreur
                            $erreurs[] = "Erreur lors de l'enregistrement du document : " . $e->getMessage();
                            error_log("Erreur d'upload de document : " . $e->getMessage());
                        }
                    }
                } else {
                    $erreurs[] = "Erreur lors de l'upload du fichier. Code d'erreur : " . $file['error'];
                }
            }
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
                    <h2 class="card-title mb-4">
                        Ajouter un document
                    </h2>

                    <?php if (!empty($erreurs)): ?>
                        <div class="alert alert-danger">
                            <?php foreach ($erreurs as $err): ?>
                                <div><?= htmlspecialchars($err) ?></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            Le document a été <?= $document_existant ? 'modifié' : 'ajouté' ?> avec succès !
                        </div>
                        <script>
                            setTimeout(function() {
                                window.location.href = 'matiere_details.php?id=<?= $id_matiere ?>';
                            }, 2000);
                        </script>
                    <?php endif; ?>

                    <form action="" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="document" class="form-label">Document</label>
                            <input type="file" class="form-control" id="document" name="document" required
                                   accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx">
                            <div class="form-text">
                                Formats acceptés : PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX. Taille maximum : 10MB.
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <?= $document_existant ? 'Modifier le document' : 'Ajouter le document' ?>
                            </button>
                            <a href="matiere_details.php?id=<?= $id_matiere ?>" class="btn btn-secondary">
                                Retour aux détails de la matière
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/../partials/footer.php'; ?>
