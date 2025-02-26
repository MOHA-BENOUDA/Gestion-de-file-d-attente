<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suivre Mon Tour</title>
    <link rel="stylesheet" href="css/suivi.css">
</head>
<body>
    <div class="container">
        <!-- Formulaire pour entrer le code unique -->
        <h1>Suivre Mon Tour</h1>
        <form id="codeForm">
            <label for="code">Entrez votre code unique :</label>
            <input type="text" id="code" name="code" required>
            <button type="submit" class="btn">Valider</button>
        </form>

        <!-- Section d'information utilisateur -->
        <div id="userInfo" style="display: none;">
            <h2>Informations personnelles</h2>
            <p id="info"></p>
            <p id="queueMessage"></p>
        </div>
    </div>

    <script>
        // Simulation d'un code valide et des informations personnelles
        const validCode = "12345";  // Code unique valide (exemple)
        const userData = {
            name: "Jean Dupont",
            age: 28,
            phone: "0123456789",
            email: "jean.dupont@example.com",
            queuePosition: 3  // Position dans la file d'attente
        };

        const queueLength = 10;  // Nombre total de personnes dans la file d'attente

        // Formulaire et affichage de l'élément de code
        const codeForm = document.getElementById('codeForm');
        const codeInput = document.getElementById('code');
        const userInfo = document.getElementById('userInfo');
        const info = document.getElementById('info');
        const queueMessage = document.getElementById('queueMessage');

        // Lorsque le formulaire est soumis
        codeForm.addEventListener('submit', function(event) {
            event.preventDefault();  // Empêche l'envoi du formulaire

            // Vérifier le code entré
            const enteredCode = codeInput.value.trim();
            if (enteredCode === validCode) {
                // Si le code est valide, afficher les informations
                codeForm.style.display = 'none';  // Masquer le formulaire
                userInfo.style.display = 'block';  // Afficher les informations

                // Affichage des informations personnelles
                info.innerHTML = `
                    <strong>Nom :</strong> ${userData.name}<br>
                    <strong>Âge :</strong> ${userData.age}<br>
                    <strong>Téléphone :</strong> ${userData.phone}<br>
                    <strong>Email :</strong> ${userData.email}
                `;

                // Calcul du nombre de personnes restantes avant son tour
                const remainingPeople = queueLength - userData.queuePosition;
                queueMessage.innerHTML = `Il reste ${remainingPeople} personne(s) avant votre tour.`;

            } else {
                // Si le code est invalide
                alert("Code invalide. Essayez à nouveau.");
            }
        });
    </script>
</body>
</html>
