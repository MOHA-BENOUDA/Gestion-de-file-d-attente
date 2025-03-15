<?php
session_start();
require '../includes/config.php'; // Assurez-vous que ce fichier contient la connexion à la BD

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);

    // Vérifier si l'ID du rendez-vous existe bien dans la base de données
    $stmt = $conn->prepare("SELECT * FROM rendez_vous WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Supprimer le rendez-vous
        $stmt = $conn->prepare("DELETE FROM rendez_vous WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Rendez-vous supprimé avec succès."]);
        } else {
            echo json_encode(["success" => false, "message" => "Erreur lors de la suppression du rendez-vous."]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Rendez-vous introuvable."]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["success" => false, "message" => "Requête invalide."]);
}
?>
