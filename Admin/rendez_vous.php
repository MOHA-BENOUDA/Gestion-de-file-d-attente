<?php
// Affichage des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Connexion à la base de données
$host = "localhost";
$dbname = "gestion_rdv";  // Remplacez par votre base de données
$user = "root";          // Remplacez par votre utilisateur MySQL
$password = "";          // Remplacez par votre mot de passe MySQL

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Ajouter un rendez-vous
if (isset($_POST['ajouter'])) {
    $cin = isset($_POST['cin']) ? trim(htmlspecialchars($_POST['cin'])) : '';
    $nom = isset($_POST['nom']) ? trim(htmlspecialchars($_POST['nom'])) : '';
    $prenom = isset($_POST['prenom']) ? trim(htmlspecialchars($_POST['prenom'])) : '';
    $email = isset($_POST['email']) ? trim(htmlspecialchars($_POST['email'])) : '';
    $telephone = isset($_POST['telephone']) ? trim(htmlspecialchars($_POST['telephone'])) : '';
    $date_rdv = isset($_POST['date']) ? $_POST['date'] : '';
    $heure_rdv = isset($_POST['heure']) ? $_POST['heure'] : '';
    $code_unique = uniqid();
    $date_creation = date('Y-m-d H:i:s'); // Date de création du rendez-vous

    // Vérifier si l'heure est déjà occupée
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM rendez_vous WHERE date_rdv = :date_rdv AND heure_rdv = :heure_rdv");
    $stmt->execute([':date_rdv' => $date_rdv, ':heure_rdv' => $heure_rdv]);
    $rdv_existant = $stmt->fetchColumn();

    if ($rdv_existant > 0) {
        echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: 'Cette heure est déjà occupée. Veuillez choisir une autre heure.',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.href='rendez_vous.php';
                });
              </script>";
        exit();
    }

    // Insérer le rendez-vous
    try {
        $sql = "INSERT INTO rendez_vous (cin, nom, prenom, email, telephone, date_rdv, heure_rdv, code_unique, date_creation, etat) 
                VALUES (:cin, :nom, :prenom, :email, :telephone, :date_rdv, :heure_rdv, :code_unique, :date_creation, 'En attente')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':cin' => $cin,
            ':nom' => $nom,
            ':prenom' => $prenom,
            ':email' => $email,
            ':telephone' => $telephone,
            ':date_rdv' => $date_rdv,
            ':heure_rdv' => $heure_rdv,
            ':code_unique' => $code_unique,
            ':date_creation' => $date_creation
        ]);
        echo "<script>
                Swal.fire({
                    icon: 'success',
                    title: 'Succès',
                    text: 'Rendez-vous ajouté avec succès !',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.href='rendez_vous.php';
                });
              </script>";
    } catch (PDOException $e) {
        echo "Erreur : " . $e->getMessage();
    }
}

// Supprimer un rendez-vous
if (isset($_GET['supprimer'])) {
    $id = $_GET['supprimer'];
    
    // Supprimer le rendez-vous et afficher une alerte
    try {
        $stmt = $pdo->prepare("DELETE FROM rendez_vous WHERE id = ?");
        $stmt->execute([$id]);
        echo "<script>
                Swal.fire({
                    icon: 'success',
                    title: 'Supprimé',
                    text: 'Rendez-vous supprimé !',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.href='rendez_vous.php';
                });
              </script>";
    } catch (PDOException $e) {
        echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: 'Erreur lors de la suppression du rendez-vous.',
                    confirmButtonText: 'OK'
                });
              </script>";
    }
}

// Modifier l'heure et la date d'un rendez-vous
if (isset($_POST['modifier'])) {
    $id = $_POST['id'];
    $newDate = $_POST['newDate'];
    $newHeure = $_POST['newHeure'];

    // Mettre à jour la date et l'heure du rendez-vous
    try {
        $stmt = $pdo->prepare("UPDATE rendez_vous SET date_rdv = :newDate, heure_rdv = :newHeure WHERE id = :id");
        $stmt->execute([':newDate' => $newDate, ':newHeure' => $newHeure, ':id' => $id]);
        echo "<script>
                Swal.fire({
                    icon: 'success',
                    title: 'Succès',
                    text: 'Rendez-vous modifié avec succès !',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.href='rendez_vous.php';
                });
              </script>";
    } catch (PDOException $e) {
        echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: 'Erreur lors de la modification du rendez-vous.',
                    confirmButtonText: 'OK'
                });
              </script>";
    }
}

// Mettre à jour l'état des rendez-vous
$currentDateTime = date('Y-m-d H:i'); // Inclure les secondes pour une comparaison précise
try {
    // Mettre à jour l'état en "En cours" si l'heure est dépassée
    $stmt = $pdo->prepare("UPDATE rendez_vous SET etat = 'En cours' WHERE CONCAT(date_rdv, ' ', heure_rdv) <= :currentDateTime AND etat = 'En attente'");
    $stmt->execute([':currentDateTime' => $currentDateTime]);
} catch (PDOException $e) {
    echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Erreur',
                text: 'Erreur lors de la mise à jour des états.',
                confirmButtonText: 'OK'
            });
          </script>";
}

