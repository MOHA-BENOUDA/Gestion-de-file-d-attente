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
        $sqlPos = "SELECT COUNT(*) AS total FROM file_attente";
        $resultPos = $conn->query($sqlPos);
        $rowPos = $resultPos->fetch_assoc();
        $position = $rowPos["total"] + 1;
        
        $sqlInsert = "INSERT INTO file_attente (id_rdv, position, etat) VALUES ($id_rdv, $position, 'en attente')";
        $conn->query($sqlInsert);
    }
}

// Traitement des actions POST
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["action"])) {
  if ($_POST["action"] == "passer" && isset($_POST["etat"])) {
      $sql = "SELECT fa.id FROM file_attente fa
              JOIN rendez_vous r ON fa.id_rdv = r.id
              WHERE DATE(r.date_rdv) = CURDATE() AND fa.etat = 'en attente'
              ORDER BY fa.position ASC LIMIT 1";
      $result = $conn->query($sql);
      
      if ($result && $result->num_rows > 0) {
          $row = $result->fetch_assoc();
          $id = $row["id"];
          $etat = $_POST["etat"];
          
          // Mettre à jour l'état dans la base de données
          $conn->query("UPDATE file_attente SET etat = '$etat' WHERE id = $id");
          // Récupérer l'ID de la personne en tête de file après la mise à jour
          $sqlFirst = "SELECT id FROM file_attente ORDER BY position ASC LIMIT 1";

$resultFirst = $conn->query($sqlFirst);

if ($resultFirst && $resultFirst->num_rows > 0) {
    $rowFirst = $resultFirst->fetch_assoc();
    $idFirst = $rowFirst['id'];

    // Supprimer la personne en tête de file
    $conn->query("DELETE FROM file_attente WHERE id = $idFirst");

    // Mettre à jour les positions restantes
    if ($conn->query("UPDATE file_attente SET etat = '$etat' WHERE id = $id")) {
      echo "Mise à jour réussie pour ID: $id";
  } else {
      echo "Erreur : " . $conn->error;
  }
  }

      }
  }

  



    if ($_POST["action"] == "ajouter") {
        $cin = $conn->real_escape_string($_POST["cin"]);
        $nom = $conn->real_escape_string($_POST["nom"]);
        $prenom = $conn->real_escape_string($_POST["prenom"]);
        $email = $conn->real_escape_string($_POST["email"]);
        $telephone = $conn->real_escape_string($_POST["telephone"]);
        
        $code_unique = strtoupper(substr(md5(uniqid()), 0, 2)) . rand(100000, 999999);
        
        $sqlRdv = "INSERT INTO rendez_vous (cin, nom, prenom, email, telephone, date_rdv, code_unique, etat) 
                   VALUES ('$cin', '$nom', '$prenom', '$email', '$telephone', CURDATE(), '$code_unique', 'en attente')";
        if ($conn->query($sqlRdv)) {
            $id_rdv = $conn->insert_id;
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

$sql = "SELECT r.id AS rdv_id, r.cin, r.nom, r.code_unique, fa.etat, fa.position
        FROM rendez_vous r
        JOIN file_attente fa ON r.id = fa.id_rdv
        WHERE DATE(r.date_rdv) = CURDATE()
        ORDER BY fa.position ASC";
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
<body class="bg-gray-100 p-6 ml-32">

<div class="max-w-4xl mx-auto bg-white p-6 rounded-lg shadow-lg ml-32">

  <h1 class="text-2xl font-bold text-center mb-6">📋 Gestion de la File d'Attente</h1>
  
  <input type="text" id="search" placeholder="🔍 Rechercher..." 
         class="w-full p-3 border border-gray-300 rounded mb-6" 
         onkeyup="filterPatients()">
         <div class="ml-72 p-6">
 <div class="mt-6 flex justify-between">
    <button onclick="openPasserModal()" class="bg-blue-500 text-white px-6 py-3 rounded hover:bg-blue-600">⏭️ Passer au suivant</button>
    <button onclick="openAjouterModal()" class="bg-green-500 text-white px-6 py-3 rounded hover:bg-green-600">➕ Ajouter</button>
  </div>
    <?php include 'sidebar.php'; ?>
    
    <main class="flex-1 p-6">
        <!-- Contenu spécifique de la page -->
    </main>
</div>

  <table class="w-full border-collapse border border-gray-300 shadow-md">
    <thead>
      <tr class="bg-blue-500 text-white">
        <th class="border p-3">Code Unique</th>
        <th class="border p-3">CIN</th>
        <th class="border p-3">Nom</th>
        <th class="border p-3">État</th>
      </tr>
    </thead>
    <tbody id="patient-list">
      <?php while ($row = $result->fetch_assoc()): ?>
      <tr class='border-b text-center'>
        <td class='border p-3'><?= htmlspecialchars($row['code_unique']) ?></td>
        <td class='border p-3'><?= htmlspecialchars($row['cin']) ?></td>
        <td class='border p-3'><?= htmlspecialchars($row['nom']) ?></td>
        <td class='border p-3 text-<?= ($row['etat'] === 'appelé') ? 'green-500 font-bold' : 'red-500' ?>'><?= htmlspecialchars($row['etat']) ?></td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>


<!-- MODAL POUR PASSER AU SUIVANT -->
<div id="passerModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 flex justify-center items-center">
  <div class="bg-white p-6 rounded shadow-lg w-96">
    <h2 class="text-lg font-bold mb-4">Passer au Suivant</h2>
    <p class="mb-4">Le patient est-il présent ou absent ?</p>
    
    <form id="passerForm" method="POST" action="file.php">
      <input type="hidden" name="action" value="passer">
      <input type="hidden" name="etat" id="etatSelection">

      <div class="flex justify-between">
        <button type="button" onclick="setEtat('appelé')" class="bg-green-500 text-white px-4 py-2 rounded">Présent</button>
        <button type="button" onclick="setEtat('absent')" class="bg-red-500 text-white px-4 py-2 rounded">Absent</button>
      </div>
    </form>
  </div>
</div>

<script>
  function openPasserModal() {
    document.getElementById("passerModal").classList.remove("hidden");
  }

  function setEtat(etat) {
    document.getElementById("etatSelection").value = etat;
    document.getElementById("passerForm").submit();
  }
</script>

<!-- MODAL POUR AJOUTER UN PATIENT -->
<div id="ajouterModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 flex justify-center items-center">
  <div class="bg-white p-6 rounded shadow-lg w-96">
    <h2 class="text-lg font-bold mb-4">Ajouter un Patient</h2>
    
    <form id="ajouterForm" method="POST" action="file.php">
      <input type="hidden" name="action" value="ajouter">

      <label class="block">CIN :</label>
      <input type="text" name="cin" class="w-full p-2 border rounded mb-2" required>

      <label class="block">Nom :</label>
      <input type="text" name="nom" class="w-full p-2 border rounded mb-2" required>

      <label class="block">Prénom :</label>
      <input type="text" name="prenom" class="w-full p-2 border rounded mb-2" required>

      <label class="block">Email :</label>
      <input type="email" name="email" class="w-full p-2 border rounded mb-2" required>

      <label class="block">Téléphone :</label>
      <input type="text" name="telephone" class="w-full p-2 border rounded mb-2" required>

      <div class="flex justify-end mt-4">
        <button type="button" onclick="closeAjouterModal()" class="bg-gray-400 text-white px-4 py-2 rounded mr-2">Annuler</button>
        <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded">Ajouter</button>
      </div>
    </form>
  </div>
</div>

<script>
  function openAjouterModal() {
    document.getElementById("ajouterModal").classList.remove("hidden");
  }

  function closeAjouterModal() {
    document.getElementById("ajouterModal").classList.add("hidden");
  }
</script>
<script>
  function filterPatients() {
    let searchValue = document.getElementById("search").value.toLowerCase();
    let rows = document.querySelectorAll("#patient-list tr");
    rows.forEach(row => {
      let cin = row.cells[1].innerText.toLowerCase();
      let name = row.cells[2].innerText.toLowerCase();
      row.style.display = (cin.includes(searchValue) || name.includes(searchValue)) ? "table-row" : "none";
    });
  }

  
</script>
</body>
</html>
<?php $conn->close(); ?>
