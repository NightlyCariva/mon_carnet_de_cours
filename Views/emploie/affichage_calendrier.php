<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /MCC_taoufiq/Views/auth/connexion_form.php');
    exit();
}
require_once __DIR__ . '/../../BDD/pdo.php';

// Récupérer le rôle de l'utilisateur
$stmt = $pdo->prepare('SELECT r.role, u.Id_Filière FROM User u JOIN Role r ON u.Id_Role = r.Id_Role WHERE u.Id_user = ?');
$stmt->execute([$_SESSION['user_id']]);
list($userRole, $id_filiere) = $stmt->fetch(PDO::FETCH_NUM);

// Déterminer le mois/année à afficher
$month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');
$year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
$firstDayOfMonth = mktime(0, 0, 0, $month, 1, $year);
$daysInMonth = date('t', $firstDayOfMonth);
$startDay = date('N', $firstDayOfMonth); // 1 (lundi) à 7 (dimanche)

// Récupérer les cours à afficher
if (strtolower($userRole) === 'etudiant' || strtolower($userRole) === 'étudiant') {
    // Cours de la filière de l'étudiant
    $stmt = $pdo->prepare('
        SELECT c.rdv, c.duree, m.nom as matiere, f.Nom_filière
        FROM Cours c
        JOIN Matiere m ON c.Id_Matiere = m.Id_Matiere
        JOIN Filière f ON m.Id_Matiere IN (SELECT Id_Matiere FROM Appartenir WHERE Id_Filière = f.Id_Filière)
        WHERE f.Id_Filière = ?
          AND MONTH(c.rdv) = ? AND YEAR(c.rdv) = ?
        ORDER BY c.rdv
    ');
    $stmt->execute([$id_filiere, $month, $year]);
} else {
    // Cours programmés par le responsable
    $stmt = $pdo->prepare('
        SELECT c.rdv, c.duree, m.nom as matiere, f.Nom_filière
        FROM Cours c
        JOIN Matiere m ON c.Id_Matiere = m.Id_Matiere
        JOIN Filière f ON m.Id_Matiere IN (SELECT Id_Matiere FROM Appartenir WHERE Id_Filière = f.Id_Filière)
        WHERE c.Id_user = ?
          AND MONTH(c.rdv) = ? AND YEAR(c.rdv) = ?
        ORDER BY c.rdv
    ');
    $stmt->execute([$_SESSION['user_id'], $month, $year]);
}
$cours = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Organiser les cours par jour
$coursByDay = [];
foreach ($cours as $c) {
    $day = (int)date('j', strtotime($c['rdv']));
    $coursByDay[$day][] = $c;
}

include_once __DIR__ . '/../partials/header.php';
?>

<div class="container mt-4">
    <h2 class="mb-4">Calendrier des cours - <?= strftime('%B %Y', $firstDayOfMonth) ?></h2>
    <div class="d-flex justify-content-between mb-3">
        <a href="?month=<?= $month == 1 ? 12 : $month - 1 ?>&year=<?= $month == 1 ? $year - 1 : $year ?>" class="btn btn-outline-secondary">&lt; Mois précédent</a>
        <?php if (strtolower($userRole) === 'professeur responsable'): ?>
            <a href="ajout_cours_form.php" class="btn btn-primary">Ajouter un cours</a>
        <?php endif; ?>
        <a href="?month=<?= $month == 12 ? 1 : $month + 1 ?>&year=<?= $month == 12 ? $year + 1 : $year ?>" class="btn btn-outline-secondary">Mois suivant &gt;</a>
    </div>
    <table class="table table-bordered text-center align-middle">
        <thead>
            <tr>
                <th>Lun</th><th>Mar</th><th>Mer</th><th>Jeu</th><th>Ven</th><th>Sam</th><th>Dim</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $day = 1;
        for ($row = 0; $day <= $daysInMonth; $row++) {
            echo "<tr>";
            for ($col = 1; $col <= 7; $col++) {
                if (($row === 0 && $col < $startDay) || $day > $daysInMonth) {
                    echo "<td></td>";
                } else {
                    echo "<td style='min-width:120px;'>";
                    echo "<div class='fw-bold'>$day</div>";
                    if (isset($coursByDay[$day])) {
                        foreach ($coursByDay[$day] as $c) {
                            echo "<div class='border rounded p-1 mb-1 bg-light'>";
                            echo "<div><strong>" . htmlspecialchars($c['matiere']) . "</strong></div>";
                            echo "<div class='small text-muted'>" . htmlspecialchars($c['Nom_filière']) . "</div>";
                            echo "<div class='small'>Heure : " . date('H:i', strtotime($c['rdv'])) . "</div>";
                            echo "<div class='small'>Durée : " . htmlspecialchars($c['duree']) . " min</div>";
                            echo "</div>";
                        }
                    }
                    echo "</td>";
                    $day++;
                }
            }
            echo "</tr>";
        }
        ?>
        </tbody>
    </table>
</div>

<?php include_once __DIR__ . '/../partials/footer.php'; ?>