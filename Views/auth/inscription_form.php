<?php
// Backend inscription
require_once __DIR__ . '/../../BDD/pdo.php';
require_once __DIR__ . '/../../Models/user.php';
require_once __DIR__ . '/../../Models/filiere.php';

$erreurs = [];
$success = false;
$user = new User($pdo, null, null, null, null, null, null);
$filiere = new Filiere($pdo, null);
// Récupérer toutes les filières
$filieres = $filiere->getAllFilieres();	

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = $_POST['role'] ?? '';
    $numero_etudiant = $_POST['numero_etudiant'] ?? null;
    $email = trim($_POST['email'] ?? '');
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $mdp = $_POST['mdp'] ?? '';
    $photo_de_profil = 'default_profil.png';
    $id_filiere = $_POST['filiere'] ?? null;

    if (empty($role)) {
        $erreurs[] = "Le rôle est obligatoire.";
    }
    if (empty($numero_etudiant) && $role === 'etudiant') {
        $erreurs[] = "Le numéro étudiant est obligatoire.";
    } elseif ($user->checkNumeroEtudiantExists($numero_etudiant)) {
        $erreurs[] = "Ce numéro étudiant est déjà utilisé.";
    }
    if (empty($email)) {
        $erreurs[] = "L'email est obligatoire.";
    } else {
        if ($user->checkEmailExists($email)) {
            $erreurs[] = "Cet email est déjà utilisé.";
        }
    }
    if (empty($nom)) {
        $erreurs[] = "Le nom est obligatoire.";
    }
    if (empty($prenom)) {
        $erreurs[] = "Le prénom est obligatoire.";
    }
    if (empty($mdp)) {
        $erreurs[] = "Le mot de passe est obligatoire.";
    }
    if ($role === 'etudiant' && empty($id_filiere)) {
        $erreurs[] = "La filière est obligatoire pour les étudiants.";
    } elseif ($role === 'prof') {
        $id_filiere = $filiere->getFiliereByName('Responsable')['Id_Filière'];
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
            //Views/images/
            $upload_dir = __DIR__ . '/../../Views/images/';
            
            if (move_uploaded_file($file['tmp_name'], $upload_dir . $photo_de_profil)) {
                // Le fichier a été déplacé avec succès, le nom est déjà dans $photo_de_profil
            } else {
                $erreurs[] = "Erreur lors de l'upload de la photo de profil.";
                $photo_de_profil = 'default_profil.png';
            }
        }
    } else {
        $photo_de_profil = 'default_profil.png';
    }

    // Récupérer l'id du rôle
    $role_nom = $role === 'prof' ? 'Professeur responsable' : 'etudiant';
    $stmt = $pdo->prepare('SELECT Id_Role FROM Role WHERE role = ?');
    $stmt->execute([$role_nom]);
    $id_role = $stmt->fetchColumn();
    if (!$id_role) {
        $erreurs[] = "Rôle invalide.";
    }

    if (empty($erreurs)) {
        // Hash du mot de passe
        $mdp_hash = password_hash($mdp, PASSWORD_DEFAULT);
        // Insertion
        $stmt = $pdo->prepare('INSERT INTO User (numéro_étudiant, email, nom, prenom, photo_de_profil, mdp, Id_Role, Id_Filière) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $numero_etudiant ?: null,
            $email,
            $nom,
            $prenom,
            $photo_de_profil,
            $mdp_hash,
            $id_role,
            $id_filiere
        ]);
        $success = true;
    }
}
?>
<?php include_once __DIR__ . '/../partials/header.php'; ?>
<div class="container mt-5">
    <h2 class="mb-4">Inscription</h2>
    <?php if (!empty($erreurs)): ?>
        <div class="alert alert-danger">
            <?php foreach ($erreurs as $err): ?>
                <div><?= htmlspecialchars($err) ?></div>
            <?php endforeach; ?>
        </div>
    <?php elseif ($success): ?>
        <div class="alert alert-success">Inscription réussie ! Vous pouvez vous connecter.</div>
        <script>
            setTimeout(function() {
                window.location.href = 'connexion_form.php';
            }, 2000);
        </script>
    <?php endif; ?>
    <form action="" method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label class="form-label">Rôle</label><br>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="role" id="etudiant" value="etudiant" checked>
                <label class="form-check-label" for="etudiant">Étudiant</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="role" id="prof" value="prof">
                <label class="form-check-label" for="prof">Professeur responsable</label>
            </div>
        </div>
        <div class="mb-3">
            <label for="numero_etudiant" class="form-label">Numéro étudiant</label>
            <input type="text" class="form-control" id="numero_etudiant" name="numero_etudiant" value="<?= htmlspecialchars($_POST['numero_etudiant'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" required pattern="^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label for="nom" class="form-label">Nom</label>
            <input type="text" class="form-control" id="nom" name="nom" required value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label for="prenom" class="form-label">Prénom</label>
            <input type="text" class="form-control" id="prenom" name="prenom" required value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>">
        </div>
        <div class="mb-3" id="filiereField">
            <label for="filiere" class="form-label">Filière</label>
            <select class="form-select" id="filiere" name="filiere">
                <option value="">Sélectionnez une filière</option>
                <?php foreach ($filieres as $filiere): ?>
                    <option value="<?= $filiere['Id_Filière'] ?>" <?= (isset($_POST['filiere']) && $_POST['filiere'] == $filiere['Id_Filière']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($filiere['Nom_filière']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="photo_de_profil" class="form-label">Photo de profil</label>
            <input type="file" class="form-control" id="photo_de_profil" name="photo_de_profil" accept="image/*">
            <div class="form-text">Laisser vide pour utiliser l'image par défaut.</div>
        </div>
        <div class="mb-3">
            <label for="mdp" class="form-label">Mot de passe</label>
            <input type="password" class="form-control" id="mdp" name="mdp" required>
        </div>
        <button type="submit" class="btn btn-primary">S'inscrire</button>
    </form>
</div>

<?php include_once __DIR__ . '/../partials/footer.php'; ?>