<?php
$host = "localhost"; // Serveur MySQL (XAMPP)
$user = "root";      // Par défaut, XAMPP utilise "root"
$password = "";      // Par défaut, pas de mot de passe
$dbname = "gestion"; // Nom de ta base de données

// Connexion à la base de données
$conn = new mysqli($host, $user, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
}

// Définir l'encodage des caractères en UTF-8
$conn->set_charset("utf8");
if ($conn) {
    echo "connexion ";
} else {
    echo "Erreur de connexion.";
}
?>
