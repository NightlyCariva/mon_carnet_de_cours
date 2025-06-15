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

// Récupérer les infos de la matière (pour le titre)
$stmt = $pdo->prepare('SELECT nom FROM Matiere WHERE Id_Matiere = ?');
$stmt->execute([$id_matiere]);
$matiere = $stmt->fetch();
if (!$matiere) {
    header('Location: grid_matieres.php');
    exit();
}

// Gestion de l'envoi d'un message
$erreurs = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contenu'])) {
    $contenu = trim($_POST['contenu']);
    if ($contenu === '') {
        $erreurs[] = "Le message ne peut pas être vide.";
    } else {
        $stmt = $pdo->prepare('INSERT INTO Message (Contenu, Id_user, Id_Matiere, created_at) VALUES (?, ?, ?, NOW())');
        $stmt->execute([$contenu, $_SESSION['user_id'], $id_matiere]);
        header("Location: matiere_forum.php?id=$id_matiere");
        exit();
    }
}

// Récupérer tous les messages du forum de la matière
$stmt = $pdo->prepare('
    SELECT m.Contenu, m.created_at, u.nom, u.prenom, u.Id_user
    FROM Message m
    JOIN User u ON m.Id_user = u.Id_user
    WHERE m.Id_Matiere = ?
    ORDER BY m.created_at ASC
');
$stmt->execute([$id_matiere]);
$messages = $stmt->fetchAll();

include_once __DIR__ . '/../partials/header.php';
?>

<div class="container mt-4">
    <h2 class="mb-4">Forum de la matière : <?= htmlspecialchars($matiere['nom']) ?></h2>

    <div class="card mb-4" style="min-height: 400px;">
        <div class="card-body" style="background: #f8f9fa;">
            <?php if (empty($messages)): ?>
                <div class="text-center text-muted">Aucun message pour l'instant.</div>
            <?php else: ?>
                <?php foreach ($messages as $msg): ?>
                    <div class="d-flex mb-3 <?= $msg['Id_user'] == $_SESSION['user_id'] ? 'justify-content-end' : 'justify-content-start' ?>">
                        <div class="p-2 rounded shadow-sm"
                             style="max-width: 70%; background: <?= $msg['Id_user'] == $_SESSION['user_id'] ? '#d1e7dd' : '#fff' ?>;">
                            <div class="small text-muted mb-1">
                                <?= htmlspecialchars($msg['prenom'] . ' ' . $msg['nom']) ?> ·
                                <span><?= date('d/m/Y H:i', strtotime($msg['created_at'])) ?></span>
                            </div>
                            <div><?= nl2br(htmlspecialchars($msg['Contenu'])) ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($erreurs)): ?>
        <div class="alert alert-danger">
            <?php foreach ($erreurs as $err): ?>
                <div><?= htmlspecialchars($err) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form action="" method="POST" class="mt-3">
        <div class="mb-3">
            <textarea name="contenu" class="form-control" rows="3" placeholder="Votre message..." required></textarea>
        </div>
        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
            <button type="submit" class="btn btn-primary">Envoyer</button>
            <a href="matiere_details.php?id=<?= $id_matiere ?>" class="btn btn-secondary">Retour</a>
        </div>
    </form>
</div>

<?php include_once __DIR__ . '/../partials/footer.php'; ?>