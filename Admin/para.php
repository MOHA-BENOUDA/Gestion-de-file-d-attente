<?php
include '../includes/config.php'; 

// Ajouter un administrateur
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ajouter_admin'])) {
    $nom = $_POST['nom'];
    $email = $_POST['email'];
    $telephone = $_POST['telephone'];
    $mot_de_passe = $_POST['mot_de_passe'];

    $sql = "INSERT INTO administrateurs (nom, email, telephone, mot_de_passe) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $nom, $email, $telephone, $mot_de_passe);
    $stmt->execute();
}

// Supprimer un administrateur
if (isset($_GET['supprimer'])) {
    $id = $_GET['supprimer'];
    $conn->query("DELETE FROM administrateurs WHERE id = $id");
    header("Location: para.php");
    exit();
}

// Récupérer la liste des administrateurs
$result = $conn->query("SELECT * FROM administrateurs");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Administrateurs</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6 ml-64">

<div class="max-w-4xl mx-auto bg-white p-6 rounded-lg shadow-lg">

        <?php include 'sidebar.php'; ?>
 
    <main class="flex-1 p-6">
        <h1 class="text-2xl font-bold mb-4">⚙ Gestion des Administrateurs</h1>

        <div class="bg-white p-4 shadow rounded-lg mb-6">
            <h2 class="text-lg font-bold mb-2">➕ Ajouter un Administrateur</h2>
            <form method="POST">
                <input type="text" name="nom" class="w-full p-2 border rounded mb-2" placeholder="Nom" required>
                <input type="email" name="email" class="w-full p-2 border rounded mb-2" placeholder="Email" required>
                <input type="text" name="telephone" class="w-full p-2 border rounded mb-2" placeholder="Téléphone" required>
                <input type="password" name="mot_de_passe" class="w-full p-2 border rounded mb-2" placeholder="Mot de passe" required>
                <button type="submit" name="ajouter_admin" class="bg-blue-600 text-white px-4 py-2 rounded">Ajouter</button>
            </form>
        </div>

        <div class="bg-white p-4 shadow rounded-lg">
            <h2 class="text-lg font-bold mb-2">📋 Liste des Administrateurs</h2>
            <table class="w-full border-collapse border border-gray-200">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border p-2">ID</th>
                        <th class="border p-2">Nom</th>
                        <th class="border p-2">Email</th>
                        <th class="border p-2">Téléphone</th>
                        <th class="border p-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()) { ?>
                        <tr>
                            <td class="border p-2"><?php echo $row['id']; ?></td>
                            <td class="border p-2"><?php echo $row['nom']; ?></td>
                            <td class="border p-2"><?php echo $row['email']; ?></td>
                            <td class="border p-2"><?php echo $row['telephone']; ?></td>
                            <td class="border p-2">
                                <a href="?supprimer=<?php echo $row['id']; ?>" class="bg-red-500 text-white px-3 py-1 rounded">Supprimer</a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
</body>
</html>
