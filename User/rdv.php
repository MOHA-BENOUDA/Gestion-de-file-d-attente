<<<<<<< HEAD
<?php
require_once "../includes/config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = $_POST["nom"];
    $email = $_POST["email"];
    $telephone = $_POST["telephone"];
    $date_rdv = $_POST["date_rdv"];
    $heure_rdv = $_POST["heure_rdv"];
    $code_unique = uniqid();
 
    // Vérifier si la date et l'heure ne sont pas bloquées
    $sql = "SELECT * FROM jours_bloques WHERE date_bloquee = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$date_rdv]);
    if ($stmt->rowCount() > 0) {
        die("Cette date est bloquée. Veuillez choisir une autre date.");
    }

    $sql = "SELECT * FROM heures_bloquees WHERE date_bloquee = ? AND heure_bloquee = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$date_rdv, $heure_rdv]);
    if ($stmt->rowCount() > 0) {
        die("Cet horaire est bloqué. Veuillez choisir un autre créneau.");
    }

    // Ajouter le rendez-vous
    $sql = "INSERT INTO rendez_vous (nom, email, telephone, date_rdv, heure_rdv, code_unique) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nom, $email, $telephone, $date_rdv, $heure_rdv, $code_unique]);

    $id_rdv = $pdo->lastInsertId(); // Récupérer l'ID du RDV

    // Ajouter à la file d'attente
    $sql = "SELECT COUNT(*) AS position FROM file_attente";
    $stmt = $pdo->query($sql);
    $position = $stmt->fetch()["position"] + 1;

    $sql = "INSERT INTO file_attente (id_rdv, position) VALUES (?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_rdv, $position]);

    echo "Votre rendez-vous a été enregistré ! Votre code unique est : <strong>$code_unique</strong>";
}
?>

=======
>>>>>>> 1a25b7d (la modification cote user)
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prendre un Rendez-vous - Page 1</title>
    <style> 
/* Importation de Google Fonts */
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
    background: rgba(255, 255, 255, 0.3); /* Légère transparence pour l'effet de fond */
    padding: 10px;  /* Réduit l'espace intérieur du conteneur */
    border-radius: 8px;  /* Coins arrondis */
    box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.1); /* Ombre douce */
    backdrop-filter: blur(8px); /* Effet de flou pour un style moderne */
    max-width: 350px;  /* Réduit la largeur du conteneur */
    width: 100px;
    margin: auto; /* Centre le conteneur */
}

h2 {
    font-size: 14px; /* Titre plus petit */
    color: white;
    margin-bottom: 15px;
}

#form-page-1 {
    max-width: 400px;  /* Réduit la largeur du formulaire */
    margin: auto;       /* Centre le formulaire */
    padding: 15px;      /* Réduit l'espace intérieur */
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1); /* Effet d'ombre doux */
}
.form-label {
    font-weight: 400;
    color: #333;
    font-size: 12px; /* Taille plus petite pour les labels */
    margin-bottom: 5px;
}

.form-control {
    border-radius: 5px;
    padding: 6px 10px; /* Moins de padding pour garder l’ensemble compact */
    font-size: 12px; /* Taille du texte dans les champs réduite */
    border: 1px solid #ccc;
    margin-bottom: 10px; /* Espacement entre les champs */
}

.form-control:focus {
    border-color: #0072ff;
    box-shadow: 0px 0px 4px rgba(0, 114, 255, 0.3);
}

.btn-success {
    background: #0072ff;
    border: none;
    padding: 8px 12px; /* Moins de padding pour garder un bouton compact */
    font-size: 14px;
    font-weight: bold;
    border-radius: 5px;
    transition: 0.3s ease-in-out;
}

.btn-success:hover {
    background: white;
    color: #0072ff;
    box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.3);
    transform: scale(1.05);
}

@media (max-width: 768px) {
    .container {
        max-width: 80%; /* Plus large sur petits écrans */
    }
}

#next-page-1 {
    background-color: rgb(37, 7, 230) !important;
    border-color: rgb(9, 20, 240) !important;
}

    </style>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center mb-4">Prendre un Rendez-vous</h2>
        
        <!-- Formulaire de la page 1 -->
        <form id="form-page-1" class="p-4 shadow rounded bg-white">
            <div class="mb-3">
                <label for="cin" class="form-label">CIN :</label>
                <input type="text" class="form-control" id="cin" name="cin" required>
            </div>

            <div class="mb-3">
                <label for="nom" class="form-label">Nom :</label>
                <input type="text" class="form-control" id="nom" name="nom" required>
            </div>

            <div class="mb-3">
                <label for="prenom" class="form-label">Prénom :</label>
                <input type="text" class="form-control" id="prenom" name="prenom" required>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email :</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>

            <div class="mb-3">
                <label for="telephone" class="form-label">Téléphone :</label>
                <input type="tel" class="form-control" id="telephone" name="telephone" required>
            </div>

            <button type="button" class="btn btn-success w-100 mb-3" id="next-page-1">Suivant</button>
        </form>
    </div>

    <script>
        $(document).ready(function() {
            // Rediriger vers la page 2 lorsque le bouton "Suivant" est cliqué
            $("#next-page-1").click(function() {
                // Vérifier que tous les champs sont remplis
                var cin = $("#cin").val();
                var nom = $("#nom").val();
                var prenom = $("#prenom").val();
                var email = $("#email").val();
                var telephone = $("#telephone").val();

                if(cin && nom && prenom && email && telephone) {
                    // Enregistrer les informations dans localStorage
                    localStorage.setItem("cin", cin);
                    localStorage.setItem("nom", nom);
                    localStorage.setItem("prenom", prenom);
                    localStorage.setItem("email", email);
                    localStorage.setItem("telephone", telephone);

                    // Rediriger vers la page 2
                    window.location.href = "date.php"; // Page 2
                } else {
                    alert("Veuillez remplir tous les champs.");
                }
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
