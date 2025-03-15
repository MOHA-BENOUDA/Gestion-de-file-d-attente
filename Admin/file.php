<?php
session_start();

// Connexion à la base de données
$conn = new mysqli("localhost", "root", "", "gestion_rdv");
if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
}

// Synchronisation : Ajouter en file d'attente tous les rendez-vous de la journée qui n'y sont pas encore
$sqlSync = "SELECT id FROM rendez_vous 
            WHERE DATE(date_rdv) = CURDATE()
            AND id NOT IN (SELECT id_rdv FROM file_attente)";
$resultSync = $conn->query($sqlSync);
if ($resultSync) {
    while ($rowSync = $resultSync->fetch_assoc()) {
        $id_rdv = $rowSync["id"];
        // Détermination de la position : max(position) + 1
        $sqlPos = "SELECT MAX(position) AS max_pos FROM file_attente";
        $resultPos = $conn->query($sqlPos);
        $rowPos = $resultPos->fetch_assoc();
        $position = ($rowPos["max_pos"] ?? 0) + 1;
        // Insertion dans la file d'attente
        $sqlInsert = "INSERT INTO file_attente (id_rdv, position, etat) VALUES ($id_rdv, $position, 'en attente')";
        $conn->query($sqlInsert);
    }
}

// Traitement des actions POST
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["action"])) {

    // Action : Passer au patient suivant (uniquement parmi ceux en attente)
    if ($_POST["action"] == "passer") {
        $sql = "SELECT fa.id 
                FROM file_attente fa
                JOIN rendez_vous r ON fa.id_rdv = r.id
                WHERE DATE(r.date_rdv) = CURDATE() AND fa.etat = 'en attente'
                ORDER BY r.heure_rdv ASC
                LIMIT 1";
        $result = $conn->query($sql);
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $id = $row["id"];
            $updateSql = "UPDATE file_attente SET etat = 'appelé' WHERE id = $id";
            $conn->query($updateSql);
        }
    }

    // Action : Ajouter un nouveau patient dans la file
    if ($_POST["action"] == "ajouter") {
        $nom = $conn->real_escape_string($_POST["nom"]);
        $heure = $conn->real_escape_string($_POST["heure"]);

        // Vérifier si un rendez-vous existe déjà pour ce patient aujourd'hui
        $sqlCheck = "SELECT id FROM rendez_vous 
                     WHERE nom='$nom' AND heure_rdv='$heure' AND DATE(date_rdv)=CURDATE() LIMIT 1";
        $resCheck = $conn->query($sqlCheck);
        if ($resCheck && $resCheck->num_rows > 0) {
            $row = $resCheck->fetch_assoc();
            $id_rdv = $row["id"];
        } else {
            // Création d'un nouveau rendez-vous pour aujourd'hui
            $sqlRdv = "INSERT INTO rendez_vous (cin, nom, prenom, email, telephone, date_rdv, heure_rdv, code_unique, etat) 
                       VALUES ('', '$nom', '', '', '', CURDATE(), '$heure', CONCAT('RDV', NOW()), 'en attente')";
            if ($conn->query($sqlRdv)) {
                $id_rdv = $conn->insert_id;
            }
        }
        // Vérifier que le rendez-vous n'est pas déjà dans la file (bien que la synchronisation devrait l'ajouter)
        $sqlFileCheck = "SELECT id FROM file_attente WHERE id_rdv = $id_rdv";
        $resFileCheck = $conn->query($sqlFileCheck);
        if ($resFileCheck && $resFileCheck->num_rows == 0) {
            $sqlPos = "SELECT MAX(position) AS max_pos FROM file_attente";
            $resultPos = $conn->query($sqlPos);
            $rowPos = $resultPos->fetch_assoc();
            $position = ($rowPos["max_pos"] ?? 0) + 1;
            $sqlFile = "INSERT INTO file_attente (id_rdv, position, etat) VALUES ($id_rdv, $position, 'en attente')";
            $conn->query($sqlFile);
        }
    }

    // Redirection pour éviter la double soumission
    header("Location: file.php");
    exit();
}

