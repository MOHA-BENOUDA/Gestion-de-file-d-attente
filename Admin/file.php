<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File d'attente - Gestion des RDV</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
    <h1 class="text-2xl font-bold mb-4">⏳ File d’attente</h1>
    
    <!-- Barre de recherche -->
    <input type="text" id="search" placeholder="Rechercher..." class="w-full p-2 border rounded mb-4">
    
    <!-- Tableau des RDV en attente -->
    <table class="w-full bg-white shadow rounded-lg border">
        <thead>
            <tr class="bg-gray-200">
                <th class="py-2 px-4">Nom</th>
                <th class="py-2 px-4">Email</th>
                <th class="py-2 px-4">Date</th>
                <th class="py-2 px-4">Motif</th>
                <th class="py-2 px-4">Actions</th>
            </tr>
        </thead>
        <tbody id="fileAttenteBody"></tbody>
    </table>
    
    <script>
        let rdvsAttente = [
            {nom: "Othmane", email: "othmane@gmail.com", date: "2025-03-10", motif: "Consultation", status: "En attente"},
            {nom: "Sara", email: "sara@mail.com", date: "2025-03-12", motif: "Suivi médical", status: "En attente"}
        ];
        
        function updateFileAttente() {
            let tbody = document.getElementById("fileAttenteBody");
            tbody.innerHTML = "";
            rdvsAttente.forEach((rdv, index) => {
                tbody.innerHTML += `
                    <tr class="border-b">
                        <td class="py-2 px-4">${rdv.nom}</td>
                        <td class="py-2 px-4">${rdv.email}</td>
                        <td class="py-2 px-4">${rdv.date}</td>
                        <td class="py-2 px-4">${rdv.motif}</td>
                        <td class="py-2 px-4">
                            <button onclick="validerRdv(${index})" class="bg-green-500 text-white px-3 py-1 rounded">Valider</button>
                            <button onclick="annulerRdv(${index})" class="bg-red-500 text-white px-3 py-1 rounded">Annuler</button>
                        </td>
                    </tr>`;
            });
        }

        function validerRdv(index) {
            alert(`Le RDV de ${rdvsAttente[index].nom} a été validé ✅`);
            rdvsAttente.splice(index, 1);
            updateFileAttente();
        }

        function annulerRdv(index) {
            alert(`Le RDV de ${rdvsAttente[index].nom} a été annulé ❌`);
            rdvsAttente.splice(index, 1);
            updateFileAttente();
        }
        
        document.getElementById("search").addEventListener("input", function() {
            let filter = this.value.toLowerCase();
            document.querySelectorAll("#fileAttenteBody tr").forEach(row => {
                row.style.display = row.innerText.toLowerCase().includes(filter) ? "table-row" : "none";
            });
        });

        updateFileAttente();
    </script>
</body>
</html>
