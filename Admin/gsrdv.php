<?php
session_start();
require '../includes/config.php'; // Connexion BD

date_default_timezone_set('Europe/Paris');

// Jours et horaires de travail
$joursOuvres = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi'];
$heures = ['09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00'];

// Récupération des 2 prochaines semaines
$dates = [];
$today = new DateTime();
while (count($dates) < 10) {
    if ($today->format('N') < 6) {
        $dates[$joursOuvres[count($dates) % 5] . " " . $today->format('d/m')] = $today->format('Y-m-d');
    }
    $today->modify('+1 day');
}

// Récupération des rendez-vous existants
$rdvs = $conn->query("SELECT * FROM rendez_vous ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Prise de Rendez-vous</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-gray-100 p-6">
    <input type="text" id="search" placeholder="Rechercher..." class="border p-2 w-full mb-4">

    <table class="w-full border bg-white">
        <thead>
            <tr class="bg-gray-200">
                <th class="p-2 border">CIN</th>
                <th class="p-2 border">Nom</th>
                <th class="p-2 border">Prénom</th>
                <th class="p-2 border">Date</th>
                <th class="p-2 border">Heure</th>
                <th class="p-2 border">Action</th>
            </tr>
        </thead>
        <tbody id="rdvTable">
            <?php while ($rdv = $rdvs->fetch_assoc()): ?>
                <tr>
                    <td class="p-2 border"><?= $rdv['cin'] ?></td>
                    <td class="p-2 border"><?= $rdv['nom'] ?></td>
                    <td class="p-2 border"><?= $rdv['prenom'] ?></td>
                    <td class="p-2 border"><?= $rdv['date_rdv'] ?></td>
                    <td class="p-2 border"><?= $rdv['heure_rdv'] ?></td>
                    <td class="p-2 border text-center">
                        <button onclick="supprimerRdv(<?= $rdv['id'] ?>)" class="bg-red-500 text-white px-4 py-2 rounded">Supprimer</button>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <button onclick="openFormModal()" class="bg-blue-500 text-white px-6 py-3 rounded-lg shadow-lg hover:bg-blue-600 mt-4">
        Prendre un Rendez-vous
    </button>

    <div id="formModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
        <div class="bg-white p-6 rounded-lg w-1/3">
            <h2 class="text-xl font-bold">Informations Personnelles</h2>
            <form id="rdvForm">
                <input type="text" name="cin" placeholder="CIN" class="w-full border p-2 rounded mb-2">
                <input type="text" name="nom" placeholder="Nom" class="w-full border p-2 rounded mb-2">
                <input type="text" name="prenom" placeholder="Prénom" class="w-full border p-2 rounded mb-2">
                <input type="email" name="email" placeholder="Email" class="w-full border p-2 rounded mb-2">
                <input type="text" name="telephone" placeholder="Téléphone" class="w-full border p-2 rounded mb-2">
                <button type="button" onclick="openHoraireModal()" class="bg-green-500 text-white px-4 py-2 rounded w-full">Suivant</button>
            </form>
        </div>
    </div>

    <div id="horaireModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
        <div class="bg-white p-6 rounded-lg w-3/4">
            <h2 class="text-xl font-bold">Sélectionnez un créneau</h2>
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
                            <td class="p-2 border"><?= $jour ?></td>
                            <?php foreach ($heures as $heure): ?>
                                <td class="p-2 border text-center">
                                    <input type="radio" name="horaire" value="<?= $date ?>|<?= $heure ?>">
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <button onclick="validerRdv()" class="bg-green-500 text-white px-4 py-2 rounded w-full mt-4">Confirmer</button>
        </div>
    </div>

    <script>
        function openFormModal() { document.getElementById('formModal').classList.remove('hidden'); }
        function openHoraireModal() { document.getElementById('horaireModal').classList.remove('hidden'); document.getElementById('formModal').classList.add('hidden'); }
        function validerRdv() {
            let formData = $("#rdvForm").serialize();
            let selected = document.querySelector('input[name="horaire"]:checked');
            if (!selected) { alert("Sélectionnez un créneau"); return; }
            let [date, heure] = selected.value.split('|');
            formData += `&date_rdv=${date}&heure_rdv=${heure}`;
            $.post("valider_rdv.php", formData, function(response) {
                alert("Rendez-vous enregistré"); window.location.reload();
            }, "json");
        }

        $('#search').on('keyup', function() {
            let value = $(this).val().toLowerCase();
            $('#rdvTable tr').filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
            });
        });
    </script>
</body>
</html>