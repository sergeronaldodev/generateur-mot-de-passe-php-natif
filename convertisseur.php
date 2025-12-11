<?php

// Taux de conversion basés sur 1 unité de la DEVISE SOURCE vers la DEVISE CIBLE
$taux_conversion = [
    'EUR' => [
        'USD' => 1.085,
        'GBP' => 0.850,
        'JPY' => 160.50,
        'CAD' => 1.480,
        'XOF' => 655.957, // NOUVEAU : 1 EUR = 655.957 XOF
    ],
    'USD' => [
        'EUR' => 1 / 1.085, 
        'GBP' => 0.850 / 1.085,
        'JPY' => 160.50 / 1.085,
        'CAD' => 1.480 / 1.085,
        'XOF' => 655.957 / 1.085, // NOUVEAU : XOF basé sur EUR/USD
    ],
    // NOUVEAU : Ajouter les taux pour le FCFA (XOF)
    'XOF' => [
        'EUR' => 1 / 655.957, // Taux fixe inverse : 1 XOF = 1/655.957 EUR
        'USD' => (1 / 655.957) * 1.085, // XOF vers USD via EUR
        'GBP' => (1 / 655.957) * 0.850, // XOF vers GBP via EUR
        'JPY' => (1 / 655.957) * 160.50, // XOF vers JPY via EUR
        'CAD' => (1 / 655.957) * 1.480, // XOF vers CAD via EUR
    ],
    'GBP' => [ // Ajout de la LIGNE GBP
        'EUR' => 1 / 0.850, // 1 GBP = 1/0.850 EUR (inverse du taux EUR vers GBP)
        'USD' => 1.085 / 0.850, // GBP vers USD via EUR
        // Nous n'avons besoin que du taux vers EUR pour que le pivot fonctionne
    ],
    // Note : Pour les autres devises (GBP, JPY, CAD), il faudrait aussi les ajouter ici
   
];

$montant_converti = null;
$message_resultat = '';

