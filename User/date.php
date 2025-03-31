<?php
session_start();
require '../includes/config.php';

if (!isset($_SESSION['cin']) || !isset($_SESSION['code_unique'])) {
    header('Location: rdv.php');
    exit();
}

$code_unique = $_SESSION['code_unique'];
date_default_timezone_set('Europe/Paris');
$currentDate = new DateTime(); // Date et heure actuelles
$currentHour = (int)$currentDate->format('H'); // Heure actuelle (ex: 18)
$currentMinute = (int)$currentDate->format('i'); // Minutes actuelles

// Définition des heures disponibles
$heures = ['09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00'];

// Calcul des heures passées pour aujourd'hui
$heuresPassees = [];
foreach ($heures as $heurePlage) {
    list($h, $m) = explode(':', $heurePlage);
    $heurePlageEnMinutes = (int)$h * 60 + (int)$m;
    $currentEnMinutes = $currentHour * 60 + $currentMinute;
    if ($heurePlageEnMinutes <= $currentEnMinutes) {
        $heuresPassees[] = $heurePlage;
    }
}

$weekOffset = isset($_GET['week']) ? (int)$_GET['week'] : 0;
$displayDate = clone $currentDate;
$displayDate->modify("+$weekOffset week");

// Limite maximale de deux semaines
$maxWeekOffset = 2; // Deux semaines après la semaine actuelle

$lundi = clone $displayDate;
$lundi->modify('-' . ($lundi->format('N') - 1) . ' days');

$jours = [];
for ($i = 0; $i < 5; $i++) {
    $jours[] = clone $lundi;
    $lundi->modify('+1 day');
}

// Récupération des jours bloqués
$blockedDays = [];
$sqlBlockedDays = "SELECT date_bloquee, raison FROM jours_bloques";
$result = $conn->query($sqlBlockedDays);
while ($row = $result->fetch_assoc()) {
    $blockedDays[$row['date_bloquee']] = $row['raison'];
}
$result->free();

// Récupération des heures bloquées
$blockedHours = [];
$sqlBlockedHours = "SELECT date_bloquee, heure_bloquee FROM heures_bloquees";
$result = $conn->query($sqlBlockedHours);
while ($row = $result->fetch_assoc()) {
    $blockedHours[$row['date_bloquee']][] = substr($row['heure_bloquee'], 0, 5);
}
$result->free();

// Récupération de la capacité des créneaux
$resultCapacite = $conn->query("SELECT capacite_max FROM capacite_globale LIMIT 1");
$capaciteGlobale = ($resultCapacite && $resultCapacite->num_rows > 0) ? $resultCapacite->fetch_assoc()['capacite_max'] : 5;

$capaciteRestante = [];
$sqlCapacite = "SELECT date_rdv, heure_rdv, COUNT(*) AS nombre_reservations FROM rendez_vous GROUP BY date_rdv, heure_rdv";
$result = $conn->query($sqlCapacite);
while ($row = $result->fetch_assoc()) {
    $date = $row['date_rdv'];
    $heure = substr($row['heure_rdv'], 0, 5);
    $capaciteRestante[$date][$heure] = $capaciteGlobale - $row['nombre_reservations'];
}
$result->free();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Choisir Date et Heure</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f6f9; }
        .container { max-width: 900px; margin-top: 30px; }
        .modal-content { border-radius: 15px; box-shadow: 0px 4px 10px rgba(0,0,0,0.1); }
        .modal-header { background-color: #007bff; color: white; border-top-left-radius: 15px; border-top-right-radius: 15px; }
        .btn-primary, .btn-success, .btn-secondary { border-radius: 8px; }
        .blocked { background-color: #f8d7da; color: #721c24; font-weight: bold; }
        .full { background-color: #ffc107; color: black; font-weight: bold; }
        .disabled { background-color: #dcdcdc; color: #6c757d; pointer-events: none; }
        table th, table td { vertical-align: middle; }
        input[type="radio"] { transform: scale(1.2); }
    </style>
</head>
<body>
<div class="container">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <a href="rdv.php" class="btn btn-light">⬅ Revenir</a>
                <h5 class="modal-title">Sélectionner une date et une heure</h5>
            </div>
            <div class="modal-body">
                <form action="traiter_rdv.php" method="POST">
                    <table class="table table-bordered text-center bg-white">
                        <thead class="table-primary">
                            <tr>
                                <th>Date</th>
                                <?php foreach ($heures as $heurePlage): ?>
                                    <th><?= $heurePlage ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($jours as $jour): ?>
                            <tr>
                                <td>
                                    <strong><?= $jour->format('d/m/Y') ?></strong>
                                    <?php if ($jour < $currentDate): ?>
                                        <br><small class="text-muted">Indisponible</small>
                                    <?php elseif (isset($blockedDays[$jour->format('Y-m-d')])): ?>
                                        <br><small class="text-danger">Bloqué: <?= htmlspecialchars($blockedDays[$jour->format('Y-m-d')]) ?></small>
                                    <?php endif; ?>
                                </td>
                                <?php foreach ($heures as $heurePlage): ?>
                                    <td class="<?php 
                                        $dateStr = $jour->format('Y-m-d');
                                        echo ($jour < $currentDate) ? 'disabled' : (
                                            ($jour == $currentDate && in_array($heurePlage, $heuresPassees)) ? 'disabled' : (
                                                isset($blockedDays[$dateStr]) ? 'blocked' : (
                                                    isset($blockedHours[$dateStr]) && in_array($heurePlage, $blockedHours[$dateStr]) ? 'blocked' : (
                                                        isset($capaciteRestante[$dateStr][$heurePlage]) && $capaciteRestante[$dateStr][$heurePlage] <= 0 ? 'full' : ''
                                                    )
                                                )
                                            )
                                        );
                                    ?>">
                                        <?php if ($jour <=$currentDate): ?>
                                            <span class="text-muted">Indisponible</span>
                                        <?php elseif ($jour == $currentDate && in_array($heurePlage, $heuresPassees)): ?>
                                            <span class="text-muted">Passé</span>
                                        <?php elseif (isset($blockedDays[$dateStr])): ?>
                                            <span class="text-danger">Jour Bloqué</span>
                                        <?php elseif (isset($blockedHours[$dateStr]) && in_array($heurePlage, $blockedHours[$dateStr])): ?>
                                            <span class="text-danger">Bloqué</span>
                                        <?php elseif (isset($capaciteRestante[$dateStr][$heurePlage]) && $capaciteRestante[$dateStr][$heurePlage] <= 0): ?>
                                            <span class="text-info">Complet</span>
                                        <?php else: ?>
                                            <input type="radio" name="date_heure" value="<?= htmlspecialchars($dateStr . ' ' . $heurePlage . ':00') ?>" required>
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <button type="submit" class="btn btn-success w-100">Confirmer</button>
                </form>
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <?php if ($weekOffset > 0): ?>
                    <a href="date.php?week=<?= $weekOffset - 1 ?>" class="btn btn-secondary">Semaine Précédente</a>
                <?php else: ?>
                    <button class="btn btn-secondary disabled">Semaine Précédente</button>
                <?php endif; ?>
                <?php if ($weekOffset < $maxWeekOffset): ?>
                    <a href="date.php?week=<?= $weekOffset + 1 ?>" class="btn btn-secondary">Semaine Suivante</a>
                <?php else: ?>
                    <button class="btn btn-secondary disabled">Semaine Suivante</button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>