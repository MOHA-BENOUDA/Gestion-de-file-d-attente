<?php
require_once "../includes/config.php";

$position = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $code_unique = $_POST["code_unique"];

    // Récupérer l'ID du RDV
    $sql = "SELECT id FROM rendez_vous WHERE code_unique = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$code_unique]);
    $rdv = $stmt->fetch();

    if ($rdv) {
        $id_rdv = $rdv["id"];

        // Trouver la position dans la file
        $sql = "SELECT position FROM file_attente WHERE id_rdv = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_rdv]);
        $file = $stmt->fetch();

        if ($file) {
            $position = $file["position"];
        } else {
            echo "Votre rendez-vous a déjà été traité ou annulé.";
        }
    } else {
        echo "Code invalide. Vérifiez et réessayez.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Suivre mon tour</title>
</head>
<body>
    <h1>Suivez votre tour</h1>
    <form method="POST">
        <label>Entrez votre code unique :</label>
        <input type="text" name="code_unique" required>
        <button type="submit">Vérifier</button>
    </form>

    <?php if ($position !== null): ?>
        <p>Votre position dans la file d'attente : <strong><?php echo $position; ?></strong></p>
    <?php endif; ?>
</body>
</html>
