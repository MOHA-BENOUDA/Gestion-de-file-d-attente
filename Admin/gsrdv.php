<?php
session_start();
require '../includes/config.php'; // Doit initialiser la connexion $conn

if (!isset($_SESSION['cin'])) {
    header("Location: gsrdv.php"); 
    exit();
}

if (!isset($_SESSION['code_unique'])) {
    header("Location: gsrdv.php");
    exit();
}
$code_unique = $_SESSION['code_unique'];

date_default_timezone_set('Europe/Paris');
// Définir la locale pour afficher les jours en français
setlocale(LC_TIME, 'fr_FR.UTF-8');

// Gestion du décalage de semaine
$weekOffset = isset($_GET['week']) ? (int)$_GET['week'] : 0;
$startDate = new DateTime();
$startDate->modify("+" . ($weekOffset * 7) . " days");

// Récupération des 10 prochains jours ouvrés à partir de la date de départ
$dates = [];
while (count($dates) < 10) {
    if ($startDate->format('N') < 6) { // jours de lundi (1) à vendredi (5)
        // Utilisation de strftime pour récupérer le jour en français
        $dayName = ucfirst(strftime('%A', $startDate->getTimestamp()));
        $key = $dayName . " " . $startDate->format('d/m');
        $dates[$key] = $startDate->format('Y-m-d');
    }
    $startDate->modify('+1 day');
}

// Horaires de travail disponibles
$heures = ['09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00'];

// Récupération des rendez-vous existants pour affichage
$rdvs = $conn->query("SELECT * FROM rendez_vous ORDER BY id DESC");
// Récupérer le nombre max de rendez-vous par créneau
$max_rdv_par_creneau = 3; // Ajustez selon votre besoin

// Stocker les créneaux complets
$creneaux_complets = [];

$sqlComplet = "SELECT date_rdv, heure_rdv, COUNT(*) as total FROM rendez_vous GROUP BY date_rdv, heure_rdv";
$resultComplet = $conn->query($sqlComplet);

while ($row = $resultComplet->fetch_assoc()) {
    if ($row['total'] >= $max_rdv_par_creneau) {
        $creneaux_complets[$row['date_rdv']][$row['heure_rdv']] = true;
    }
}


// Récupération des blocages

// Jours bloqués : tableau associatif ['YYYY-MM-DD' => raison]
$blockedDays = [];
$sqlBlockedDays = "SELECT date_bloquee, raison FROM jours_bloques";
if ($result = $conn->query($sqlBlockedDays)) {
    while ($row = $result->fetch_assoc()) {
        $blockedDays[$row['date_bloquee']] = $row['raison'];
    }
    $result->free();
}

// Heures bloquées : tableau associatif ['YYYY-MM-DD' => [ liste d'heures bloquées au format 'HH:MM' ]]
$blockedHours = [];
$sqlBlockedHours = "SELECT date_bloquee, heure_bloquee, raison FROM heures_bloquees";
if ($result = $conn->query($sqlBlockedHours)) {
    while ($row = $result->fetch_assoc()) {
        $date = $row['date_bloquee'];
        $heure = substr($row['heure_bloquee'], 0, 5);
        $blockedHours[$date][] = $heure;
    }
    $result->free();
}
// Récupération de la capacité des créneaux
$resultCapacite = $conn->query("SELECT capacite_max FROM capacite_globale LIMIT 1");
$capaciteGlobale = ($resultCapacite && $resultCapacite->num_rows > 0) ? $resultCapacite->fetch_assoc()['capacite_max'] : 5;

$capaciteRestante = [];
$sqlCapacite = "SELECT date_rdv, heure_rdv, COUNT(*) AS nombre_reservations FROM rendez_vous GROUP BY date_rdv, heure_rdv";
$result = $conn->query($sqlCapacite);
while ($row = $result->fetch_assoc()) {
    $date = $row['date_rdv'];
    $heure = substr($row['heure_rdv'], 0, 5);
    $capaciteRestante[$date][$heure] = $capaciteGlobale - $row['nombre_reservations'];
}
$result->free();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Prise de Rendez-vous</title>
    <!-- Utilisation de Bootstrap pour un design moderne -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .blocked {
            background-color: #f8d7da !important;
            color: #721c24 !important;
            font-weight: bold;
        }
    </style>
</head>
<body class="bg-gray-100 p-6">
<div class="container mx-auto max-w-4xl mb-8">

<h2 class="text-2xl font-semibold text-gray-800 text-center mb-4">Rendez-vous existants</h2>

<!-- Barre de recherche et bouton -->
<div class="flex items-center justify-center gap-4 mb-6">
    <input type="text" id="search" placeholder="🔍 Rechercher..." 
           class="border border-gray-300 rounded-full px-5 py-3 w-96 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
           <button onclick="openFormModal()" class="bg-blue-500 text-white font-medium px-6 py-3 rounded-full shadow-md hover:bg-blue-600 transition">
    Prendre un Rendez-vous
