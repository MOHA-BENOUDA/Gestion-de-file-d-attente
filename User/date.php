<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choisir Date et Heure</title>
    <link rel="stylesheet" href="css/date.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center mb-4">Sélectionner une date et une heure</h2>
        
        <form action="traitement_rdv.php" method="POST">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Heures</th>
                            <th>Lundi</th>
                            <th>Mardi</th>
                            <th>Mercredi</th>
                            <th>Jeudi</th>
                            <th>Vendredi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Boucle sur les horaires de 9h à 16h -->
                        <tr>
                            <td>9h - 10h</td>
                            <td><input type="radio" name="horaire_lundi" value="9-10"></td>
                            <td><input type="radio" name="horaire_mardi" value="9-10"></td>
                            <td><input type="radio" name="horaire_mercredi" value="9-10"></td>
                            <td><input type="radio" name="horaire_jeudi" value="9-10"></td>
                            <td><input type="radio" name="horaire_vendredi" value="9-10"></td>
                        </tr>
                        <tr>
                            <td>10h - 11h</td>
                            <td><input type="radio" name="horaire_lundi" value="10-11"></td>
                            <td><input type="radio" name="horaire_mardi" value="10-11"></td>
                            <td><input type="radio" name="horaire_mercredi" value="10-11"></td>
                            <td><input type="radio" name="horaire_jeudi" value="10-11"></td>
                            <td><input type="radio" name="horaire_vendredi" value="10-11"></td>
                        </tr>
                        <tr>
                            <td>11h - 12h</td>
                            <td><input type="radio" name="horaire_lundi" value="11-12"></td>
                            <td><input type="radio" name="horaire_mardi" value="11-12"></td>
                            <td><input type="radio" name="horaire_mercredi" value="11-12"></td>
                            <td><input type="radio" name="horaire_jeudi" value="11-12"></td>
                            <td><input type="radio" name="horaire_vendredi" value="11-12"></td>
                        </tr>
                        <tr>
                            <td>12h - 13h</td>
                            <td><input type="radio" name="horaire_lundi" value="12-13"></td>
                            <td><input type="radio" name="horaire_mardi" value="12-13"></td>
                            <td><input type="radio" name="horaire_mercredi" value="12-13"></td>
                            <td><input type="radio" name="horaire_jeudi" value="12-13"></td>
                            <td><input type="radio" name="horaire_vendredi" value="12-13"></td>
                        </tr>
                        <tr>
                            <td>13h - 14h</td>
                            <td><input type="radio" name="horaire_lundi" value="13-14"></td>
                            <td><input type="radio" name="horaire_mardi" value="13-14"></td>
                            <td><input type="radio" name="horaire_mercredi" value="13-14"></td>
                            <td><input type="radio" name="horaire_jeudi" value="13-14"></td>
                            <td><input type="radio" name="horaire_vendredi" value="13-14"></td>
                        </tr>
                        <tr>
                            <td>14h - 15h</td>
                            <td><input type="radio" name="horaire_lundi" value="14-15"></td>
                            <td><input type="radio" name="horaire_mardi" value="14-15"></td>
                            <td><input type="radio" name="horaire_mercredi" value="14-15"></td>
                            <td><input type="radio" name="horaire_jeudi" value="14-15"></td>
                            <td><input type="radio" name="horaire_vendredi" value="14-15"></td>
                        </tr>
                        <tr>
                            <td>15h - 16h</td>
                            <td><input type="radio" name="horaire_lundi" value="15-16"></td>
                            <td><input type="radio" name="horaire_mardi" value="15-16"></td>
                            <td><input type="radio" name="horaire_mercredi" value="15-16"></td>
                            <td><input type="radio" name="horaire_jeudi" value="15-16"></td>
                            <td><input type="radio" name="horaire_vendredi" value="15-16"></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <button type="submit" class="btn btn-primary w-100 mt-3">Valider</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
