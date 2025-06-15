<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MCC</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="/MCC_taoufiq/Views/CSS/style.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
  <div class="container-fluid">
    <a class="navbar-brand" href="/MCC_taoufiq/Views/emploie/affichage_calendrier.php">MCC</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <?php if (isset($_SESSION['user_id'])): ?>
          <!-- Liens pour utilisateur connecté -->
          <li class="nav-item">
            <a class="nav-link" href="/MCC_taoufiq/Views/profil/user_profil.php">Profil</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/MCC_taoufiq/Views/emploie/affichage_calendrier.php">Emploi</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/MCC_taoufiq/Views/matiere/grid_matieres.php">Matières</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/MCC_taoufiq/Views/auth/logout.php">Déconnexion</a>
          </li>
        <?php else: ?>
          <!-- Liens pour visiteur non connecté -->
          <li class="nav-item">
            <a class="nav-link" href="/MCC_taoufiq/Views/auth/connexion_form.php">Connexion</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/MCC_taoufiq/Views/auth/inscription_form.php">Inscription</a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
<!-- Le contenu principal commence ici -->
<main>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
