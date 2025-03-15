<?php
session_start();
require '../includes/config.php'; // Connexion à la BD

// Vérification des données
if (!isset($_SESSION['rdv_data'])) {
    die("Aucune donnée de rendez-vous trouvée !");
}
$data = $_SESSION['rdv_data'];

date_default_timezone_set('Europe/Paris');

// Jours et horaires de travail
$joursOuvres = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi'];
$heures = ['09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00'];

// Récupération des dates de la semaine
$dates = [];
$today = new DateTime();
while (count($dates) < 5) {
    if ($today->format('N') < 6) { // Exclut samedi et dimanche
        $dates[$joursOuvres[count($dates)]] = $today->format('Y-m-d');
    }
    $today->modify('+1 day');
}

// Vérification des créneaux déjà réservés
$bookedSlots = [];
$query = "SELECT date_rdv, heure_rdv FROM rendez_vous";
$result = $conn->query($query);
while ($row = $result->fetch_assoc()) {
    $bookedSlots[$row['date_rdv']][] = $row['heure_rdv'];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choisir un Horaire</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-gray-100 p-6">

    <!-- Bouton pour ouvrir le modal -->
    <button onclick="openModal()" class="bg-blue-500 text-white px-4 py-2 rounded">Choisir un Horaire</button>

    <!-- Modal -->
    <div id="horaireModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
        <div class="bg-white p-6 rounded-lg shadow-lg w-3/4">
            <h2 class="text-xl font-bold mb-4">Sélectionnez un créneau</h2>
            <table class="w-full border">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="p-2 border">Jour</th>
                        <?php foreach ($heures as $heure): ?>
                            <th class="p-2 border"><?= $heure ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dates as $jour => $date): ?>
                        <tr>
                            <td class="p-2 border"><?= $jour ?> (<?= date('d/m', strtotime($date)) ?>)</td>
                            <?php foreach ($heures as $heure): ?>
                                <td class="p-2 border text-center">
                                    <?php if (isset($bookedSlots[$date]) && in_array($heure, $bookedSlots[$date])): ?>
                                        ❌
                                    <?php else: ?>
                                        <input type="radio" name="horaire" value="<?= $date ?>|<?= $heure ?>">
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div class="mt-4 flex justify-end">
                <button onclick="closeModal()" class="bg-gray-500 text-white px-4 py-2 rounded mr-2">Annuler</button>
                <button onclick="validerRdv()" class="bg-green-500 text-white px-4 py-2 rounded">Confirmer</button>
            </div>
        </div>
    </div>

    <script>
        function openModal() {
            document.getElementById('horaireModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('horaireModal').classList.add('hidden');
        }

        function validerRdv() {
            let selected = document.querySelector('input[name="horaire"]:checked');
            if (!selected) {
                alert("Veuillez sélectionner un créneau !");
                return;
            }

            let [date, heure] = selected.value.split('|');

            $.post("valider_rdv.php", {
                cin: "<?= $data['cin'] ?>",
                nom: "<?= $data['nom'] ?>",
                prenom: "<?= $data['prenom'] ?>",
                email: "<?= $data['email'] ?>",
                telephone: "<?= $data['telephone'] ?>",
                date_rdv: date,
                heure_rdv: heure
            }, function(response) {
                if (response.status === "success") {
                    alert("Rendez-vous enregistré !");
                    closeModal();
                    window.location.href = "index.php"; // Redirige vers la liste des RDV
                } else {
                    alert("Erreur : " + response.message);
                }
            }, "json");
        }
    </script>

</body>
</html>
