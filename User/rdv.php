<?php
require_once "../includes/config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = $_POST["nom"];
    $email = $_POST["email"];
    $telephone = $_POST["telephone"];
    $date_rdv = $_POST["date_rdv"];
    $heure_rdv = $_POST["heure_rdv"];
    $code_unique = uniqid(); // Génère un code unique
 
    // Vérifier si la date et l'heure ne sont pas bloquées
    $sql = "SELECT * FROM jours_bloques WHERE date_bloquee = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$date_rdv]);
    if ($stmt->rowCount() > 0) {
        die("Cette date est bloquée. Veuillez choisir une autre date.");
    }

    $sql = "SELECT * FROM heures_bloquees WHERE date_bloquee = ? AND heure_bloquee = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$date_rdv, $heure_rdv]);
    if ($stmt->rowCount() > 0) {
        die("Cet horaire est bloqué. Veuillez choisir un autre créneau.");
    }

    // Ajouter le rendez-vous
    $sql = "INSERT INTO rendez_vous (nom, email, telephone, date_rdv, heure_rdv, code_unique) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nom, $email, $telephone, $date_rdv, $heure_rdv, $code_unique]);

    $id_rdv = $pdo->lastInsertId(); // Récupérer l'ID du RDV

    // Ajouter à la file d'attente
    $sql = "SELECT COUNT(*) AS position FROM file_attente";
    $stmt = $pdo->query($sql);
    $position = $stmt->fetch()["position"] + 1;

    $sql = "INSERT INTO file_attente (id_rdv, position) VALUES (?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_rdv, $position]);

    echo "Votre rendez-vous a été enregistré ! Votre code unique est : <strong>$code_unique</strong>";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Prendre un Rendez-vous</title>
</head>
<body>
    <h1>Prendre un Rendez-vous</h1>
    <form method="POST">
        <label>Nom :</label>
        <input type="text" name="nom" required>
        
        <label>Email :</label>
        <input type="email" name="email" required>

        <label>Téléphone :</label>
        <input type="text" name="telephone" required>

        <label>Date du Rendez-vous :</label>
        <input type="date" name="date_rdv" required>

        <label>Heure du Rendez-vous :</label>
        <input type="time" name="heure_rdv" required>

        <button type="submit">Valider</button>
    </form>
</body>
</html>
