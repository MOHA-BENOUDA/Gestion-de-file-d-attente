<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gestion_rdv";

// Connexion à la base de données
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
}

// Sélection du premier patient en attente
$sql = "SELECT * FROM file_attente WHERE etat = 'en attente' ORDER BY position ASC LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $patient = $result->fetch_assoc();
    $id = $patient['id'];

    // Mise à jour de l'état du patient
    $sqlUpdate = "UPDATE file_attente SET etat = 'appelé' WHERE id = $id";
    if ($conn->query($sqlUpdate) === TRUE) {
        echo "Le patient a été appelé.";
    } else {
        echo "Erreur lors de la mise à jour : " . $conn->error;
    }
} else {
    echo "Aucun patient en attente.";
}

$conn->close();
header("Location: file.php"); // Retour à la file d'attente
exit();
?>