// Récupérer tous les rendez-vous
$stmt = $pdo->query("SELECT * FROM rendez_vous ORDER BY date_rdv DESC, heure_rdv DESC");
$rendez_vous = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Rendez-vous</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
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

        <!-- Contenu principal -->
        <main class="flex-1 p-6">
            <h1 class="text-2xl font-bold">📅 Gestion des Rendez-vous</h1>
            
            <!-- Formulaire d'ajout -->
            <div class="bg-white mt-6 p-4 shadow rounded-lg">
                <h3 class="text-lg font-bold">Ajouter un Rendez-vous</h3>
                <form method="POST" action="rendez_vous.php" class="mt-4">
                    <div class="grid grid-cols-2 gap-4">
                        <input type="text" name="cin" placeholder="CIN" class="p-2 border rounded" required>
                        <input type="text" name="nom" placeholder="Nom" class="p-2 border rounded" required>
                        <input type="text" name="prenom" placeholder="Prénom" class="p-2 border rounded" required>
                        <input type="email" name="email" placeholder="Email" class="p-2 border rounded" required>
                        <input type="text" name="telephone" placeholder="Téléphone" class="p-2 border rounded" required>
                        <input type="date" name="date" class="p-2 border rounded" required>
                        <input type="time" name="heure" class="p-2 border rounded" required>
                    </div>
                    <button type="submit" name="ajouter" class="mt-4 bg-blue-500 text-white p-2 rounded">Ajouter</button>
                </form>
            </div>

            <!-- Tableau des rendez-vous -->
            <div class="bg-white mt-6 p-4 shadow rounded-lg">
                <h3 class="text-lg font-bold">📅 Liste des Rendez-vous</h3>
                <table class="w-full mt-4 border">
                    <thead>
                        <tr class="bg-gray-200">
                            <th class="py-2 px-4">CIN</th>
                            <th class="py-2 px-4">Nom</th>
                            <th class="py-2 px-4">Prénom</th>
                            <th class="py-2 px-4">Email</th>
                            <th class="py-2 px-4">Téléphone</th>
                            <th class="py-2 px-4">Date</th>
                            <th class="py-2 px-4">Heure</th>
                            <th class="py-2 px-4">État</th>
                            <th class="py-2 px-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rendez_vous as $rv) : ?>
                            <tr class='border-b'>
                                <td class='py-2 px-4'><?= htmlspecialchars($rv['cin']) ?></td>
                                <td class='py-2 px-4'><?= htmlspecialchars($rv['nom']) ?></td>
                                <td class='py-2 px-4'><?= htmlspecialchars($rv['prenom']) ?></td>
                                <td class='py-2 px-4'><?= htmlspecialchars($rv['email']) ?></td>
                                <td class='py-2 px-4'><?= htmlspecialchars($rv['telephone']) ?></td>
                                <td class='py-2 px-4'><?= htmlspecialchars($rv['date_rdv']) ?></td>
                                <td class='py-2 px-4'><?= htmlspecialchars($rv['heure_rdv']) ?></td>
                                <td class='py-2 px-4 text-green-500'><?= htmlspecialchars($rv['etat']) ?></td>
                                <td class='py-2 px-4'>
                                    <a href="?supprimer=<?= $rv['id'] ?>" onclick="return confirm('Voulez-vous supprimer ce rendez-vous ?');" class='bg-red-500 text-white p-1 rounded'>Supprimer</a>
                                    <button class='bg-yellow-500 text-white p-1 rounded' onclick="openModal(<?= $rv['id'] ?>, '<?= htmlspecialchars($rv['date_rdv']) ?>', '<?= htmlspecialchars($rv['heure_rdv']) ?>')">Modifier</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Modal pour modifier l'heure et la date -->
            <div id="modal" class="fixed inset-0 flex items-center justify-center z-50 hidden">
                <div class="bg-white p-4 rounded shadow-lg">
                    <h3 class="text-lg font-bold">Modifier l'heure et la date</h3>
                    <form id="updateForm" method="POST">
                        <input type="hidden" name="id" id="modalRdvId">
                        <label for="newDate" class="block mt-2">Nouvelle Date</label>
                        <input type="date" name="newDate" id="newDate" class="p-2 border rounded" required>
                        <label for="newHeure" class="block mt-2">Nouvelle Heure</label>
                        <input type="time" name="newHeure" id="newHeure" class="p-2 border rounded" required>
                        <button type="submit" name="modifier" class="mt-4 bg-blue-500 text-white p-2 rounded">Modifier</button>
                        <button type="button" onclick="closeModal()" class="mt-2 bg-gray-500 text-white p-2 rounded">Annuler</button>
                    </form>
                </div>
            </div>

            <script>
                function openModal(id, currentDate, currentHour) {
                    document.getElementById('modalRdvId').value = id;
                    document.getElementById('newDate').value = currentDate;
                    document.getElementById('newHeure').value = currentHour; 
                    document.getElementById('modal').classList.remove('hidden');
                }

                function closeModal() {
                    document.getElementById('modal').classList.add('hidden');
                }
            </script>
        </main>
    </div>
</body>
</html>
