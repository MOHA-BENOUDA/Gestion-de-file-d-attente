<?php
session_start();
require '../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];

    // Requête pour supprimer le rendez-vous de la base de données
    $query = "DELETE FROM rendez_vous WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $id);

    if ($stmt->execute()) {
        header('Location: gestion_rdv_admin.php'); // Redirige après la suppression
        exit();
    } else {
        echo "Erreur lors de la suppression du rendez-vous.";
    }
}
?>
