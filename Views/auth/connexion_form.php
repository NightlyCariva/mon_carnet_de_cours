<?php
// Backend connexion
require_once __DIR__ . '/../../BDD/pdo.php';
require_once __DIR__ . '/../../Models/user.php';

$erreurs = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $mdp = $_POST['mdp'] ?? '';

    if (empty($email)) {
        $erreurs[] = "L'email est obligatoire.";
    }
    if (empty($mdp)) {
        $erreurs[] = "Le mot de passe est obligatoire.";
    }

    if (empty($erreurs)) {
        // Vérifier les identifiants
        $stmt = $pdo->prepare('SELECT * FROM User WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($mdp, $user['mdp'])) {
            // Démarrer la session
            session_start();
            $_SESSION['user_id'] = $user['Id_user'];
            $_SESSION['user_role'] = $user['Id_Role'];
            $_SESSION['user_nom'] = $user['nom'];
            $_SESSION['user_prenom'] = $user['prenom'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_photo'] = $user['photo_de_profil'];

            header('Location: /MCC_taoufiq/index.php');
            exit();
        } else {
            $erreurs[] = "Email ou mot de passe incorrect";
        }
    }
}
?>
<?php include_once __DIR__ . '/../partials/header.php'; ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-body">
                    <h2 class="text-center mb-4">Connexion</h2>
                    
                    <?php if (!empty($erreurs)): ?>
                        <div class="alert alert-danger">
                            <?php foreach ($erreurs as $err): ?>
                                <div><?= htmlspecialchars($err) ?></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <form action="" method="POST">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required 
                                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label for="mdp" class="form-label">Mot de passe</label>
                            <input type="password" class="form-control" id="mdp" name="mdp" required>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Se connecter</button>
                        </div>
                    </form>
                    
                    <div class="text-center mt-3">
                        <p>Pas encore de compte ? <a href="inscription_form.php">S'inscrire</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/../partials/footer.php'; ?>
