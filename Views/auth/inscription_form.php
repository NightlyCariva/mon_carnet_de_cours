<?php
// Backend inscription
require_once __DIR__ . '/../../BDD/pdo.php';
require_once __DIR__ . '/../../Models/user.php';

$erreurs = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = $_POST['role'] ?? '';
    $numero_etudiant = $_POST['numero_etudiant'] ?? null;
    $email = trim($_POST['email'] ?? '');
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $mdp = $_POST['mdp'] ?? '';
    $photo_de_profil = 'default_profil.png';

    // Validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreurs[] = "Email invalide.";
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

    // Gestion de la photo de profil
    if (isset($_FILES['photo_de_profil']) && $_FILES['photo_de_profil']['error'] === UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['photo_de_profil']['tmp_name'];
        $original_name = basename($_FILES['photo_de_profil']['name']);
        $ext = pathinfo($original_name, PATHINFO_EXTENSION);
        $timestamp = time();
        $new_name = "profil_{$timestamp}.{$ext}";
        $upload_dir = __DIR__ . '/../images/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        if (move_uploaded_file($tmp_name, $upload_dir . $new_name)) {
            $photo_de_profil = $new_name;
        } else {
            $erreurs[] = "Erreur lors de l'upload de la photo de profil.";
        }
    }

    // Vérifier si l'email existe déjà
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM User WHERE email = ?');
    $stmt->execute([$email]);
    if ($stmt->fetchColumn() > 0) {
        $erreurs[] = "Cet email est déjà utilisé.";
    }

    // Récupérer l'id du rôle
    $role_nom = $role === 'prof' ? 'prof responsable' : 'etudiant';
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
        $stmt = $pdo->prepare('INSERT INTO User (numéro_étudiant, email, nom, prenom, photo_de_profil, mdp, Id_Role) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $numero_etudiant ?: null,
            $email,
            $nom,
            $prenom,
            $photo_de_profil,
            $mdp_hash,
            $id_role
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
    <?php endif; ?>
    <form action="" method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label class="form-label">Rôle</label><br>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="role" id="etudiant" value="etudiant" <?= (($_POST['role'] ?? '') !== 'prof') ? 'checked' : '' ?>>
                <label class="form-check-label" for="etudiant">Étudiant</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="role" id="prof" value="prof" <?= (($_POST['role'] ?? '') === 'prof') ? 'checked' : '' ?>>
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
