<?php
session_start();
require '../includes/config.php'; // Connexion à la base de données

// Vérification du code unique
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

        // Vérifier l'état du rendez-vous
        $sqlEtat = "SELECT etat FROM file_attente WHERE id_rdv = ?";
        $stmtEtat = $conn->prepare($sqlEtat);
        $stmtEtat->bind_param("i", $rdv['id']);
        $stmtEtat->execute();
        $resultEtat = $stmtEtat->get_result();
        $etatRdv = ($resultEtat->num_rows > 0) ? $resultEtat->fetch_assoc()['etat'] : 'en attente';

        // Envoyer la réponse en JSON
        if ($etatRdv == 'appelé') {
            echo json_encode(["status" => "success", "message" => "C'est votre tour !"]);
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
        .error-message { color: red; font-weight: bold; }
    </style>
</head>
<body>
<div class="container">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <a href="index.php" class="btn btn-light">⬅ Retour</a>
                <h5 class="modal-title">Suivre Mon Tour</h5>
            </div>
            <div class="modal-body">
                <form id="codeForm" class="text-center">
                    <label for="code" class="form-label">Entrez votre code unique :</label>
                    <input type="text" id="code" name="code" class="form-control mb-3" required>
                    <button type="submit" class="btn btn-primary w-100">Valider</button>
                </form>

                <div id="userInfo" style="display: none;">
                    <div class="card mt-4">
                        <div class="card-body">
                            <h5 class="card-title text-center mb-4">Informations personnelles</h5>
                            <p><strong>Nom :</strong> <span id="info-nom"></span></p>
                            <p><strong>Prénom :</strong> <span id="info-prenom"></span></p>
                            <p><strong>Email :</strong> <span id="info-email"></span></p>
                            <p><strong>Téléphone :</strong> <span id="info-telephone"></span></p>
                            <p><strong>Position dans la file :</strong> <span id="info-position"></span></p>
                            <p id="queueMessage" class="text-center"></p>
                        </div>
                    </div>
                </div>

                <p id="errorMessage" class="error-message text-center" style="display: none;"></p>
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <a href="index.php" class="btn btn-secondary">Retour à l'accueil</a>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js"></script>
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
                document.getElementById('info-nom').textContent = data.nom;
                document.getElementById('info-prenom').textContent = data.prenom;
                document.getElementById('info-email').textContent = data.email;
                document.getElementById('info-telephone').textContent = data.telephone;

                if (data.position == 0) {
                    document.getElementById('info-position').innerHTML = "<strong>C'est votre tour !</strong>";
                    document.getElementById('queueMessage').innerHTML = "";
                } else {
                    document.getElementById('info-position').textContent = data.position + " personne(s)";
                    document.getElementById('queueMessage').innerHTML = "<strong>Patientez encore un peu.</strong>";
                }
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