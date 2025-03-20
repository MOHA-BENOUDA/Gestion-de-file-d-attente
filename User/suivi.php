<?php
// Connexion à la base de données
$conn = new mysqli("localhost", "root", "", "gestion_rdv");
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

if (isset($_POST['code_unique'])) {
    $code_unique = $_POST['code_unique'];
    $aujourdhui = date("Y-m-d");

    // Vérifier si le code unique existe et est pour aujourd'hui
    $sql = "SELECT * FROM rendez_vous WHERE code_unique = ? AND date_rdv = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $code_unique, $aujourdhui);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $rdv = $result->fetch_assoc();

        // Compter les personnes devant l'utilisateur dans la file d'attente
        $sqlQueue = "SELECT COUNT(*) AS position FROM file_attente 
        JOIN rendez_vous ON file_attente.id_rdv = rendez_vous.id
        WHERE rendez_vous.date_rdv = ? AND rendez_vous.heure_rdv < ? AND file_attente.etat = 'en attente'";
        

        $stmtQueue = $conn->prepare($sqlQueue);
        $stmtQueue->bind_param("ss", $rdv['date_rdv'], $rdv['heure_rdv']);
        $stmtQueue->execute();
        $resultQueue = $stmtQueue->get_result();
        $queueData = $resultQueue->fetch_assoc();

        // Envoyer la réponse en JSON
        $sqlEtat = "SELECT etat FROM file_attente WHERE id_rdv = ?";
        $stmtEtat = $conn->prepare($sqlEtat);
        $stmtEtat->bind_param("i", $rdv['id']);
        $stmtEtat->execute();
        $resultEtat = $stmtEtat->get_result();
        $etatRdv = ($resultEtat->num_rows > 0) ? $resultEtat->fetch_assoc()['etat'] : 'en attente';
        
        if ($etatRdv == 'appelé') {
            echo json_encode(["status" => "success", "message" => "C'est votre tour !"]);
            exit();
        } else {
            echo json_encode([
                "status" => "success",
                "nom" => $rdv['nom'],
                "prenom" => $rdv['prenom'],
                "email" => $rdv['email'],
                "telephone" => $rdv['telephone'],
                "position" => $queueData['position'],
                "prochain" => "Patientez encore un peu."
            ]);
        }
        
    } else {
        echo json_encode(["status" => "error", "message" => "Code invalide ou non valable pour aujourd'hui."]);
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suivre Mon Tour</title>
    <link rel="stylesheet" href="css/suivi.css">
</head>
<body>
    <div class="container">
    <a href="index.php" class="btn btn-primaary">Retour à l'accueil</a>
        <h1>Suivre Mon Tour</h1>
        <form id="codeForm">
            <label for="code">Entrez votre code unique :</label>
            <input type="text" id="code" name="code" required>
            <button type="submit" class="btn">Valider</button>
        </form>

        <div id="userInfo" style="display: none;">
            <h2>Informations personnelles</h2>
            <p id="info"></p>
            <p id="queueMessage"></p>
        </div>

        <p id="errorMessage" class="error-message" style="display: none;"></p>
    </div>

    <script>
        function fetchQueueStatus() {
            let code = document.getElementById('code').value.trim();
            if (code === '') return;

            fetch('suivi.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'code_unique=' + encodeURIComponent(code)
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === "success") {
                    document.getElementById('codeForm').style.display = 'none';
                    document.getElementById('userInfo').style.display = 'block';
                    document.getElementById('info').innerHTML = `
                        <strong>Nom :</strong> ${data.nom} <br>
                        <strong>Prénom :</strong> ${data.prenom} <br>
                        <strong>Email :</strong> ${data.email} <br>
                        <strong>Téléphone :</strong> ${data.telephone}
                    `;
                    document.getElementById('queueMessage').innerHTML = 
    (data.position == 0) ? "<strong>C'est votre tour !</strong>" :
    `Il reste <strong>${data.position}</strong> personne(s) avant votre tour.`;

                } else {
                    document.getElementById('errorMessage').textContent = data.message;
                    document.getElementById('errorMessage').style.display = 'block';
                }
            })
            .catch(error => console.error('Erreur:', error));
        }

        document.getElementById('codeForm').addEventListener('submit', function(event) {
            event.preventDefault();
            fetchQueueStatus();
            setInterval(fetchQueueStatus, 5000); // Actualisation toutes les 5 secondes
        });
    </script>
</body>
</html>
