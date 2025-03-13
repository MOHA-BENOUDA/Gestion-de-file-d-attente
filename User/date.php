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

/*----------------------------------------------
  Récupération des jours et heures bloqués
----------------------------------------------*/
// Jours bloqués : tableau associatif ['YYYY-MM-DD' => raison]
$blockedDays = [];
$sqlBlockedDays = "SELECT date_bloquee, raison FROM jours_bloques";
if ($result = $conn->query($sqlBlockedDays)) {
    while ($row = $result->fetch_assoc()) {
        $blockedDays[$row['date_bloquee']] = $row['raison'];
    }
    $result->free();
}

// Heures bloquées : tableau associatif ['YYYY-MM-DD' => [ liste d'heures bloquées au format 'HH:MM' ]
$blockedHours = [];
$sqlBlockedHours = "SELECT date_bloquee, heure_bloquee, raison FROM heures_bloquees";
if ($result = $conn->query($sqlBlockedHours)) {
    while ($row = $result->fetch_assoc()) {
        $date = $row['date_bloquee'];
        // Extraire l'heure au format "HH:MM" (exemple: "09:00")
        $heure = substr($row['heure_bloquee'], 0, 5);
        $blockedHours[$date][] = $heure;
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
        .blocked {
            background-color: #f8d7da;
            color: #721c24;
            font-weight: bold;
        }
    </style>
</head>
<body class="bg-light p-4">
    <div class="container">
        <h2 class="text-center mb-4">Sélectionner une date et une heure</h2>

        <form action="confirmation.php" method="POST">
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
                                // Pour chaque cellule, on vérifie d'abord si le jour est bloqué
                                $isDayBlocked = isset($blockedDays[$dateStr]);
                                // Vérification si l'heure est bloquée pour ce jour
                                $isHourBlocked = false;
                                if (isset($blockedHours[$dateStr])) {
                                    // Comparer le créneau horaire, ici on compare le format "HH:MM"
                                    if (in_array($heurePlage, $blockedHours[$dateStr])) {
                                        $isHourBlocked = true;
                                    }
                                }
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
            <div class="text-center mt-4">
                <button type="submit" class="btn btn-primary w-100">Confirmer</button>
            </div>
        </form>

        <!-- Navigation entre les semaines -->
        <div class="d-flex justify-content-between mt-4">
            <?php if ($weekOffset > 0): ?>
                <a href="date.php?week=<?= $weekOffset - 1 ?>" class="btn btn-secondary">Semaine Précédente</a>
            <?php endif; ?>
            <a href="date.php?week=<?= $weekOffset + 1 ?>" class="btn btn-secondary">Semaine Suivante</a>
        </div>
    </div>
</body>
</html>
