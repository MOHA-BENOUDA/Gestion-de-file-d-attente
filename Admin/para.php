<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paramètres - Gestion des RDV</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
<div class="flex h-screen">
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
    <h1 class="text-2xl font-bold mb-4">⚙ Paramètres</h1>
    
    <div class="bg-white p-4 shadow rounded-lg">
        <h2 class="text-lg font-bold mb-2">🔧 Paramètres généraux</h2>
        <form>
            <label class="block mb-2">Nom d'utilisateur</label>
            <input type="text" class="w-full p-2 border rounded mb-4" placeholder="Entrez votre nom">
            
            <label class="block mb-2">Email</label>
            <input type="email" class="w-full p-2 border rounded mb-4" placeholder="Entrez votre email">
            
            <label class="block mb-2">Mot de passe</label>
            <input type="password" class="w-full p-2 border rounded mb-4" placeholder="Nouveau mot de passe">
            
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Enregistrer</button>
        </form>
     </div>
    </div>
</body>
</html>
