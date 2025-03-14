<?php
session_start();
require '../includes/config.php'; // Connexion à la BD

date_default_timezone_set('Europe/Paris');
$currentDate = new DateTime();
$weekOffset = isset($_GET['week']) ? (int)$_GET['week'] : 0;
$displayDate = clone $currentDate;
$displayDate->modify("+$weekOffset week");

$lundi = clone $displayDate;
$lundi->modify('-' . ($lundi->format('N') - 1) . ' days');
$jours = [];
for ($i = 0; $i < 5; $i++) {
    $jours[] = clone $lundi;
    $lundi->modify('+1 day');
}
$heures = ['09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00'];

$search = $_GET['search'] ?? '';
$query = "SELECT * FROM rendez_vous WHERE cin LIKE ? OR nom LIKE ? ORDER BY date_rdv ASC";
$stmt = $conn->prepare($query);
$searchTerm = "%$search%";
$stmt->bind_param("ss", $searchTerm, $searchTerm);
$stmt->execute();
$result = $stmt->get_result();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cin = trim($_POST['cin']);
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);
    $telephone = trim($_POST['telephone']);
    $horaire = $_POST['horaire'];
    
    if (!empty($cin) && !empty($nom) && !empty($prenom) && !empty($email) && !empty($telephone) && !empty($horaire)) {
        list($date, $heure) = explode('|', $horaire);
        $code_unique = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
        $stmt = $conn->prepare("INSERT INTO rendez_vous (code_unique, cin, nom, prenom, email, telephone, date_rdv, heure_rdv) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('ssssssss', $code_unique, $cin, $nom, $prenom, $email, $telephone, $date, $heure);
        if ($stmt->execute()) {
            echo json_encode(["status" => "success"]);
        } else {
            echo json_encode(["status" => "error"]);
        }
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Rendez-vous</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-gray-100 p-6">
    <h1 class="text-2xl font-bold text-center mb-4">📅 Gestion des Rendez-vous</h1>
    
    <div class="mb-4 flex justify-between">
        <input type="text" id="search" placeholder="Rechercher un RDV..." class="p-2 border rounded w-full mr-2">
        <button onclick="openModal('rdvModal')" class="bg-green-600 text-white px-4 py-2 rounded">➕ Ajouter un RDV</button>
    </div>
    
    <div class="bg-white mt-6 p-4 shadow rounded-lg">
        <h3 class="text-lg font-bold">📋 Liste des Rendez-vous</h3>
        <table class="w-full mt-4 border" id="rdvTable">
            <thead>
                <tr class="bg-gray-200">
                    <th>Code Unique</th>
                    <th>CIN</th>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Email</th>
                    <th>Téléphone</th>
                    <th>Date</th>
                    <th>Heure</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($rdv = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $rdv['code_unique'] ?></td>
                        <td><?= $rdv['cin'] ?></td>
                        <td><?= $rdv['nom'] ?></td>
                        <td><?= $rdv['prenom'] ?></td>
                        <td><?= $rdv['email'] ?></td>
                        <td><?= $rdv['telephone'] ?></td>
                        <td><?= $rdv['date_rdv'] ?></td>
                        <td><?= $rdv['heure_rdv'] ?></td>
                        <td>
                            <button onclick="confirmDelete(<?= $rdv['id'] ?>)" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 transition duration-300">Supprimer</button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    
    <script>
        function confirmDelete(id) {
            Swal.fire({
                title: "Êtes-vous sûr ?",
                text: "Ce rendez-vous sera supprimé définitivement!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Oui, supprimer!"
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post("delete.php", { id: id }, function(response) {
                        location.reload();
                    });
                }
            });
        }
        
        $(document).ready(function () {
            $('#search').on('keyup', function () {
                var value = $(this).val().toLowerCase();
                $('#rdvTable tbody tr').filter(function () {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
                });
            });
        });
    </script>
</body>
</html>