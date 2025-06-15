<?php
// Vérifier si l'utilisateur est connecté
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /MCC_taoufiq/Views/auth/connexion_form.php');
    exit();
}

require_once __DIR__ . '/../../BDD/pdo.php';
require_once __DIR__ . '/../../Models/user.php';
require_once __DIR__ . '/../../Models/filiere.php';

$erreurs = [];
$success = false;

// Récupérer les informations actuelles de l'utilisateur
$stmt = $pdo->prepare('
    SELECT u.*, r.role as role_nom, f.Nom_filière 
    FROM User u 
    LEFT JOIN Role r ON u.Id_Role = r.Id_Role 
    LEFT JOIN Filière f ON u.Id_Filière = f.Id_Filière 
    WHERE u.Id_user = ?
');
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Récupérer toutes les filières
$filiere = new Filiere($pdo, null);
$filieres = $filiere->getAllFilieres();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $mdp = $_POST['mdp'] ?? '';
    $id_filiere = $_POST['filiere'] ?? null;
    $photo_de_profil = $user['photo_de_profil'];

    // Validation des champs
    if (empty($email)) {
        $erreurs[] = "L'email est obligatoire.";
    } elseif ($email !== $user['email'] && $user->checkEmailExists($email)) {
        $erreurs[] = "Cet email est déjà utilisé.";
    }

    if (empty($nom)) {
        $erreurs[] = "Le nom est obligatoire.";
    }

    if (empty($prenom)) {
        $erreurs[] = "Le prénom est obligatoire.";
    }

    // Gestion de la photo de profil
    if (isset($_FILES['photo_de_profil']) && $_FILES['photo_de_profil']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['photo_de_profil'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (!in_array($ext, $allowed_extensions)) {
            $erreurs[] = "Format de fichier non autorisé. Utilisez JPG, JPEG, PNG ou GIF.";
        } else {
            $timestamp = time();
            $photo_de_profil = "profil_{$timestamp}.{$ext}";
            $upload_dir = __DIR__ . '/../../Views/images/';
            
            if (move_uploaded_file($file['tmp_name'], $upload_dir . $photo_de_profil)) {
                // Supprimer l'ancienne photo si ce n'est pas la photo par défaut
                if ($user['photo_de_profil'] !== 'default_profil.png') {
                    @unlink($upload_dir . $user['photo_de_profil']);
                }
            } else {
                $erreurs[] = "Erreur lors de l'upload de la photo de profil.";
                $photo_de_profil = $user['photo_de_profil'];
            }
        }
    }

    if (empty($erreurs)) {
        // Préparer la requête de mise à jour
        $sql = 'UPDATE User SET email = ?, nom = ?, prenom = ?, photo_de_profil = ?';
        $params = [$email, $nom, $prenom, $photo_de_profil];

        // Ajouter le mot de passe à la mise à jour seulement s'il a été modifié
        if (!empty($mdp)) {
            $sql .= ', mdp = ?';
            $params[] = password_hash($mdp, PASSWORD_DEFAULT);
        }

        // Ajouter la filière à la mise à jour
        $sql .= ', Id_Filière = ?';
        $params[] = $id_filiere;

        // Finaliser la requête
        $sql .= ' WHERE Id_user = ?';
        $params[] = $_SESSION['user_id'];

        // Exécuter la mise à jour
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute($params)) {
            $success = true;
            // Mettre à jour les variables de session
            $_SESSION['user_email'] = $email;
            $_SESSION['user_nom'] = $nom;
            $_SESSION['user_prenom'] = $prenom;
            $_SESSION['user_photo'] = $photo_de_profil;
        } else {
            $erreurs[] = "Une erreur est survenue lors de la mise à jour du profil.";
        }
    }
}

include_once __DIR__ . '/../partials/header.php';
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title mb-4">Modifier mon profil</h2>

                    <?php if (!empty($erreurs)): ?>
                        <div class="alert alert-danger">
                            <?php foreach ($erreurs as $err): ?>
                                <div><?= htmlspecialchars($err) ?></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            Votre profil a été mis à jour avec succès !
                        </div>
                    <?php endif; ?>

                    <form action="" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required 
                                   value="<?= htmlspecialchars($user['email']) ?>">
                        </div>

                        <div class="mb-3">
                            <label for="nom" class="form-label">Nom</label>
                            <input type="text" class="form-control" id="nom" name="nom" required 
                                   value="<?= htmlspecialchars($user['nom']) ?>">
                        </div>

                        <div class="mb-3">
                            <label for="prenom" class="form-label">Prénom</label>
                            <input type="text" class="form-control" id="prenom" name="prenom" required 
                                   value="<?= htmlspecialchars($user['prenom']) ?>">
                        </div>

                        <?php if ($user['role_nom'] === 'etudiant'): ?>
                        <div class="mb-3">
                            <label for="filiere" class="form-label">Filière</label>
                            <select class="form-select" id="filiere" name="filiere" required>
                                <?php foreach ($filieres as $f): ?>
                                    <option value="<?= $f['Id_Filière'] ?>" 
                                            <?= ($user['Id_Filière'] == $f['Id_Filière']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($f['Nom_filière']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <label for="photo_de_profil" class="form-label">Photo de profil</label>
                            <input type="file" class="form-control" id="photo_de_profil" name="photo_de_profil" accept="image/*">
                            <div class="form-text">Laisser vide pour conserver la photo actuelle.</div>
                        </div>

                        <div class="mb-3">
                            <label for="mdp" class="form-label">Nouveau mot de passe</label>
                            <input type="password" class="form-control" id="mdp" name="mdp">
                            <div class="form-text">Laisser vide pour conserver le mot de passe actuel.</div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Mettre à jour le profil</button>
                            <a href="user_profil.php" class="btn btn-secondary">Retour au profil</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/../partials/footer.php'; ?>
