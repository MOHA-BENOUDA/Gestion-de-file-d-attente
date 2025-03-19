<?php 
$current_page = basename($_SERVER['PHP_SELF']); 
?>

<aside class="fixed top-0 left-0 w-64 bg-blue-900 text-white p-5 h-full">

    <h2 class="text-xl font-bold">Gestion RDV</h2>
    <nav class="mt-5">
        <a href="dashboard.php" class="block py-2 px-3 rounded mt-2 <?= $current_page == 'dashboard.php' ? 'bg-blue-800' : 'hover:bg-blue-700' ?>">📊 Dashboard</a>
        <a href="gsrdv.php" class="block py-2 px-3 rounded mt-2 <?= $current_page == 'gsrdv.php' ? 'bg-blue-800' : 'hover:bg-blue-700' ?>">📅 Rendez-vous</a>
        <a href="file.php" class="block py-2 px-3 rounded mt-2 <?= $current_page == 'file.php' ? 'bg-blue-800' : 'hover:bg-blue-700' ?>">⏳ File d’attente</a>
        <a href="gestion_blocages.php" class="block py-2 px-3 rounded mt-2 <?= $current_page == 'gestion_blocages.php' ? 'bg-blue-800' : 'hover:bg-blue-700' ?>">🚫 Blockages des jours</a>
        <a href="gestion_creneaux.php" class="block py-2 px-3 rounded mt-2 <?= $current_page == 'gestion_creneaux.php' ? 'bg-blue-800' : 'hover:bg-blue-700' ?>">🔴 Capacité Max</a>
        <a href="notif.php" class="block py-2 px-3 rounded mt-2 <?= $current_page == 'notif.php' ? 'bg-blue-800' : 'hover:bg-blue-700' ?>">🔔 Notifications</a>
        <a href="para.php" class="block py-2 px-3 rounded mt-2 <?= $current_page == 'para.php' ? 'bg-blue-800' : 'hover:bg-blue-700' ?>">⚙ Paramètres</a>
    </nav>
</aside>
