<?php
session_start();
require '../includes/config.php'; // Connexion BD

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cin = $_POST['cin'];
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    $telephone = $_POST['telephone'];
    $date_rdv = $_POST['date_rdv'];
    $heure_rdv = $_POST['heure_rdv'];
    $etat = "en attente";
    $date_creation = date("Y-m-d H:i:s");

    // Génération d'un code unique basé sur CIN + timestamp
    $code_unique = strtoupper(substr(md5($cin . time()), 0, 8));

    // Enregistrement dans la base de données
    $sql = "INSERT INTO rendez_vous (cin, nom, prenom, email, telephone, date_rdv, heure_rdv, code_unique, etat, date_creation)
            VALUES ('$cin', '$nom', '$prenom', '$email', '$telephone', '$date_rdv', '$heure_rdv', '$code_unique', '$etat', '$date_creation')";

    if ($conn->query($sql) === TRUE) {
        echo json_encode(["status" => "success", "message" => "Rendez-vous enregistré avec succès.", "code_unique" => $code_unique]);
    } else {
        echo json_encode(["status" => "error", "message" => "Erreur: " . $conn->error]);
    }
}
?>
