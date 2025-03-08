<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, sans-serif; }
        body { display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100vh; background: #f4f4f4; }
        h1 { margin-bottom: 20px; }
        .container { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
        .btn {
            width: 250px;
            height: 150px;
            border: none;
            color: white;
            font-size: 18px;
            font-weight: bold;
            border-radius: 10px;
            cursor: pointer;
            transition: 0.3s;
        }
        .btn:hover { transform: scale(1.1); }
        .rdv { background: #3498db; }      /* Bleu */
        .file { background: #e67e22; }     /* Orange */
        .jour { background: #2ecc71; }     /* Vert */
        .heure { background: #e74c3c; }    /* Rouge */
    </style>
</head>
<body>
    <h1>Tableau de Bord Administrateur</h1>
    <div class="container">
        <button class="btn rdv">📆 Gestion des Rendez-vous</button>
        <button class="btn file">⏳ Gestion de la File d'Attente</button>
        <button class="btn jour">📅 Jours Bloqués</button>
        <button class="btn heure">⏰ Heures Bloquées</button>
    </div>
</body>
</html>