</button>

</div>
    <div class="flex">
    <!-- Sidebar -->
    <div class="w-64 bg-blue-900 min-h-screen p-6 text-white">
        <?php include 'sidebar.php'; ?>
    </div>

    <!-- Contenu principal -->
    <main class="flex-1 p-6">
        <!-- Contenu spécifique de la page -->
 


        <div class="overflow-hidden rounded-lg shadow-lg">
        <table class="min-w-full bg-white border border-gray-200 mx-auto">

        <thead class="bg-gray-100 text-gray-600 uppercase text-sm">
    <tr class="text-center">
        <th class="py-3 px-6 text-center">Code Unique</th>
        <th class="py-3 px-6 text-center">CIN</th>
        <th class="py-3 px-6 text-center">Nom</th>
        <th class="py-3 px-6 text-center">Prénom</th>
        <th class="py-3 px-6 text-center">Date</th>
        <th class="py-3 px-6 text-center">Heure</th>
        <th class="py-3 px-6 text-center">Statut</th> <!-- Nouvelle colonne -->
        <th class="py-3 px-6 text-center">Action</th>
    </tr>
</thead>

                <tbody id="rdvTable">
                    <?php while ($rdv = $rdvs->fetch_assoc()): ?>
                        <tr>
                            <td class="p-2"><?= htmlspecialchars($rdv['code_unique']) ?></td>
                            <td class="p-2"><?= htmlspecialchars($rdv['cin']) ?></td>
                            <td class="p-2"><?= htmlspecialchars($rdv['nom']) ?></td>
                            <td class="p-2"><?= htmlspecialchars($rdv['prenom']) ?></td>
                            <td class="p-2"><?= htmlspecialchars($rdv['date_rdv']) ?></td>
                            <td class="p-2"><?= htmlspecialchars($rdv['heure_rdv']) ?></td>
                            <td class="p-2 text-center">
                                <button onclick="supprimerRdv(<?= $rdv['id'] ?>)" class="btn btn-danger btn-sm">Supprimer</button>
                            </td>
                            <td class="p-2 text-center">
    <button onclick="ouvrirModifierModal(<?= $rdv['id'] ?>, '<?= $rdv['date_rdv'] ?>', '<?= $rdv['heure_rdv'] ?>')" class="btn btn-warning btn-sm">Modifier</button>
</td>
<td class="p-2 text-center">
    <?php if (isset($creneaux_complets[$rdv['date_rdv']][$rdv['heure_rdv']])): ?>
        <span class="px-3 py-1 rounded-lg bg-yellow-500 text-white font-bold">Complet</span>
    <?php else: ?>
        <span class="px-3 py-1 rounded-lg bg-green-500 text-white font-bold">Disponible</span>
    <?php endif; ?>
</td>

                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

   
    <!-- Modal : Informations Personnelles -->
    <div id="formModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
        <div class="bg-white p-6 rounded-lg w-1/3 relative">
            <button onclick="closeFormModal()" class="absolute top-2 right-2 text-gray-500 text-2xl">&times;</button>
            <h2 class="text-xl font-bold mb-4">Informations Personnelles</h2>
            <form id="rdvForm">
                <input type="text" name="cin" placeholder="CIN" class="form-control mb-2" required>
                <input type="text" name="nom" placeholder="Nom" class="form-control mb-2">
                <input type="text" name="prenom" placeholder="Prénom" class="form-control mb-2">
                <input type="email" name="email" placeholder="Email" class="form-control mb-2">
                <input type="text" name="telephone" placeholder="Téléphone" class="form-control mb-2">
                <button type="button" onclick="openHoraireModal()" class="btn btn-primary w-100">Suivant</button>
                </form>
        </div>
    </div>

    <!-- Modal : Sélection de Créneau -->
<div id="horaireModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
    <div class="bg-white p-6 rounded-lg w-3/4 relative max-h-[90vh] overflow-y-auto">
        <button onclick="closeHoraireModal()" class="absolute top-2 right-2 text-gray-500 text-2xl">&times;</button>
        <h2 class="text-xl font-bold mb-4">Sélectionnez un créneau</h2>
        
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="table-primary">
                    <tr>
                        <th>Jour</th>
                        <?php foreach ($heures as $heure): ?>
                            <th><?= $heure ?> - <?= date("H", strtotime($heure)) + 1 ?>h</th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dates as $jourLabel => $date): ?>
                        <tr>
                            <td>
                                <strong><?= $jourLabel ?></strong>
                                <?php if (isset($blockedDays[$date])): ?>
                                    <br><small class="text-danger">Bloqué: <?= htmlspecialchars($blockedDays[$date]) ?></small>
                                <?php endif; ?>
                            </td>
                            <?php foreach ($heures as $heure): 
                                $isDayBlocked = isset($blockedDays[$date]);
                                $isHourBlocked = (isset($blockedHours[$date]) && in_array($heure, $blockedHours[$date]));
                            ?>
                                <td class="<?= ($isDayBlocked || $isHourBlocked) ? 'blocked' : '' ?> text-center">
    <?php if ($isDayBlocked): ?>
        <span class="text-danger">Jour Bloqué</span>
    <?php elseif ($isHourBlocked): ?>
        <span class="tgit add .
