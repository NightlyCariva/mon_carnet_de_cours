<?php
// Vérifier si l'utilisateur est connecté
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /MCC_taoufiq/Views/auth/connexion_form.php');
    exit();
}

require_once __DIR__ . '/../../BDD/pdo.php';

// Récupérer les informations de l'utilisateur
$stmt = $pdo->prepare('
    SELECT u.*, r.role as role_nom, f.Nom_filière 
    FROM User u 
    LEFT JOIN Role r ON u.Id_Role = r.Id_Role 
    LEFT JOIN Filière f ON u.Id_Filière = f.Id_Filière 
    WHERE u.Id_user = ?
');
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

include_once __DIR__ . '/../partials/header.php';
?>

<div class="container mt-5">
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <img src="/MCC_taoufiq/Views/images/<?= htmlspecialchars($user['photo_de_profil']) ?>" 
                         class="rounded-circle mb-3" 
                         style="width: 150px; height: 150px; object-fit: cover;"
                         alt="Photo de profil">
                    <h4><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></h4>
                    <p class="text-muted"><?= htmlspecialchars($user['role_nom']) ?></p>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-4">Informations personnelles</h5>
                    
                    <div class="row mb-3">
                        <div class="col-sm-3">
                            <p class="mb-0 text-muted">Email</p>
                        </div>
                        <div class="col-sm-9">
                            <p class="mb-0"><?= htmlspecialchars($user['email']) ?></p>
                        </div>
                    </div>

                    <?php if ($user['numéro_étudiant']): ?>
                    <div class="row mb-3">
                        <div class="col-sm-3">
                            <p class="mb-0 text-muted">Numéro étudiant</p>
                        </div>
                        <div class="col-sm-9">
                            <p class="mb-0"><?= htmlspecialchars($user['numéro_étudiant']) ?></p>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ($user['Nom_filière']): ?>
                    <div class="row mb-3">
                        <div class="col-sm-3">
                            <p class="mb-0 text-muted">Filière</p>
                        </div>
                        <div class="col-sm-9">
                            <p class="mb-0"><?= htmlspecialchars($user['Nom_filière']) ?></p>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="row mb-3">
                        <div class="col-sm-3">
                            <p class="mb-0 text-muted">Rôle</p>
                        </div>
                        <div class="col-sm-9">
                            <p class="mb-0"><?= htmlspecialchars($user['role_nom']) ?></p>
                        </div>
                    </div>

                    <div class="mt-4">
                        <a href="profil_form.php" class="btn btn-primary">Modifier le profil</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/../partials/footer.php'; ?>
