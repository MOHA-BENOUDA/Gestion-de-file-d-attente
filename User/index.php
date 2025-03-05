<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil - Gestion de file d'attente</title>
<style> 
  @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Poppins', sans-serif;
}

body {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    background: linear-gradient(to right, #00c6ff, #0072ff);
    text-align: center;
}

.container {
    background: rgba(255, 255, 255, 0.3);
    padding: 20px;
    border-radius: 10px;
    backdrop-filter: blur(8px);
    max-width: 350px;
    width: 100%;
    box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.2);
}

h2 {
    font-size: 18px;
    color: white;
    margin-bottom: 15px;
}

.button-group {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.btn-success {
    background: #0072ff;
    border: none;
    padding: 10px;
    font-size: 14px;
    font-weight: bold;
    border-radius: 8px;
    color: white;
    transition: 0.3s ease-in-out;
    cursor: pointer;
}

.btn-success:hover {
    background: white;
    color: #0072ff;
    box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.3);
    transform: scale(1.05);
}

</style>
</head>

<body>
    <div class="container">
        <h2>Bienvenue sur la gestion de file d'attente</h2>
        <div class="button-group">
            <a href="rdv.php"><button class="btn-success">Prendre un rendez-vous</button></a>
            <a href="suivi.php"><button class="btn-success">Suivre mon tour</button></a>
        </div>
    </div>
</body>
</html>
