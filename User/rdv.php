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
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: linear-gradient(to right, #00c6ff, #0072ff);
            text-align: center;
        }

        .container {
            background: rgba(255, 255, 255, 0.3);
            padding: 20px;
            border-radius: 10px;
            backdrop-filter: blur(8px);
            max-width: 400px;
            width: 100%;
            box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.2);
        }

        h2 {
            font-size: 20px;
            color: white;
            margin-bottom: 15px;
        }

        .form-group {
            margin-bottom: 15px;
            text-align: left;
        }

        label {
            font-weight: bold;
            color: white;
        }

        input {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            border: none;
            outline: none;
            font-size: 14px;
        }

        .btn-primary {
            background: #0072ff;
            border: none;
            padding: 10px;
            font-size: 16px;
            font-weight: bold;
            border-radius: 8px;
            color: white;
            transition: 0.3s ease-in-out;
            cursor: pointer;
            width: 100%;
        }

        .btn-primary:hover {
            background: white;
            color: #0072ff;
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.3);
            transform: scale(1.05);
        }

        .alert {
            background: rgba(255, 0, 0, 0.7);
            color: white;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
        }

        .btn-back {
            display: inline-block;
            text-decoration: none;
            background: white;
            color: #0072ff;
            padding: 8px 15px;
            border-radius: 5px;
            font-weight: bold;
            transition: 0.3s;
        }

        .btn-back:hover {
            background: #0072ff;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" class="btn-back">Revenir</a>
        <h2>Prendre un Rendez-vous</h2>
        
        <?php if (isset($erreur)): ?>
            <div class="alert"> <?= $erreur ?> </div>
        <?php endif; ?>

        <form action="" method="post">
            <div class="form-group">
                <label>CIN :</label>
                <input type="text" name="cin" required>
            </div>
            <div class="form-group">
                <label>Nom :</label>
                <input type="text" name="nom" required>
            </div>
            <div class="form-group">
                <label>Prénom :</label>
                <input type="text" name="prenom" required>
            </div>
            <div class="form-group">
                <label>Email :</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Téléphone :</label>
                <input type="tel" name="telephone" required>
            </div>
            <button type="submit" class="btn-primary">Suivant</button>
        </form>
    </div>
</body>
</html>
