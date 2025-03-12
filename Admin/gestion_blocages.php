<?php
session_start();
$conn = new mysqli("localhost", "root", "", "gestion_rdv");
if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
}

// Traitement des formulaires
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action']) && $_POST['action'] == 'ajouter_jour') {
        // Ajouter un jour bloqué
        $date_bloquee = $conn->real_escape_string($_POST['date_bloquee']);
        $raison = $conn->real_escape_string($_POST['raison']);
        $sql = "INSERT INTO jours_bloques (date_bloquee, raison) VALUES ('$date_bloquee', '$raison')";
        $conn->query($sql);
    }
    if (isset($_POST['action']) && $_POST['action'] == 'ajouter_heure') {
        // Ajouter une heure bloquée
        $date_bloquee = $conn->real_escape_string($_POST['date_bloquee']);
        $heure_bloquee = $conn->real_escape_string($_POST['heure_bloquee']);
        $raison = $conn->real_escape_string($_POST['raison']);
        $sql = "INSERT INTO heures_bloquees (date_bloquee, heure_bloquee, raison) VALUES ('$date_bloquee', '$heure_bloquee', '$raison')";
        $conn->query($sql);
    }
    header("Location: gestion_blocages.php");
    exit();
}

// Récupérer les jours bloqués
$sqlJours = "SELECT * FROM jours_bloques ORDER BY date_bloquee ASC";
$resultJours = $conn->query($sqlJours);

// Récupérer les heures bloquées
$sqlHeures = "SELECT * FROM heures_bloquees ORDER BY date_bloquee ASC, heure_bloquee ASC";
$resultHeures = $conn->query($sqlHeures);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Blocages</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-4xl mx-auto bg-white p-6 rounded shadow">
        <h1 class="text-2xl font-bold mb-4">Gestion des Jours et Heures Bloqués</h1>

        <!-- Formulaire pour ajouter un jour bloqué -->
        <h2 class="text-xl font-semibold mt-4">Ajouter un jour bloqué</h2>
        <form method="POST" action="gestion_blocages.php" class="mt-2">
            <input type="hidden" name="action" value="ajouter_jour">
            <div class="mb-2">
                <label class="block">Date bloquée (AAAA-MM-JJ) :</label>
                <input type="date" name="date_bloquee" required class="border p-2 rounded w-full">
            </div>
            <div class="mb-2">
                <label class="block">Raison :</label>
                <input type="text" name="raison" required class="border p-2 rounded w-full">
            </div>
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Ajouter Jour Bloqué</button>
        </form>

        <!-- Formulaire pour ajouter une heure bloquée -->
        <h2 class="text-xl font-semibold mt-6">Ajouter une heure bloquée</h2>
        <form method="POST" action="gestion_blocages.php" class="mt-2">
            <input type="hidden" name="action" value="ajouter_heure">
            <div class="mb-2">
                <label class="block">Date (AAAA-MM-JJ) :</label>
                <input type="date" name="date_bloquee" required class="border p-2 rounded w-full">
            </div>
            <div class="mb-2">
                <label class="block">Heure bloquée (HH:MM) :</label>
                <input type="time" name="heure_bloquee" required class="border p-2 rounded w-full">
            </div>
            <div class="mb-2">
                <label class="block">Raison :</label>
                <input type="text" name="raison" required class="border p-2 rounded w-full">
            </div>
            <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded">Ajouter Heure Bloquée</button>
        </form>

        <!-- Affichage des jours bloqués -->
        <h2 class="text-xl font-semibold mt-6">Jours Bloqués</h2>
        <ul class="list-disc pl-5">
            <?php
            if ($resultJours && $resultJours->num_rows > 0) {
                while ($row = $resultJours->fetch_assoc()) {
                    echo "<li>{$row['date_bloquee']} - {$row['raison']}</li>";
                }
            } else {
                echo "<li>Aucun jour bloqué.</li>";
            }
            ?>
        </ul>

        <!-- Affichage des heures bloquées -->
        <h2 class="text-xl font-semibold mt-6">Heures Bloquées</h2>
        <ul class="list-disc pl-5">
            <?php
            if ($resultHeures && $resultHeures->num_rows > 0) {
                while ($row = $resultHeures->fetch_assoc()) {
                    echo "<li>{$row['date_bloquee']} à {$row['heure_bloquee']} - {$row['raison']}</li>";
                }
            } else {
                echo "<li>Aucune heure bloquée.</li>";
            }
            ?>
        </ul>
    </div>
</body>
</html>
<?php $conn->close(); ?>
