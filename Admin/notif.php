<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - Gestion des RDV</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
    <h1 class="text-2xl font-bold mb-4">🔔 Notifications</h1>
    
    <div class="bg-white p-4 shadow rounded-lg">
        <h2 class="text-lg font-bold mb-2">📢 Dernières Notifications</h2>
        <ul id="notificationList" class="divide-y divide-gray-200">
            <li class="py-3 px-4 hover:bg-gray-50 cursor-pointer">✅ Votre rendez-vous avec Dr. Karim est confirmé.</li>
            <li class="py-3 px-4 hover:bg-gray-50 cursor-pointer">📅 Un nouveau créneau de RDV est disponible pour demain.</li>
            <li class="py-3 px-4 hover:bg-gray-50 cursor-pointer">⚠️ Votre RDV du 12/03/2025 a été modifié.</li>
        </ul>
    </div>
</body>
</html>
