<?php
session_start();
require '../includes/config.php'; // Doit initialiser la connexion $conn

if (!isset($_SESSION['cin'])) {
    header('Location: rdv.php'); 
    exit();
}

// Vérifier que le code unique est défini
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

// Générer 5 jours de la semaine
$jours = [];
for ($i = 0; $i < 5; $i++) {
    $jours[] = clone $lundi;
    $lundi->modify('+1 day');
}

// Plages horaires disponibles (format "HH:MM")
$heures = ['09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00'];

// Récupération des jours et heures bloqués
$blockedDays = [];
$sqlBlockedDays = "SELECT date_bloquee, raison FROM jours_bloques";
if ($result = $conn->query($sqlBlockedDays)) {
    while ($row = $result->fetch_assoc()) {
        $blockedDays[$row['date_bloquee']] = $row['raison'];
    }
    $result->free();
}

$blockedHours = [];
$sqlBlockedHours = "SELECT date_bloquee, heure_bloquee FROM heures_bloquees";
if ($result = $conn->query($sqlBlockedHours)) {
    while ($row = $result->fetch_assoc()) {
        $date = $row['date_bloquee'];
        $heure = substr($row['heure_bloquee'], 0, 5);
        $blockedHours[$date][] = $heure;
    }
    $result->free();
}
$capaciteRestante = [];
$sqlCapacite = "SELECT date_rdv, heure_rdv, capacite_max, 
                (SELECT COUNT(*) FROM rendez_vous WHERE rendez_vous.date_rdv = plages_horaires.date_rdv 
                AND rendez_vous.heure_rdv = plages_horaires.heure_rdv) AS nombre_reservations 
                FROM plages_horaires";

if ($result = $conn->query($sqlCapacite)) {
    while ($row = $result->fetch_assoc()) {
        $date = $row['date_rdv'];
        $heure = substr($row['heure_rdv'], 0, 5);
        $capaciteRestante[$date][$heure] = $row['capacite_max'] - $row['nombre_reservations'];
    }
    $result->free();
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choisir Date et Heure</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .modal-header {
            background-color: #007bff;
            color: white;
        }
        .blocked {
            background-color: #f8d7da;
            color: #721c24;
            font-weight: bold;
        }
    </style>
</head>
<body class="bg-light p-4">
    <div class="container">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                <a href="rdv.php" class="btn btn-primary" > Revenir</a>
                    <h5 class="modal-title">Sélectionner une date et une heure</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="traiter_rdv.php" method="POST">
                        <div class="table-responsive">
                            <table class="table table-bordered text-center">
                                <thead class="table-primary">
                                    <tr>
                                        <th>Date</th>
                                        <?php foreach ($heures as $heurePlage): ?>
                                            <th><?= $heurePlage ?> - <?= date("H", strtotime($heurePlage)) + 1 ?>h</th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    foreach ($jours as $jour): 
                                        $dateStr = $jour->format('Y-m-d');
                                    ?>
                                    <tr>
                                        <td>
                                            <strong><?= $jour->format('d/m/Y') ?></strong>
                                            <?php if (isset($blockedDays[$dateStr])): ?>
                                                <br><small class="text-danger">Bloqué: <?= htmlspecialchars($blockedDays[$dateStr]) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <?php 
                                        foreach ($heures as $heurePlage): 
                                            $isDayBlocked = isset($blockedDays[$dateStr]);
                                            $isHourBlocked = isset($blockedHours[$dateStr]) && in_array($heurePlage, $blockedHours[$dateStr]);
                                        ?>
                                            <td class="<?= ($isDayBlocked || $isHourBlocked) ? 'blocked' : '' ?>">
                                                <?php if ($jour < $currentDate): ?>
                                                    <span class="text-muted">Indisponible</span>
                                                <?php else: ?>
                                                    <?php if ($isDayBlocked): ?>
                                                        <span class="text-danger">Jour Bloqué</span>
                                                    <?php elseif ($isHourBlocked): ?>
                                                        <span class="text-danger">Bloqué</span>
                                                    <?php else: ?>
                                                        <input type="radio" name="date_heure" value="<?= htmlspecialchars($dateStr . ' ' . $heurePlage . ':00') ?>" required>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php if ($isDayBlocked): ?>
    <span class="text-danger">Jour Bloqué</span>
<?php elseif ($isHourBlocked): ?>
    <span class="text-danger">Bloqué</span>
<?php elseif (isset($capaciteRestante[$dateStr][$heurePlage]) && $capaciteRestante[$dateStr][$heurePlage] <= 0): ?>
    <span class="text-danger">Complet</span>
<?php else: ?>
    <input type="radio" name="date_heure" value="<?= htmlspecialchars($dateStr . ' ' . $heurePlage . ':00') ?>" required>
<?php endif; ?>

                        <div class="text-center mt-3">
                            <button type="submit" class="btn btn-success w-100">Confirmer</button>
                        </div>
                    </form>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <?php if ($weekOffset > 0): ?>
                        <a href="date.php?week=<?= $weekOffset - 1 ?>" class="btn btn-secondary">Semaine Précédente</a>
                    <?php endif; ?>
                    <a href="date.php?week=<?= $weekOffset + 1 ?>" class="btn btn-secondary">Semaine Suivante</a>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
