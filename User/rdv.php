<?php
session_start();
require '../includes/config.php'; // Connexion  BD

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST['cin']) || empty($_POST['nom']) || empty($_POST['prenom']) || empty($_POST['email']) || empty($_POST['telephone'])) {
        $erreur = "Tous les champs sont obligatoires.";
    } else {
        $_SESSION['cin'] = $_POST['cin'];
        $_SESSION['nom'] = $_POST['nom'];
        $_SESSION['prenom'] = $_POST['prenom'];
        $_SESSION['email'] = $_POST['email'];
        $_SESSION['telephone'] = $_POST['telephone'];
        $lettres = strtoupper(substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 2));
        $chiffres = str_pad(mt_rand(0, 999999), 6, "0", STR_PAD_LEFT);
        $code_unique = $lettres . $chiffres;
        $_SESSION['code_unique'] = $code_unique;  
        $stmt_check = $conn->prepare("SELECT id FROM rendez_vous WHERE cin = ?");
        $stmt_check->bind_param("s", $_SESSION['cin']);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $erreur = "Cet utilisateur a déjà un rendez-vous.";
        } else {
            header("Location: date.php");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prendre un Rendez-vous</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">Prendre un Rendez-vous</h2>
        
        <?php if (isset($erreur)): ?>
            <div class="alert alert-danger"><?= $erreur ?></div>
        <?php endif; ?>

        <form action="" method="post">
            <div class="mb-3">
                <label class="form-label">CIN :</label>
                <input type="text" name="cin" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Nom :</label>
                <input type="text" name="nom" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Prénom :</label>
                <input type="text" name="prenom" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Email :</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Téléphone :</label>
                <input type="tel" name="telephone" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Suivant</button>
        </form>
    </div>
</body>
</html>
