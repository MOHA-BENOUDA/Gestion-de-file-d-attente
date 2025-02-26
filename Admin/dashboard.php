<?php
session_start();
require_once '../includes/config.php'; // Assurez-vous que ce fichier contient $conn

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Récupérer la liste des patients en attente
$result_file_attente = $conn->query("SELECT * FROM file_attente");

// Récupérer la liste des rendez-vous
$result_rendezvous = $conn->query("SELECT * FROM rendez_vous");

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Administrateur</title>
    <link rel="stylesheet" href="dash-style.css">
</head>
<body>
    <div class="dashboard-container">
        <h2>Bienvenue, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
        <a href="logout.php">Déconnexion</a>

        <h3>Liste des Patients en Attente</h3>
        <table>
            <thead>
                <tr>
                    <th>Numéro de Passage</th>
                    <th>Nom du Patient</th>
                    <th>Heure d'Arrivée</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result_file_attente->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['numero_passage']; ?></td>
                    <td><?php echo htmlspecialchars($row['nom_patient']); ?></td>
                    <td><?php echo htmlspecialchars($row['heure_arrivee']); ?></td>
                    <td>
                        <a href="appeler_patient.php?id=<?php echo $row['id']; ?>">Appeler</a>
                        <a href="remove_patient.php?id=<?php echo $row['id']; ?>">Supprimer</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        
        <h3>Ajouter un Patient à la File</h3>
        <form method="post" action="add_patient.php">
            <label for="nom_patient">Nom du Patient:</label>
            <input type="text" id="nom_patient" name="nom_patient" required>

            <label for="heure_arrivee">Heure d'Arrivée:</label>
            <input type="time" id="heure_arrivee" name="heure_arrivee" required>

            <button type="submit">Ajouter</button>
        </form>

        <h3>Liste des Rendez-vous</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom du Patient</th>
                    <th>Date</th>
                    <th>Heure</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result_rendezvous->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['nom_patient']); ?></td>
                    <td><?php echo htmlspecialchars($row['date']); ?></td>
                    <td><?php echo htmlspecialchars($row['heure']); ?></td>
                    <td>
                        <a href="edit_rendezvous.php?id=<?php echo $row['id']; ?>">Modifier</a>
                        <a href="delete_rendezvous.php?id=<?php echo $row['id']; ?>">Annuler</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>

<?php
$conn->close();
?>
