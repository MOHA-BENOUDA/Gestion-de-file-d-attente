<?php
session_start();
require '../includes/config.php';

if (!isset($_SESSION['cin'])) {
    header('Location: rdv.php'); 
    exit();
}

// Vérifier que le code unique est bien défini
if (!isset($_SESSION['code_unique'])) {
    header('Location: rdv.php');
    exit();
}
$code_unique = $_SESSION['code_unique'];

// Définir le fuseau horaire
date_default_timezone_set('Europe/Paris');

// Récupérer la date actuelle
$currentDate = new DateTime();

// Gestion du décalage de semaine
$weekOffset = isset($_GET['week']) ? (int)$_GET['week'] : 0;
$displayDate = clone $currentDate;
$displayDate->modify("+$weekOffset week");

// Trouver le lundi de la semaine affichée
$lundi = clone $displayDate;
$lundi->modify('-' . ($lundi->format('N') - 1) . ' days');

// Générer les 5 jours de la semaine
$jours = [];
for ($i = 0; $i < 5; $i++) {
    $jours[] = clone $lundi;
    $lundi->modify('+1 day');
}

// Heures des rendez-vous
$heures = ['09-10', '10-11', '11-12', '12-13', '13-14', '14-15', '15-16'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choisir Date et Heure</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center mb-4">Sélectionner une date et une heure</h2>

        <form action="confirmation.php" method="POST">
            <div class="table-responsive">
                <table class="table table-bordered text-center">
                    <thead>
                        <tr>
                            <th>Heures</th>
                            <?php foreach ($jours as $jour): ?>
                                <th class="<?= $jour < $currentDate ? 'bg-secondary text-white' : '' ?>">
                                    <?= $jour->format('d-m-Y') ?>
                                </th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($heures as $heure): ?>
                            <tr>
                                <td><strong><?= $heure ?>h</strong></td>
                                <?php foreach ($jours as $jour): ?>
                                    <td>
                                        <?php if ($jour >= $currentDate): ?>
                                            <!-- Utilisation du séparateur "|" pour éviter les conflits avec la date -->
                                            <input type="radio" name="horaire" value="<?= htmlspecialchars($jour->format('Y-m-d') . '|' . explode('-', $heure)[0]) ?>" required>
                                        <?php else: ?>
                                            <span class="text-muted">Indisponible</span>
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <button type="submit" class="btn btn-primary w-100 mt-3">Valider</button>
        </form>

        <div class="d-flex justify-content-between mt-3">
            <?php if ($weekOffset > 0): ?>
                <a href="date.php?week=<?= $weekOffset - 1 ?>" class="btn btn-secondary">Semaine Précédente</a>
            <?php endif; ?>
            <a href="date.php?week=<?= $weekOffset + 1 ?>" class="btn btn-secondary">Semaine Suivante</a>
        </div>
    </div>
</body>
</html>