// Requête pour afficher uniquement les patients dans la file d'attente
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
   <div class="flex h-screen">
    <div  class="max-w-4xl mx-auto bg-white p-6 rounded-lg shadow-lg">
    <h1 class="text-2xl font-bold text-center mb-6">📋 Gestion de la File d'Attente</h1>
   
        <!-- Sidebar -->
        <aside class="w-64 bg-blue-900 text-white p-5">
            <h2 class="text-xl font-bold">Gestion RDV</h2>
            <nav class="mt-5">
                <a href="dashboard.php" class="block py-2 px-3 hover:bg-blue-800 rounded mt-2">📊 Dashboard</a>
                <a href="rendez_vous.php" class="block py-2 px-3 bg-blue-800 rounded mt-2">📅 Rendez-vous</a>
                <a href="file.php" class="block py-2 px-3 hover:bg-blue-700 rounded mt-2">⏳ File d’attente</a>
                <a href="notif.php" class="block py-2 px-3 hover:bg-blue-700 rounded mt-2">🔔 Notifications</a>
                <a href="para.php" class="block py-2 px-3 hover:bg-blue-700 rounded mt-2">⚙ Paramètres</a>
            </nav>
        </aside>
      
    <!-- Barre de recherche -->
    <input type="text" id="search" placeholder="🔍 Rechercher un patient..." 
           class="w-full p-3 border border-gray-300 rounded mb-6 focus:outline-none focus:ring-2 focus:ring-blue-500" 
           onkeyup="filterPatients()">

    <!-- Tableau des patients -->
    <div class="overflow-x-auto">
      <table class="w-full border-collapse border border-gray-300 shadow-md">
        <thead>
          <tr class="bg-blue-500 text-white">
            <th class="border p-3">Nom</th>
            <th class="border p-3">Heure RDV</th>
            <th class="border p-3">État</th>
          </tr>
        </thead>
        <tbody id="patient-list">
          <?php
          if ($result && $result->num_rows > 0) {
              while ($row = $result->fetch_assoc()) {
                  // Définition de la couleur selon l'état
                  $etat = $row['etat'];
                  $etatColor = "text-red-500"; // Par défaut pour "en attente"
                  if ($etat === 'appelé') {
                      $etatColor = "text-green-500 font-bold";
                  } elseif ($etat === 'terminé') {
                      $etatColor = "text-gray-500";
                  }
                  echo "<tr class='border-b text-center'>";
                  echo "<td class='border p-3'>" . htmlspecialchars($row['nom']) . "</td>";
                  echo "<td class='border p-3'>" . htmlspecialchars($row['heure_rdv']) . "</td>";
                  echo "<td class='border p-3 $etatColor'>" . htmlspecialchars($etat) . "</td>";
                  echo "</tr>";
              }
          } else {
              echo "<tr><td colspan='3' class='p-4 text-center text-gray-500'>Aucun patient dans la file pour aujourd'hui.</td></tr>";
          }
          ?>
        </tbody>
      </table>
    </div>

    <!-- Boutons d'action -->
    <div class="mt-6 flex flex-col md:flex-row items-center justify-between gap-6">
      <!-- Formulaire pour passer au patient suivant -->
      <form method="POST" action="file.php" class="w-full md:w-auto">
        <input type="hidden" name="action" value="passer">
        <button type="submit" class="bg-blue-500 w-full md:w-auto text-white px-6 py-3 rounded hover:bg-blue-600">
          ⏭️ Passer au suivant
        </button>
      </form>
      <!-- Formulaire pour ajouter un nouveau patient dans la file -->
      <form method="POST" action="file.php" class="flex flex-col md:flex-row w-full md:w-auto gap-4">
        <input type="hidden" name="action" value="ajouter">
        <input type="text" name="nom" placeholder="Nom du patient" required 
               class="border p-3 rounded focus:outline-none focus:ring-2 focus:ring-green-500">
        <input type="time" name="heure" required class="border p-3 rounded focus:outline-none focus:ring-2 focus:ring-green-500">
        <button type="submit" class="bg-green-500 text-white px-6 py-3 rounded hover:bg-green-600">
          ➕ Ajouter
        </button>
      </form>
    </div>
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
</div>

</body>
</html>

<?php
$conn->close();
?>