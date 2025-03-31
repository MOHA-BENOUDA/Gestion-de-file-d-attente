<?php
session_start();
// Ici, vérifier que l'utilisateur est administrateur (ex: via $_SESSION['admin'])

// Connexion à la base de données
require '../includes/config.php'; // Doit initialiser la connexion $conn

// Traitement des formulaires d'ajout de blocage
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    if ($_POST['action'] === 'ajouter_jour') {
        $date_bloquee = $conn->real_escape_string($_POST['date_bloquee']);
        $raison = $conn->real_escape_string($_POST['raison']);
        $sql = "INSERT INTO jours_bloques (date_bloquee, raison) VALUES ('$date_bloquee', '$raison')";
        $conn->query($sql);
    }
    if ($_POST['action'] === 'ajouter_heure') {
        $date_bloquee = $conn->real_escape_string($_POST['date_bloquee']);
        $heure_bloquee = $conn->real_escape_string($_POST['heure_bloquee']);
        $raison = $conn->real_escape_string($_POST['raison']);
        $sql = "INSERT INTO heures_bloquees (date_bloquee, heure_bloquee, raison) VALUES ('$date_bloquee', '$heure_bloquee', '$raison')";
        $conn->query($sql);
    }
    header("Location: gestion_blocages.php");
    exit();
}

// Récupérer les jours bloqués
$joursBloques = [];
$sqlJours = "SELECT * FROM jours_bloques ORDER BY date_bloquee ASC";
if ($result = $conn->query($sqlJours)) {
    while ($row = $result->fetch_assoc()) {
        $blockedDays[$row['date_bloquee']] = $row['raison'];
    }
    $result->free();
}

// Récupérer les heures bloquées
$blockedHours = [];
$sqlBlockedHours = "SELECT date_bloquee, heure_bloquee, raison FROM heures_bloquees ORDER BY date_bloquee ASC, heure_bloquee ASC";
if ($result = $conn->query($sqlBlockedHours)) {
    while ($row = $result->fetch_assoc()) {
        $date = $row['date_bloquee'];
        $heure = substr($row['heure_bloquee'], 0, 2); // Ex : '09'
        $blockedHours[$date][] = ['heure' => $heure, 'raison' => $row['raison']];
    }
    $result->free();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Administration des Blocages</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="d-flex">
    <!-- Sidebar -->
    <div class="bg-dark text-white p-3" style="width: 250px; height: 100vh; position: fixed;">
        <?php include 'sidebar.php'; ?>
    </div>

    <!-- Contenu principal -->
    <div class="flex-grow-1 p-4" style="margin-left: 260px;">
        <div class="container bg-white p-5 rounded shadow">
            <h1 class="text-center mb-4">Administration des Blocages</h1>

            <!-- Formulaire pour ajouter un jour bloqué -->
            <h2 class="mb-3">Ajouter un Jour Bloqué</h2>
            <form method="POST" action="gestion_blocages.php" class="mb-4">
                <input type="hidden" name="action" value="ajouter_jour">
                <div class="mb-3">
                    <label class="form-label">Date bloquée :</label>
                    <input type="date" name="date_bloquee" required class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">Raison :</label>
                    <input type="text" name="raison" required class="form-control">
                </div>
                <button type="submit" class="btn btn-primary">Ajouter Jour Bloqué</button>
            </form>

            <hr class="my-4">

            <!-- Affichage des blocages -->
            <h2 class="text-center mb-3">Liste des Jours Bloqués</h2>
            <ul class="list-group mb-4">
                <?php
                $sqlJours = "SELECT * FROM jours_bloques ORDER BY date_bloquee ASC";
                $resultJours = $conn->query($sqlJours);
                if ($resultJours && $resultJours->num_rows > 0) {
                    while ($row = $resultJours->fetch_assoc()) {
                        echo "<li class='list-group-item'>" . htmlspecialchars($row['date_bloquee']) . " - " . htmlspecialchars($row['raison']) . "</li>";
                    }
                } else {
                    echo "<li class='list-group-item'>Aucun jour bloqué.</li>";
                }
                ?>
            </ul>
        </div>
    </div>
</div>

</body>

</html>
<?php $conn->close(); ?>
