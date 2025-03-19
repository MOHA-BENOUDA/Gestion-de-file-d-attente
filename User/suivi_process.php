<?php
require '../includes/config.php';

if (isset($_POST['code'])) {
    $code = $_POST['code'];

    $sql = "SELECT nom, prenom, telephone, email, queue_position FROM patients WHERE code_unique = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $code);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        echo json_encode(["success" => true, "data" => $data]);
    } else {
        echo json_encode(["success" => false, "message" => "Code invalide"]);
    }
}
?>
