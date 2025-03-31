<?php
// Connexion à la base de données
include '../includes/config.php';

// Récupération des statistiques globales
$query_stats = "SELECT 
    (SELECT COUNT(*) FROM rendez_vous) AS total_rdv,
    (SELECT COUNT(*) FROM rendez_vous WHERE etat='En attente') AS en_attente,
    (SELECT COUNT(*) FROM rendez_vous WHERE etat='Validé') AS valides,
    (SELECT COUNT(*) FROM rendez_vous WHERE etat='Annulé') AS annules";
$result_stats = $conn->query($query_stats);
$stats = $result_stats->fetch_assoc();

// Capacité maximale
$globale_result = $conn->query("SELECT capacite_max FROM capacite_globale LIMIT 1");
$globale = $globale_result->fetch_assoc();
$capacite_max = $globale['capacite_max'];

// File d'attente
$result_file = $conn->query("SELECT COUNT(*) AS en_file FROM file_attente WHERE etat='En attente'");
$file_attente = $result_file->fetch_assoc()['en_file'];

// Jours et heures bloqués
$query_blocage = "SELECT 
    (SELECT COUNT(*) FROM jours_bloques) AS jours_bloques, 
    (SELECT COUNT(*) FROM heures_bloquees) AS heures_bloquees";
$result_blocage = $conn->query($query_blocage);
$blocage = $result_blocage->fetch_assoc();

// Données pour le graphique
$query_chart = "SELECT DATE_FORMAT(date_rdv, '%Y-%m-%d') AS jour, COUNT(*) AS total 
                FROM rendez_vous GROUP BY jour ORDER BY jour ASC";
$result_chart = $conn->query($query_chart);
$jours = [];
$rdv_counts = [];
while ($row = $result_chart->fetch_assoc()) {
    $jours[] = $row['jour'];
    $rdv_counts[] = $row['total'];
}
$jours_json = json_encode($jours);
$rdv_counts_json = json_encode($rdv_counts);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Gestion des RDV</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-light">
    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-2 bg-dark text-white p-3 min-vh-100">
                <h4 class="text-center">Gestion RDV</h4>
                <ul class="nav flex-column mt-4">
    <li class="nav-item">
        <a href="dashboard.php" class="nav-link text-white <?= $current_page == 'dashboard.php' ? 'bg-blue-800' : 'hover:bg-blue-700' ?>">📊 Dashboard</a>
    </li>
    <li class="nav-item">
        <a href="gsrdv.php" class="nav-link text-white <?= $current_page == 'gsrdv.php' ? 'bg-blue-800' : 'hover:bg-blue-700' ?>">📅 Rendez-vous</a>
    </li>
    <li class="nav-item">
        <a href="file.php" class="nav-link text-white <?= $current_page == 'file.php' ? 'bg-blue-800' : 'hover:bg-blue-700' ?>">⏳ File d’attente</a>
    </li>
    <li class="nav-item">
        <a href="gestion_blocages.php" class="nav-link text-white <?= $current_page == 'gestion_blocages.php' ? 'bg-blue-800' : 'hover:bg-blue-700' ?>">🚫 Blocages des jours</a>
    </li>
    <li class="nav-item">
        <a href="gestion_creneaux.php" class="nav-link text-white <?= $current_page == 'gestion_creneaux.php' ? 'bg-blue-800' : 'hover:bg-blue-700' ?>">🔴 Capacité Max</a>
    </li>
  
    <li class="nav-item">
        <a href="para.php" class="nav-link text-white <?= $current_page == 'para.php' ? 'bg-blue-800' : 'hover:bg-blue-700' ?>">⚙ Paramètres</a>
    </li>
</ul>

            </nav>
            <main class="col-md-10 p-4">
                <h2>📊 Tableau de bord</h2>
                <div class="row">
                    <div class="col-md-3"><div class="card bg-primary text-white p-3"><h5>Total RDV</h5><p class="display-6"><?= $stats['total_rdv'] ?></p></div></div>
                    <div class="col-md-3"><div class="card bg-warning text-white p-3"><h5>En attente</h5><p class="display-6"><?= $stats['en_attente'] ?></p></div></div>
                    <div class="col-md-3"><div class="card bg-success text-white p-3"><h5>Validés</h5><p class="display-6"><?= $stats['valides'] ?></p></div></div>
                    <div class="col-md-3"><div class="card bg-danger text-white p-3"><h5>Annulés</h5><p class="display-6"><?= $stats['annules'] ?></p></div></div>
                </div>
                <div class="row mt-4">
                    <div class="col-md-4"><div class="card bg-info text-white p-3"><h5>🕒 File d'attente</h5><p class="display-6"><?= $file_attente ?></p></div></div>
                    <div class="col-md-4"><div class="card bg-secondary text-white p-3"><h5>🚀 Capacité max</h5><p class="display-6"><?= $capacite_max ?></p></div></div>
                    <div class="col-md-4"><div class="card bg-dark text-white p-3"><h5>🚫 Jours bloqués</h5><p class="display-6"><?= $blocage['jours_bloques'] ?></p></div></div>
                </div>
                <div class="card p-4 mt-4">
                    <h5>📈 Évolution des RDV</h5>
                    <canvas id="chartRDV"></canvas>
                </div>
            </main>
        </div>
    </div>
    <script>
        const ctx = document.getElementById('chartRDV').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= $jours_json ?>,
                datasets: [{
                    label: 'Nombre de RDV',
                    data: <?= $rdv_counts_json ?>,
                    borderColor: 'rgba(54, 162, 235, 1)',
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderWidth: 2,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                scales: {
                    x: { title: { display: true, text: 'Date' } },
                    y: { title: { display: true, text: 'Nombre de RDV' }, beginAtZero: true }
                }
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
