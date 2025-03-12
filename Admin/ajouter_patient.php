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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = $conn->real_escape_string($_POST['nom']);
    $heure = $conn->real_escape_string($_POST['heure']);

    // Vérifier la dernière position
    $sqlPosition = "SELECT MAX(position) AS max_pos FROM file_attente";
    $result = $conn->query($sqlPosition);
    $row = $result->fetch_assoc();
    $position = ($row['max_pos'] ?? 0) + 1;

    // Insérer le patient
    $sqlInsert = "INSERT INTO file_attente (id_rdv, position, etat) 
                  VALUES ((SELECT id FROM rendez_vous WHERE nom='$nom' AND heure_rdv='$heure' LIMIT 1), '$position', 'en attente')";
    
    if ($conn->query($sqlInsert) === TRUE) {
        echo "Patient ajouté avec succès.";
    } else {
        echo "Erreur : " . $conn->error;
    }
}

$conn->close();
header("Location: file.php"); // Retour à la file d'attente
exit();
?>
