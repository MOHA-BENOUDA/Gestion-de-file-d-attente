<?php
require '../includes/config.php'; // Connexion à la BDD

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $date_rdv = $_POST['date_rdv'];
    $heure_rdv = $_POST['heure_rdv'];

    $stmt = $conn->prepare("UPDATE rendez_vous SET date_rdv = ?, heure_rdv = ? WHERE id = ?");
    $stmt->bind_param("ssi", $date_rdv, $heure_rdv, $id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false]);
    }

    $stmt->close();
    $conn->close();
}
?>