ext-danger">Bloqué</span>
    <?php elseif (isset($capaciteRestante[$date][$heure]) && $capaciteRestante[$date][$heure] <= 0): ?>
        <span class="text-info">Complet</span>
    <?php else: ?>
        <input type="radio" name="horaire" value="<?= htmlspecialchars($date . '|' . $heure) ?>" required>
    <?php endif; ?>
</td>


                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Bouton pour valider -->
        <div class="mt-4 text-center">
            <button onclick="validerRdv()" class="btn btn-primary w-100">Confirmer</button>
        </div>
    </div>
</div>
<div id="modifierModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
    <div class="bg-white p-6 rounded-lg w-1/3 relative">
        <button onclick="fermerModifierModal()" class="absolute top-2 right-2 text-gray-500 text-2xl">&times;</button>
        <h2 class="text-xl font-bold mb-4">Modifier Rendez-vous</h2>
        <form id="modifierForm">
            <input type="hidden" name="id" id="rdvId">
            <label for="nouvelleDate">Nouvelle Date</label>
            <input type="date" name="date_rdv" id="nouvelleDate" class="form-control mb-2" required>
            <label for="nouvelleHeure">Nouvelle Heure</label>
            <select name="heure_rdv" id="nouvelleHeure" class="form-control mb-2">
                <option value="09:00">09:00</option>
                <option value="10:00">10:00</option>
                <option value="11:00">11:00</option>
                <option value="12:00">12:00</option>
                <option value="13:00">13:00</option>
                <option value="14:00">14:00</option>
                <option value="15:00">15:00</option>
                <option value="16:00">16:00</option>
            </select>
            <button type="button" onclick="modifierRdv()" class="btn btn-success w-100">Enregistrer</button>
        </form>
    </div>
</div>
</main>
</div>

<script>
    
        
        function openFormModal() {
            document.getElementById('formModal').classList.remove('hidden');
        }
        function closeFormModal() {
            document.getElementById('formModal').classList.add('hidden');
        }
        function openHoraireModal() {
            document.getElementById('horaireModal').classList.remove('hidden');
            document.getElementById('formModal').classList.add('hidden');
        }
        function closeHoraireModal() {
            document.getElementById('horaireModal').classList.add('hidden');
        }
        function validerRdv() {
            let formData = $("#rdvForm").serialize();
            let selected = document.querySelector('input[name="horaire"]:checked');
            if (!selected) {
                alert("Sélectionnez un créneau");
                return;
            }
            let [date, heure] = selected.value.split('|');
            formData += `&date_rdv=${date}&heure_rdv=${heure}:00`;
            $.post("valider_rdv.php", formData, function(response) {
                alert("Rendez-vous enregistré");
                window.location.reload();
            }, "json");
        }
        function supprimerRdv(id) {
            if (confirm("Voulez-vous vraiment supprimer ce rendez-vous ?")) {
                $.ajax({
                    url: "supprimer_rdv.php",
                    type: "POST",
                    data: { id: id },
                    success: function(response) {
                        alert("Rendez-vous supprimé");
                        window.location.reload();
                    },
                    error: function() {
                        alert("Erreur lors de la suppression");
                    }
                });
            }
        }
        $('#search').on('keyup', function() {
            let value = $(this).val().toLowerCase();
            $('#rdvTable tr').filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
            });
        });
    function ouvrirModifierModal(id, date, heure) {
        document.getElementById('rdvId').value = id;
        document.getElementById('nouvelleDate').value = date;
        document.getElementById('nouvelleHeure').value = heure;
        document.getElementById('modifierModal').classList.remove('hidden');
    }

    function fermerModifierModal() {
        document.getElementById('modifierModal').classList.add('hidden');
    }

    function modifierRdv() {
        let formData = $("#modifierForm").serialize();
        
        $.post("modifier_rdv.php", formData, function(response) {
            alert("Rendez-vous modifié !");
            window.location.reload();
        }).fail(function() {
            alert("Erreur lors de la modification !");
        });
    }


    </script>
</body>
</html>