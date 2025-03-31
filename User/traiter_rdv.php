<?php
session_start();
require '../includes/config.php'; // Connexion à la BD

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

// Vérification des données en session
$required_fields = ['cin', 'nom', 'prenom', 'email', 'telephone', 'code_unique'];
foreach ($required_fields as $field) {
    if (!isset($_SESSION[$field])) {
        header('Location: rdv.php');
        exit();
    }
}

// Vérification et extraction de la date et l'heure
if (!isset($_POST['date_heure']) || empty($_POST['date_heure'])) {
    die("Erreur : Veuillez sélectionner une date et une heure pour le rendez-vous.");
}
$parts = explode(' ', $_POST['date_heure']);
if (count($parts) !== 2) {
    die("Erreur : Format de date/heure incorrect.");
}
$date_rdv = $parts[0];
$heure_rdv = $parts[1] . ":00";

// Préparation des données pour l'insertion
$cin        = trim($_SESSION['cin']);
$nom        = trim($_SESSION['nom']);
$prenom     = trim($_SESSION['prenom']);
$email      = filter_var($_SESSION['email'], FILTER_VALIDATE_EMAIL);
$telephone  = trim($_SESSION['telephone']);
$code_unique= trim($_SESSION['code_unique']);

if (!$email) {
    die("Erreur : Adresse email invalide.");
}

// Insertion du rendez-vous
$stmt = $conn->prepare("INSERT INTO rendez_vous (cin, nom, prenom, email, telephone, date_rdv, heure_rdv, code_unique, etat, date_creation) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'En attente', NOW())");
if (!$stmt) {
    die("Erreur de préparation SQL : " . $conn->error);
}
$stmt->bind_param("ssssssss", $cin, $nom, $prenom, $email, $telephone, $date_rdv, $heure_rdv, $code_unique);

if ($stmt->execute()) {
    $message = "Votre rendez-vous a été confirmé avec succès.";

    // Envoi de l'email de confirmation
    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'othmane.bouali.2005@gmail.com'; // Ton adresse email
        $mail->Password   = 'ynef jcqd jgtl rdmj'; // Mot de passe d'application sécurisé
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Paramètres de l'email
        $mail->setFrom('othmane.bouali.2005@gmail.com', 'GESTIONNAIRE');
        $mail->addAddress($email, "$nom $prenom");
        $mail->isHTML(true);
        $mail->Subject = 'Confirmation de Rendez-vous';
        $mail->Body    = 'Bonjour <b>' . htmlspecialchars($nom) . ' ' . htmlspecialchars($prenom) . '</b>,<br><br>' .
                         'Votre rendez-vous est confirmé pour le <b>' . htmlspecialchars($date_rdv) . '</b> à <b>' . htmlspecialchars($heure_rdv) . '</b>.<br><br>' .
                         'Votre code unique est : <b>' . htmlspecialchars($code_unique) . '</b><br><br>' .
                         'Cordialement,<br>Votre équipe.';

        $mail->send(); // Lance l'envoi
    } catch (Exception $e) {
        echo "Erreur lors de l'envoi de l'email : " . $e->getMessage();
    }
} else {
    die("Erreur lors de l'enregistrement du rendez-vous : " . $stmt->error);
}

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation du Rendez-vous</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f6f9; }
        .container { max-width: 600px; margin-top: 30px; }
        .modal-content { border-radius: 15px; box-shadow: 0px 4px 10px rgba(0,0,0,0.1); }
        .modal-header { background-color: #007bff; color: white; border-top-left-radius: 15px; border-top-right-radius: 15px; }
        .btn-primary, .btn-success, .btn-secondary { border-radius: 8px; }
        .badge { font-size: 1rem; padding: 0.5rem 1rem; }
        .card { border: none; box-shadow: 0px 4px 10px rgba(0,0,0,0.1); border-radius: 10px; }
        .alert { border-radius: 10px; }
    </style>
</head>
<body>
<div class="container">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <a href="index.php" class="btn btn-light">⬅ Retour</a>
                <h5 class="modal-title">Confirmation du Rendez-vous</h5>
            </div>
            <div class="modal-body">
                <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title text-center mb-4">Détails du Rendez-vous</h5>
                        <p><strong>CIN :</strong> <?= htmlspecialchars($cin) ?></p>
                        <p><strong>Nom :</strong> <?= htmlspecialchars($nom) ?></p>
                        <p><strong>Prénom :</strong> <?= htmlspecialchars($prenom) ?></p>
                        <p><strong>Email :</strong> <?= htmlspecialchars($email) ?></p>
                        <p><strong>Téléphone :</strong> <?= htmlspecialchars($telephone) ?></p>
                        <p><strong>Date :</strong> <?= htmlspecialchars($date_rdv) ?></p>
                        <p><strong>Heure :</strong> <?= htmlspecialchars($heure_rdv) ?>h</p>
                        <p><strong>Code Unique :</strong> <span class="badge bg-primary"><?= htmlspecialchars($code_unique) ?></span></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <a href="rdv.php" class="btn btn-secondary">Prendre un autre RDV</a>
                <a href="index.php" class="btn btn-primary">Retour à l'accueil</a>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>