// 1. Vérifier si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Récupérer les données
    $montant = $_POST['montant'] ?? 0;
    $devise_source = $_POST['devise_source'] ?? '';
    $devise_cible = $_POST['devise_cible'] ?? '';
    
    // Mettre à jour la variable pour conserver le montant dans le formulaire
    $montant_saisi = htmlspecialchars($montant); 

    // Validation basique des données
    if (!is_numeric($montant) || $montant <= 0 || empty($devise_source) || empty($devise_cible)) {
        $message_resultat = "<span style='color: red;'>Veuillez entrer un montant valide et sélectionner les deux devises.</span>";
        
    } elseif ($devise_source === $devise_cible) {
        // Cas 1: Devise Source = Devise Cible
        $montant_converti = $montant;
        $message_resultat = "Le montant est le même. " . number_format($montant, 2) . " {$devise_source} = " . number_format($montant_converti, 2) . " {$devise_cible}.";
        
    } elseif (isset($taux_conversion[$devise_source][$devise_cible])) {
        // Cas 2: Conversion STANDARD (Taux direct trouvé)
        
        $taux = $taux_conversion[$devise_source][$devise_cible];
        $montant_converti = $montant * $taux;

        // Formater et afficher le résultat
        $montant_formatte = number_format(round($montant_converti, 2), 2, ',', ' ');
        $montant_source_formatte = number_format(round($montant, 2), 2, ',', ' ');
        
        $message_resultat = "Résultat : {$montant_source_formatte} {$devise_source} = <strong>{$montant_formatte} {$devise_cible}</strong>";

    } else {
        // Cas 3: Conversion INDIRECTE (Taux direct NON trouvé)
        
        $devise_pivot = 'EUR'; // On utilise l'Euro comme pivot, car tous les taux y sont liés.

        // Vérification des deux étapes de la conversion indirecte :
        $taux_step1_ok = isset($taux_conversion[$devise_source][$devise_pivot]);
        $taux_step2_ok = isset($taux_conversion[$devise_pivot][$devise_cible]);
        
        if ($taux_step1_ok && $taux_step2_ok) {
            
            // 1. Taux Devise Source (GBP) -> Pivot (EUR)
            $taux_devise_vers_pivot = $taux_conversion[$devise_source][$devise_pivot];
            
            // 2. Taux Pivot (EUR) -> Devise Cible (XOF)
            $taux_pivot_vers_cible = $taux_conversion[$devise_pivot][$devise_cible];
            
            // Taux final = Multiplication des deux taux
            $taux_final = $taux_devise_vers_pivot * $taux_pivot_vers_cible;
            
            // Calcul final
            $montant_converti = $montant * $taux_final;

            // Affichage formaté
            $montant_formatte = number_format(round($montant_converti, 2), 2, ',', ' ');
            $montant_source_formatte = number_format(round($montant, 2), 2, ',', ' ');
            
            $message_resultat = "Résultat (via EUR) : {$montant_source_formatte} {$devise_source} = <strong>{$montant_formatte} {$devise_cible}</strong>";

        } else {
            // Dernier Cas: Taux indirect NON trouvé non plus
            $message_resultat = "<span style='color: orange;'>Conversion impossible. Le taux de {$devise_source} vers {$devise_pivot} ou de {$devise_pivot} vers {$devise_cible} est manquant.</span>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Convertisseur de Devises PHP</title>
    
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7f6; /* Fond très léger et neutre */
            display: flex;
            flex-direction: column;
            align-items: center; /* Centre horizontalement */
            padding-top: 50px;
            padding-bottom: 50px; /* Ajoute de l'espace en bas */
            min-height: 100vh; /* S'assure que le fond couvre toute la hauteur */
        }
        .container {
            background-color: #ffffff;
            padding: 35px 50px;
            border-radius: 15px; /* Coins plus arrondis */
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15); /* Ombre plus prononcée pour l'effet flottant */
            width: 90%; /* Prend 90% de l'espace disponible */
            max-width: 500px; /* Limite la largeur à 500px */
            
            /* Optionnel: Ajout d'une ligne de couleur d'accentuation en haut */
            border-top: 5px solid #ff9900;
        }
        h2 {
            color: #1a1a1a;
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #ff9900; /* Couleur d'accentuation */
            padding-bottom: 10px;
        }
        /* Style des éléments de formulaire */
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #555;
            margin-top: 15px;
        }
        input[type="number"], select {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 8px;
            box-sizing: border-box;
            font-size: 1em;
        }
        /* Style du bouton */
        button[type="submit"] {
            width: 100%;
            padding: 15px;
            margin-top: 25px;
            background-color: #ff9900;
            color: white;
            font-size: 1.1em;
            font-weight: bold;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        button[type="submit"]:hover {
            background-color: #e68a00;
        }
        /* Style de la zone de résultat */
        /* Style de la zone de résultat */
        .result-box {
            margin-top: 30px;
            padding: 20px;
            background-color: #f7f9fb;
            border: 2px solid #ff9900;
            border-left: 6px solid #ff9900;
            border-radius: 10px; /* Harmonisation des coins */
            font-size: 1.1em;
            color: #333;
            text-align: center;
        }

        /* Style pour les messages d'erreur ou d'avertissement */
        .error-message {
            margin-top: 20px;
            padding: 15px;
            background-color: #fff3cd;
            border: 1px solid #ffeeba;
            color: #856404;
            border-radius: 10px; /* Harmonisation des coins */
            text-align: center;
        }
    </style>
</head>
<body>
    
    <form method="POST" action="convertisseur.php">
        <h3>Entrez le montant à convertir :</h3>
        
        <label for="montant">Montant :</label>
        <input type="number" step="0.01" name="montant" id="montant" required value="<?php echo $montant_saisi; ?>">
        
        <label for="devise_source">Devise Source :</label>
        <select name="devise_source" id="devise_source">
            <?php $selected_source = $_POST['devise_source'] ?? ''; ?>
            <option value="EUR" <?php if ($selected_source === 'EUR') echo 'selected'; ?>>Euro (EUR)</option>
            <option value="USD" <?php if ($selected_source === 'USD') echo 'selected'; ?>>Dollar US (USD)</option>
            <option value="GBP" <?php if ($selected_source === 'GBP') echo 'selected'; ?>>Livre Sterling (GBP)</option>
            <option value="XOF" <?php if ($selected_source === 'XOF') echo 'selected'; ?>>FCFA (XOF)</option>
        </select>

        <label for="devise_cible">Devise Cible :</label>
        <select name="devise_cible" id="devise_cible">
            <?php $selected_cible = $_POST['devise_cible'] ?? ''; ?>
            <option value="USD" <?php if ($selected_cible === 'USD') echo 'selected'; ?>>Dollar US (USD)</option>
            <option value="EUR" <?php if ($selected_cible === 'EUR') echo 'selected'; ?>>Euro (EUR)</option>
            <option value="JPY" <?php if ($selected_cible === 'JPY') echo 'selected'; ?>>Yen Japonais (JPY)</option>
            <option value="XOF" <?php if ($selected_cible === 'XOF') echo 'selected'; ?>>FCFA (XOF)</option>
        </select>
        
        <button type="submit">Convertir</button>
    </form>

    <?php
        // Afficher l'erreur (si la validation a échoué)
        if (strpos($message_resultat, 'red') !== false || strpos($message_resultat, 'orange') !== false) {
            echo "<div class='error-message'>";
            echo strip_tags($message_resultat, '<strong>'); // Affiche le message d'erreur sans les tags style
            echo "</div>";
        }
        
        // Afficher le résultat (si la conversion a réussi)
        if (!empty($message_resultat) && strpos($message_resultat, 'red') === false && strpos($message_resultat, 'orange') === false) {
            echo "<div class='result-box'>";
            echo $message_resultat;
            echo "</div>";
        }
    ?>
</body>
</html>