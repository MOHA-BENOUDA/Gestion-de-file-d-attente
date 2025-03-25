<?php
session_start();
require '../includes/config.php';

// Récupérer la capacité globale
$capaciteGlobale = 5;
$resultCapacite = $conn->query("SELECT capacite_max FROM capacite_globale LIMIT 1");
if ($resultCapacite && $resultCapacite->num_rows > 0) {
    $row = $resultCapacite->fetch_assoc();
    $capaciteGlobale = $row['capacite_max'];
}

// Mise à jour de la capacité globale
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['modifier_capacite_globale'])) {
    $nouvelleCapacite = intval($_POST['capacite_max']);
    $conn->query("UPDATE capacite_globale SET capacite_max = $nouvelleCapacite");
    header("Location: gestion_creneaux.php");
    exit();
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Capacités</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-4">
<div class="container">
    <a href="dashboard.php" class="btn btn-primary mb-3">⬅ Revenir</a>
    <h1 class="text-center mb-4">⚙ Gestion des Capacités</h1>

    <div class="card">
        <div class="card-body">
            <h4 class="card-title">🔧 Capacité Globale</h4>
            <p>Capacité actuelle : <strong><?= $capaciteGlobale ?> personnes par heure</strong></p>
            <form method="POST">
                <input type="hidden" name="modifier_capacite_globale">
                <div class="mb-3">
                    <label class="form-label">Nouvelle Capacité :</label>
                    <input type="number" name="capacite_max" class="form-control" required min="1" value="<?= $capaciteGlobale ?>">
                </div>
                <button type="submit" class="btn btn-primary">Modifier</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
<?php $conn->close(); ?>
