<?php
session_start();
require '../includes/config.php'; // Connexion à la BD

// Vérifier que l'utilisateur a bien rempli les formulaires précédents
if (!isset($_SESSION['cin'], $_SESSION['nom'], $_SESSION['prenom'], $_SESSION['email'], $_SESSION['telephone'], $_SESSION['code_unique'])) {
    header('Location: rdv.php');
    exit();
}

// Vérifier que la date et l'heure ont bien été sélectionnées
if (!isset($_POST['horaire'])) {
    header('Location: date.php');
    exit();
}

// Récupérer les informations de la session
$cin = $_SESSION['cin'];
$nom = $_SESSION['nom'];
$prenom = $_SESSION['prenom'];
$email = $_SESSION['email'];
$telephone = $_SESSION['telephone'];
$code_unique = $_SESSION['code_unique'];

// Utiliser le séparateur '|' pour extraire la date et l'heure
$parts = explode('|', $_POST['horaire']);
if(count($parts) === 2) {
    $date_rdv = $parts[0]; // Format "YYYY-MM-DD"
    $heure_rdv = $parts[1] . ":00:00"; // Format "HH:00:00"
} else {
    die("Format de date/heure incorrect.");
}

$stmt = $conn->prepare("INSERT INTO rendez_vous (cin, nom, prenom, email, telephone, date_rdv, heure_rdv, code_unique, etat, date_creation) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'En attente', NOW())");

$stmt->bind_param("ssssssss", $cin, $nom, $prenom, $email, $telephone, $date_rdv, $heure_rdv, $code_unique);

if ($stmt->execute()) {
    // Rendez-vous enregistré avec succès, on affiche la confirmation
    $message = "Votre rendez-vous a été confirmé avec succès.";
} else {
    // Erreur lors de l'insertion
    $message = "Erreur lors de l'enregistrement du rendez-vous. Veuillez réessayer.";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation du Rendez-vous</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center mb-4">Confirmation du Rendez-vous</h2>

        <div class="alert alert-info"><?= $message ?></div>

        <?php if ($stmt->affected_rows > 0): ?>
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title text-center">Détails du Rendez-vous</h5>
                    <p><strong>CIN :</strong> <?= htmlspecialchars($cin) ?></p>
                    <p><strong>Nom :</strong> <?= htmlspecialchars($nom) ?></p>
                    <p><strong>Prénom :</strong> <?= htmlspecialchars($prenom) ?></p>
                    <p><strong>Email :</strong> <?= htmlspecialchars($email) ?></p>
                    <p><strong>Téléphone :</strong> <?= htmlspecialchars($telephone) ?></p>
                    <p><strong>Date :</strong> <?= htmlspecialchars($date_rdv) ?></p>
                    <p><strong>Heure :</strong> <?= htmlspecialchars($heure_rdv) ?>h</p>
                    <p><strong>Code Unique :</strong> <span class="badge bg-primary"><?= htmlspecialchars($code_unique) ?></span></p>
                </div>
            </div>
        <?php endif; ?>

        <div class="text-center mt-4">
            <a href="rdv.php" class="btn btn-secondary">Retour à l'accueil</a>
        </div>
    </div>
</body>
</html>
