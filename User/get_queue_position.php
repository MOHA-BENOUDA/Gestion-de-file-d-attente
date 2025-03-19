<?php
require '../includes/config.php';

if (isset($_GET['code'])) {
    $code = $_GET['code'];

    $sql = "SELECT queue_position FROM patients WHERE code_unique = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $code);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        echo json_encode(["success" => true, "position" => $data['queue_position']]);
    } else {
        echo json_encode(["success" => false]);
    }
}
?>
