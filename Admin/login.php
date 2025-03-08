<?php
session_start();
require_once '../includes/config.php'; // Assurez-vous que ce fichier contient $conn

$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") { 
    // Récupération des valeurs du formulaire
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {
        // Préparer la requête pour récupérer l'utilisateur
        $stmt = $conn->prepare("SELECT nom, mot_de_passe FROM administrateurs WHERE nom = ?");
        if ($stmt) {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            // Vérifier si l'utilisateur existe
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();

                // Comparer le mot de passe en clair (PAS SÉCURISÉ)
                if ($password === $row['mot_de_passe']) {
                    $_SESSION['username'] = $username;
                    header("Location: dashboard.php");
                    exit();
                } else {
                    $error_message = "Nom d'utilisateur ou mot de passe incorrect.";
                }
            } else {
                $error_message = "Nom d'utilisateur ou mot de passe incorrect.";
            }

            $stmt->close();
        } else {
            $error_message = "Erreur de requête.";
        }
    } else {
        $error_message = "Veuillez remplir tous les champs.";
    }
}

// Fermer la connexion
$conn->close();
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion Administrateur</title>
    <link rel="stylesheet" href="admin-style.css">
</head>
<body>
    <div class="login-container">
        <h2>Connexion Administrateur</h2>
        <form method="post" action="login.php"> 
            <label for="username">Nom d'utilisateur:</label>
            <input type="text" id="username" name="username" required>

            <label for="password">Mot de passe:</label>
            <input type="password" id="password" name="password" required>

            <?php if (!empty($error_message)): ?>
                <p class="error"><?php echo htmlspecialchars($error_message); ?></p>
            <?php endif; ?>

            <button type="submit">Connexion</button>
        </form>
    </div>
</body>
</html>
