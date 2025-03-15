<?php
session_start();

// Connexion à la base de données
$conn = new mysqli("localhost", "root", "", "gestion_rdv");
if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
}

// Synchronisation des rendez-vous du jour dans la file d'attente
$sqlSync = "SELECT id FROM rendez_vous 
            WHERE DATE(date_rdv) = CURDATE()
            AND id NOT IN (SELECT id_rdv FROM file_attente)";
$resultSync = $conn->query($sqlSync);
if ($resultSync) {
    while ($rowSync = $resultSync->fetch_assoc()) {
        $id_rdv = $rowSync["id"];
        $sqlPos = "SELECT MAX(position) AS max_pos FROM file_attente";
        $resultPos = $conn->query($sqlPos);
        $rowPos = $resultPos->fetch_assoc();
        $position = ($rowPos["max_pos"] ?? 0) + 1;
        $sqlInsert = "INSERT INTO file_attente (id_rdv, position, etat) VALUES ($id_rdv, $position, 'en attente')";
        $conn->query($sqlInsert);
    }
}

// Traitement des actions POST
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["action"])) {
    if ($_POST["action"] == "passer") {
        $sql = "SELECT fa.id FROM file_attente fa
                JOIN rendez_vous r ON fa.id_rdv = r.id
                WHERE DATE(r.date_rdv) = CURDATE() AND fa.etat = 'en attente'
                ORDER BY r.heure_rdv ASC LIMIT 1";
        $result = $conn->query($sql);
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $id = $row["id"];
            $conn->query("UPDATE file_attente SET etat = 'appelé' WHERE id = $id");
        }
    }

    if ($_POST["action"] == "ajouter") {
        $nom = $conn->real_escape_string($_POST["nom"]);
        $heure = $conn->real_escape_string($_POST["heure"]);
        $sqlCheck = "SELECT id FROM rendez_vous WHERE nom='$nom' AND heure_rdv='$heure' AND DATE(date_rdv)=CURDATE() LIMIT 1";
        $resCheck = $conn->query($sqlCheck);
        if ($resCheck && $resCheck->num_rows > 0) {
            $row = $resCheck->fetch_assoc();
            $id_rdv = $row["id"];
        } else {
            $sqlRdv = "INSERT INTO rendez_vous (cin, nom, prenom, email, telephone, date_rdv, heure_rdv, code_unique, etat) 
                       VALUES ('', '$nom', '', '', '', CURDATE(), '$heure', CONCAT('RDV', NOW()), 'en attente')";
            if ($conn->query($sqlRdv)) {
                $id_rdv = $conn->insert_id;
            }
        }
        $sqlFileCheck = "SELECT id FROM file_attente WHERE id_rdv = $id_rdv";
        $resFileCheck = $conn->query($sqlFileCheck);
        if ($resFileCheck->num_rows == 0) {
            $sqlPos = "SELECT MAX(position) AS max_pos FROM file_attente";
            $resultPos = $conn->query($sqlPos);
            $rowPos = $resultPos->fetch_assoc();
            $position = ($rowPos["max_pos"] ?? 0) + 1;
            $conn->query("INSERT INTO file_attente (id_rdv, position, etat) VALUES ($id_rdv, $position, 'en attente')");
        }
    }
    header("Location: file.php");
    exit();
}

$sql = "SELECT r.id AS rdv_id, r.nom, r.heure_rdv, fa.etat, fa.position
        FROM rendez_vous r
        JOIN file_attente fa ON r.id = fa.id_rdv
        WHERE DATE(r.date_rdv) = CURDATE()
        ORDER BY r.heure_rdv ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>File d'Attente</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
<div class="max-w-4xl mx-auto bg-white p-6 rounded-lg shadow-lg">
  <h1 class="text-2xl font-bold text-center mb-6">📋 Gestion de la File d'Attente</h1>
  <input type="text" id="search" placeholder="🔍 Rechercher un patient..." 
         class="w-full p-3 border border-gray-300 rounded mb-6 focus:outline-none focus:ring-2 focus:ring-blue-500" 
         onkeyup="filterPatients()">
  <table class="w-full border-collapse border border-gray-300 shadow-md">
    <thead>
      <tr class="bg-blue-500 text-white">
        <th class="border p-3">Nom</th>
        <th class="border p-3">Heure RDV</th>
        <th class="border p-3">État</th>
      </tr>
    </thead>
    <tbody id="patient-list">
      <?php while ($row = $result->fetch_assoc()): ?>
      <tr class='border-b text-center'>
        <td class='border p-3'><?= htmlspecialchars($row['nom']) ?></td>
        <td class='border p-3'><?= htmlspecialchars($row['heure_rdv']) ?></td>
        <td class='border p-3 text-<?= ($row['etat'] === 'appelé') ? 'green-500 font-bold' : 'red-500' ?>'><?= htmlspecialchars($row['etat']) ?></td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
  <div class="mt-6 flex flex-col md:flex-row items-center justify-between gap-6">
    <form method="POST" action="file.php">
      <input type="hidden" name="action" value="passer">
      <button type="submit" class="bg-blue-500 text-white px-6 py-3 rounded hover:bg-blue-600">⏭️ Passer au suivant</button>
    </form>
    <form method="POST" action="file.php" class="flex flex-col md:flex-row gap-4">
      <input type="hidden" name="action" value="ajouter">
      <input type="text" name="nom" placeholder="Nom" required class="border p-3 rounded">
      <input type="time" name="heure" required class="border p-3 rounded">
      <button type="submit" class="bg-green-500 text-white px-6 py-3 rounded hover:bg-green-600">➕ Ajouter</button>
    </form>
  </div>
</div>
<script>
  function filterPatients() {
    let searchValue = document.getElementById("search").value.toLowerCase();
    let rows = document.querySelectorAll("#patient-list tr");
    rows.forEach(row => {
      let name = row.cells[0].innerText.toLowerCase();
      row.style.display = name.includes(searchValue) ? "table-row" : "none";
    });
  }
</script>
</body>
</html>
<?php $conn->close(); ?>
