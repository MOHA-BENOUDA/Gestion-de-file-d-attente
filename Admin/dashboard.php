<?php
// Connexion à la base de données
include '../includes/config.php';

// Récupération des statistiques des RDV
$query_stats = "SELECT 
    (SELECT COUNT(*) FROM rendez_vous) AS total_rdv,
    (SELECT COUNT(*) FROM rendez_vous WHERE etat='En attente') AS en_attente,
    (SELECT COUNT(*) FROM rendez_vous WHERE etat='Validé') AS valides,
    (SELECT COUNT(*) FROM rendez_vous WHERE etat='Annulé') AS annules";

$result_stats = $conn->query($query_stats);
$stats = $result_stats->fetch_assoc();

// Récupération des rendez-vous pour affichage
$query_rdv = "SELECT nom, email, date_rdv, etat FROM rendez_vous ORDER BY date_rdv DESC";
$result_rdv = $conn->query($query_rdv);

// Récupération des RDV par jour pour Chart.js
$query_chart = "SELECT DATE_FORMAT(date_rdv, '%W') AS jour, COUNT(*) AS total 
                FROM rendez_vous GROUP BY jour ORDER BY FIELD(jour, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')";
$result_chart = $conn->query($query_chart);

$jours = [];
$rdv_counts = [];

while ($row = $result_chart->fetch_assoc()) {
    $jours[] = $row['jour'];
    $rdv_counts[] = $row['total'];
}

// Convertir les données en format JSON pour JavaScript
$jours_json = json_encode($jours);
$rdv_counts_json = json_encode($rdv_counts);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Gestion des RDV</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-blue-900 text-white p-5">
            <h2 class="text-xl font-bold">Gestion RDV</h2>
            <nav class="mt-5">
                <a href="dashboard.php" class="block py-2 px-3 bg-blue-800 rounded mt-2">📊 Dashboard</a>
                <a href="gsrdv.php" class="block py-2 px-3 hover:bg-blue-700 rounded mt-2">📅 Rendez-vous</a>
                <a href="file.php" class="block py-2 px-3 hover:bg-blue-700 rounded mt-2">⏳ File d’attente</a>
                <a href="gestion_blocages.php" class="block py-2 px-3 hover:bg-blue-700 rounded mt-2">🚫 Blockages des jours</a>
                <a href="admin_blockages.php" class="block py-2 px-3 hover:bg-blue-700 rounded mt-2">🔴 Capacité Max</a>
                <a href="notif.php" class="block py-2 px-3 hover:bg-blue-700 rounded mt-2">🔔 Notifications</a>
                <a href="para.php" class="block py-2 px-3 hover:bg-blue-700 rounded mt-2">⚙ Paramètres</a>
            </nav>
        </aside>

        <!-- Contenu principal -->
        <main class="flex-1 p-6">
            <h1 class="text-2xl font-bold">📊 Tableau de bord</h1>
            
            <!-- Statistiques -->
            <div class="grid grid-cols-4 gap-4 mt-6">
                <div class="bg-white p-4 shadow rounded-lg">
                    <h3 class="text-gray-600">Total RDV</h3>
                    <p class="text-2xl font-bold"><?= $stats['total_rdv'] ?></p>
                </div>
                <div class="bg-white p-4 shadow rounded-lg">
                    <h3 class="text-gray-600">En attente</h3>
                    <p class="text-2xl font-bold"><?= $stats['en_attente'] ?></p>
                </div>
                <div class="bg-white p-4 shadow rounded-lg">
                    <h3 class="text-gray-600">Validés</h3>
                    <p class="text-2xl font-bold"><?= $stats['valides'] ?></p>
                </div>
                <div class="bg-white p-4 shadow rounded-lg">
                    <h3 class="text-gray-600">Annulés</h3>
                    <p class="text-2xl font-bold"><?= $stats['annules'] ?></p>
                </div>
            </div>

            <!-- Graphique des RDV -->
            <div class="bg-white mt-6 p-4 shadow rounded-lg">
                <canvas id="chartRDV"></canvas>
            </div>

            <!-- Tableau des rendez-vous -->
            <div class="bg-white mt-6 p-4 shadow rounded-lg">
                <h3 class="text-lg font-bold">📅 Liste des Rendez-vous</h3>
                <table class="w-full mt-4">
                    <thead>
                        <tr class="bg-gray-200">
                            <th class="py-2 px-4">Nom</th>
                            <th class="py-2 px-4">Email</th>
                            <th class="py-2 px-4">Date</th>
                            <th class="py-2 px-4">État</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($rdv = $result_rdv->fetch_assoc()): ?>
                        <tr class="border-b">
                            <td class="py-2 px-4"><?= htmlspecialchars($rdv['nom']) ?></td>
                            <td class="py-2 px-4"><?= htmlspecialchars($rdv['email']) ?></td>
                            <td class="py-2 px-4"><?= htmlspecialchars($rdv['date_rdv']) ?></td>
                            <td class="py-2 px-4 font-bold 
                                <?php 
                                    if ($rdv['etat'] == 'Validé') echo 'text-green-500';
                                    elseif ($rdv['etat'] == 'Annulé') echo 'text-red-500';
                                    else echo 'text-yellow-500';
                                ?>">
                                <?= htmlspecialchars($rdv['etat']) ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script>
        // Graphique des RDV avec Chart.js
        const ctx = document.getElementById('chartRDV').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= $jours_json ?>,
                datasets: [{
                    label: 'Nombre de RDV',
                    data: <?= $rdv_counts_json ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)'
                }]
            },
        });
    </script>
</body>
</html>
