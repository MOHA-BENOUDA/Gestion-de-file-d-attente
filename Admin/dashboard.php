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
    <!-- Sidebar -->
    <div class="flex h-screen">
        <aside class="w-64 bg-blue-900 text-white p-5">
            <h2 class="text-xl font-bold">Gestion RDV</h2>
            <nav class="mt-5">
                <a href="#" class="block py-2 px-3 bg-blue-800 rounded mt-2">📊 Dashboard</a>
                <a href="gsrdv.php" class="block py-2 px-3 hover:bg-blue-700 rounded mt-2">📅 Rendez-vous</a>
                <a href="file.php" class="block py-2 px-3 hover:bg-blue-700 rounded mt-2">⏳ File d’attente</a>
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
                    <p class="text-2xl font-bold">120</p>
                </div>
                <div class="bg-white p-4 shadow rounded-lg">
                    <h3 class="text-gray-600">En attente</h3>
                    <p class="text-2xl font-bold">45</p>
                </div>
                <div class="bg-white p-4 shadow rounded-lg">
                    <h3 class="text-gray-600">Validés</h3>
                    <p class="text-2xl font-bold">60</p>
                </div>
                <div class="bg-white p-4 shadow rounded-lg">
                    <h3 class="text-gray-600">Annulés</h3>
                    <p class="text-2xl font-bold">15</p>
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
                        <tr class="border-b">
                            <td class="py-2 px-4">Othmane</td>
                            <td class="py-2 px-4">othmane@gmail.com</td>
                            <td class="py-2 px-4">06/03/2025</td>
                            <td class="py-2 px-4 text-green-500">En attente</td>
                        </tr>
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
                labels: ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi'],
                datasets: [{
                    label: 'Nombre de RDV',
                    data: [10, 20, 15, 30, 25],
                    backgroundColor: 'rgba(54, 162, 235, 0.6)'
                }]
            },
        });
    </script>
</body>
</html>
