<?php 
$current_page = basename($_SERVER['PHP_SELF']); 
?>

<aside class="fixed top-0 left-0 w-64 bg-gray-900 text-white h-full shadow-lg">
<div class="bg-dark text-white p-3" style="
    position: fixed;
    top: 0;
    left: 0;
    height: 100vh;
    width: 250px;
    padding-top: 20px;
">
    <h4 class="text-center mb-4">Gestion RDV</h4>
    <ul class="nav flex-column">
        <li class="nav-item mb-3"> 
            <a href="dashboard.php" class="nav-link text-white py-3 px-3 <?= ($current_page == 'dashboard.php') ? 'bg-primary rounded' : 'hover:bg-secondary rounded' ?>">📊 Dashboard</a>
        </li>
        <li class="nav-item mb-3">
            <a href="gsrdv.php" class="nav-link text-white py-3 px-3 <?= ($current_page == 'gsrdv.php') ? 'bg-primary rounded' : 'hover:bg-secondary rounded' ?>">📅 Rendez-vous</a>
        </li>
        <li class="nav-item mb-3">
            <a href="file.php" class="nav-link text-white py-3 px-3 <?= ($current_page == 'file.php') ? 'bg-primary rounded' : 'hover:bg-secondary rounded' ?>">⏳ File d’attente</a>
        </li>
        <li class="nav-item mb-3">
            <a href="gestion_blocages.php" class="nav-link text-white py-3 px-3 <?= ($current_page == 'gestion_blocages.php') ? 'bg-primary rounded' : 'hover:bg-secondary rounded' ?>">🚫 Blocages des jours</a>
        </li>
        <li class="nav-item mb-3">
            <a href="gestion_creneaux.php" class="nav-link text-white py-3 px-3 <?= ($current_page == 'gestion_creneaux.php') ? 'bg-primary rounded' : 'hover:bg-secondary rounded' ?>">🔴 Capacité Max</a>
        </li>
        
        <li class="nav-item mb-3">
            <a href="para.php" class="nav-link text-white py-3 px-3 <?= ($current_page == 'para.php') ? 'bg-primary rounded' : 'hover:bg-secondary rounded' ?>">⚙ Paramètres</a>
        </li>
    </ul>
</div>


</aside>
