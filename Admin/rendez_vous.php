<?php
// Connexion à la base de données
$host = "localhost";
$dbname = "gestion_rdv";  // Remplacez par votre base de données
$user = "root";                 // Remplacez par votre utilisateur MySQL
$password = "";                 // Remplacez par votre mot de passe MySQL

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Ajouter un rendez-vous
if (isset($_POST['ajouter'])) {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    $telephone = $_POST['telephone'];
    $date_rdv = $_POST['date'];
    $heure_rdv = $_POST['heure'];
    $code_unique = uniqid("RDV_"); // Générer un code unique

    $sql = "INSERT INTO rendez_vous (nom, prenom, email, telephone, date_rdv, heure_rdv, code_unique) 
            VALUES (:nom, :prenom, :email, :telephone, :date_rdv, :heure_rdv, :code_unique)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':nom' => $nom,
        ':prenom' => $prenom,
        ':email' => $email,
        ':telephone' => $telephone,
        ':date_rdv' => $date_rdv,
        ':heure_rdv' => $heure_rdv,
        ':code_unique' => $code_unique
    ]);

    echo "<script>alert('Rendez-vous ajouté avec succès !'); window.location.href='rendez_vous.php';</script>";
}

// Supprimer un rendez-vous
if (isset($_GET['supprimer'])) {
    $id = $_GET['supprimer'];
    $stmt = $pdo->prepare("DELETE FROM rendez_vous WHERE id = ?");
    $stmt->execute([$id]);
    echo "<script>alert('Rendez-vous supprimé !'); window.location.href='rendez_vous.php';</script>";
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
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-blue-900 text-white p-5">
            <h2 class="text-xl font-bold">Gestion RDV</h2>
            <nav class="mt-5">
                <a href="dashboard.php" class="block py-2 px-3 hover:bg-blue-800 rounded mt-2">📊 Dashboard</a>
                <a href="rendez_vous.php" class="block py-2 px-3 bg-blue-800 rounded mt-2">📅 Rendez-vous</a>
                <a href="#" class="block py-2 px-3 hover:bg-blue-700 rounded mt-2">⏳ File d’attente</a>
                <a href="#" class="block py-2 px-3 hover:bg-blue-700 rounded mt-2">🔔 Notifications</a>
                <a href="#" class="block py-2 px-3 hover:bg-blue-700 rounded mt-2">⚙ Paramètres</a>
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
                                <td class='py-2 px-4'><?= htmlspecialchars($rv['nom']) ?></td>
                                <td class='py-2 px-4'><?= htmlspecialchars($rv['prenom']) ?></td>
                                <td class='py-2 px-4'><?= htmlspecialchars($rv['email']) ?></td>
                                <td class='py-2 px-4'><?= htmlspecialchars($rv['telephone']) ?></td>
                                <td class='py-2 px-4'><?= htmlspecialchars($rv['date_rdv']) ?></td>
                                <td class='py-2 px-4'><?= htmlspecialchars($rv['heure_rdv']) ?></td>
                                <td class='py-2 px-4 text-green-500'><?= htmlspecialchars($rv['etat']) ?></td>
                                <td class='py-2 px-4'>
                                    <a href="?supprimer=<?= $rv['id'] ?>" onclick="return confirm('Voulez-vous supprimer ce rendez-vous ?');" class='bg-red-500 text-white p-1 rounded'>Supprimer</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>
