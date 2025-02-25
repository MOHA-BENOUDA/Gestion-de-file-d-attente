<?php
require 'includes/config.php';

if ($conn) {
    echo "Connexion réussie à la base de données !";
} else {
    echo "Erreur de connexion.";
}
?>
