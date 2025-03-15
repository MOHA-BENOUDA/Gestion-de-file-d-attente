<?php
session_start();
require '../includes/config.php'; // Connexion à la base de données

// Vérifier la connexion à la base de données
if (!$conn) {
    die("Erreur de connexion : " . mysqli_connect_error());
}

// Récupérer la capacité maximale par défaut
$sqlDefault = "SELECT valeur FROM configuration WHERE cle = 'capacite_max_defaut'";
$resultDefault = $conn->query($sqlDefault);
$capacite_defaut = ($resultDefault && $resultDefault->num_rows > 0) ? (int)$resultDefault->fetch_assoc()['valeur'] : 10;

// Mise à jour de la capacité maximale par défaut
if (isset($_POST['update_default_capacite'])) {
    $new_capacite = (int)$_POST['default_capacite'];
    if ($new_capacite > 0) {
        $sqlUpdateDefault = "INSERT INTO configuration (cle, valeur) VALUES ('capacite_max_defaut', ?) ON DUPLICATE KEY UPDATE valeur = ?";
        $stmtUpdateDefault = $conn->prepare($sqlUpdateDefault);
        $stmtUpdateDefault->bind_param("ii", $new_capacite, $new_capacite);
        $stmtUpdateDefault->execute();
        $_SESSION['message'] = "Capacité maximale par défaut mise à jour avec succès.";
        header("Location: gestion_creneaux.php");
        exit;
    }
}

// Traitement du formulaire pour modifier un jour spécifique
if (isset($_POST['update_day_capacite'])) {
    $date = $_POST['date'];
    $capacite = (int)$_POST['capacite'];
    if (!empty($date) && $capacite > 0) {
        $sqlUpdateDay = "UPDATE plages_horaires SET capacite_max = ? WHERE date_rdv = ?";
        $stmtUpdateDay = $conn->prepare($sqlUpdateDay);
        $stmtUpdateDay->bind_param("is", $capacite, $date);
        $stmtUpdateDay->execute();
        $_SESSION['message'] = "Capacité mise à jour pour la date $date.";
        header("Location: gestion_creneaux.php");
        exit;
    }
}

// Récupérer les créneaux existants
$sql = "SELECT id, date_rdv, heure_rdv, capacite_max, nombre_reservations FROM plages_horaires ORDER BY date_rdv, heure_rdv";
$result = $conn->query($sql);
$creneaux = ($result) ? $result->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des créneaux</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-4">
    <div class="container">
        <h2 class="text-center mb-4">Gérer les créneaux horaires</h2>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success">
                <?= $_SESSION['message']; unset($_SESSION['message']); ?>
            </div>
        <?php endif; ?>

        <h3>Capacité maximale par défaut</h3>
        <form method="POST" class="mb-4">
            <div class="row">
                <div class="col-md-6">
                    <label for="default_capacite" class="form-label">Capacité par défaut :</label>
                    <input type="number" name="default_capacite" id="default_capacite" class="form-control" value="<?= $capacite_defaut ?>" min="1" required>
                </div>
            </div>
            <button type="submit" name="update_default_capacite" class="btn btn-primary mt-3 w-100">Mettre à jour</button>
        </form>

        <h3>Modifier la capacité pour un jour spécifique</h3>
        <form method="POST" class="mb-4">
            <div class="row">
                <div class="col-md-6">
                    <label for="date" class="form-label">Date :</label>
                    <input type="date" name="date" id="date" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label for="capacite" class="form-label">Nouvelle capacité :</label>
                    <input type="number" name="capacite" id="capacite" class="form-control" min="1" required>
                </div>
            </div>
            <button type="submit" name="update_day_capacite" class="btn btn-warning mt-3 w-100">Modifier</button>
        </form>

        <h3>Créneaux existants</h3>
        <table class="table table-bordered text-center">
            <thead class="table-dark">
                <tr>
                    <th>Date</th>
                    <th>Heure</th>
                    <th>Capacité max</th>
                    <th>Réservations</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($creneaux as $creneau): ?>
                    <tr>
                        <td><?= htmlspecialchars($creneau['date_rdv']) ?></td>
                        <td><?= htmlspecialchars($creneau['heure_rdv']) ?></td>
                        <td><?= htmlspecialchars($creneau['capacite_max']) ?></td>
                        <td><?= htmlspecialchars($creneau['nombre_reservations']) ?></td>
                        <td>
                            <a href="delete_creneau.php?id=<?= $creneau['id'] ?>" class="btn btn-sm btn-danger">Supprimer</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
