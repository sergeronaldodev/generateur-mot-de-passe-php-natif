<?php

// Variable pour stocker le mot de passe généré
$password = ''; 
// Variable pour stocker le message d'erreur utilisateur
$error_message = '';

// 1. On vérifie si le formulaire a été soumis (méthode POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Récupération et validation des données du formulaire
    $length = (int)($_POST['length'] ?? 12); 
    // Assure une longueur minimale de 8
    $length = max(8, $length); 
    
    // Récupération de toutes les options (toutes sont maintenant facultatives)
    $use_min = isset($_POST['min']);         
    $use_num = isset($_POST['num']);         
    $use_maj = isset($_POST['maj']);         
    $use_symb = isset($_POST['symb']);       
    
    // --- 2. Construction des groupes de caractères possibles ---
    $possible_chars = '';
    $groups = []; // Utilisé pour l'inclusion forcée

    if ($use_min) {
        $possible_chars .= 'abcdefghijklmnopqrstuvwxyz';
        $groups['minuscules'] = 'abcdefghijklmnopqrstuvwxyz';
    }
    if ($use_num) {
        $possible_chars .= '0123456789'; 
        $groups['chiffres'] = '0123456789';
    }
    if ($use_maj) {
        $possible_chars .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $groups['majuscules'] = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    }
    if ($use_symb) {
        $possible_chars .= '!@#$%^&*()-_=+[]{}|;:,.<>?/~';
        $groups['symboles'] = '!@#$%^&*()-_=+[]{}|;:,.<>?/~';
    }

    // VÉRIFICATION CRITIQUE : Si aucune option n'est sélectionnée, affiche un message d'erreur
    if (empty($groups)) {
        $error_message = "Veuillez sélectionner au moins un type de caractère (Minuscules, Majuscules, Chiffres ou Symboles) pour générer le mot de passe.";
        
    } else {
        // --- 3. Génération du mot de passe avec inclusion forcée ---
        $required_chars = []; 

        // 1. Ajouter un caractère de chaque groupe requis (garantit la diversité)
        foreach ($groups as $group_chars) {
            $required_chars[] = $group_chars[random_int(0, strlen($group_chars) - 1)];
        }

        // 2. Calculer et générer le reste du mot de passe
        $remaining_length = $length - count($required_chars);
        
        if ($remaining_length >= 0) {
            $char_count = strlen($possible_chars); 
            
            for ($i = 0; $i < $remaining_length; $i++) {
                $required_chars[] = $possible_chars[random_int(0, $char_count - 1)]; 
            }
        }

        // 3. Mélanger les caractères et assembler le mot de passe final
        shuffle($required_chars); 
        $password = implode('', $required_chars); 
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Générateur de Mot de Passe PHP</title>
    
    <style>
        /* Styles CSS pour une présentation propre */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7f6;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding-top: 50px;
        }
        .container {
            background-color: #ffffff;
            padding: 30px 40px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 450px;
        }
        h2 {
            color: #333;
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #555;
        }
        input[type="number"], button {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }
        input[type="checkbox"] {
            margin-right: 10px;
        }
        button {
            background-color: #007bff;
            color: white;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color: #0056b3;
        }
        .checkbox-group {
            margin-bottom: 20px;
            padding: 10px;
            border: 1px solid #eee;
            border-radius: 5px;
        }
        .checkbox-group label {
            font-weight: normal;
            display: inline-block; /* Pour les labels des checkboxes */
        }
        /* Style de la boîte de résultat */
        .result-box {
            background-color: #e9f5ff;
            border: 2px solid #007bff;
            border-left: 5px solid #007bff;
            padding: 15px;
            margin-top: 25px;
            border-radius: 5px;
            text-align: center;
        }
        .result-box h3 {
            color: #007bff;
            margin-top: 0;
            margin-bottom: 10px;
        }
        .result-box p {
            font-size: 1.4em;
            font-weight: bold;
            color: #333;
            word-wrap: break-word;
            user-select: all; /* Facilite la sélection du mot de passe */
        }
    </style>

</head>
<body>
    <div class="container">
        
        <form method="POST" action="index.php">
            
            <h2>Générateur de mot de passe</h2>
            
            <label for="length">Longueur (min 8) :</label>
            <input type="number" name="length" id="length" min="8" value="<?php echo $_POST['length'] ?? 12; ?>" required>
            
            <div class="checkbox-group">
                <p style="font-weight: bold; margin-bottom: 10px; color: #555;">Inclure :</p>
                
                <input type="checkbox" name="min" id="min" <?php echo isset($_POST['min']) ? 'checked' : 'checked'; ?>> 
                <label for="min">Minuscules (a-z)</label><br>

                <input type="checkbox" name="num" id="num" <?php echo isset($_POST['num']) ? 'checked' : 'checked'; ?>> 
                <label for="num">Chiffres (0-9)</label><br>
                
                <input type="checkbox" name="maj" id="maj" <?php echo isset($_POST['maj']) ? 'checked' : 'checked'; ?>> 
                <label for="maj">Majuscules (A-Z)</label><br>
                
                <input type="checkbox" name="symb" id="symb" <?php echo isset($_POST['symb']) ? 'checked' : ''; ?>>
                <label for="symb">Symboles (!@#$...)</label><br>

            </div>

            <button type="submit">Générer le mot de passe</button>
        </form>

        <?php
        // Affichage de l'erreur (en rouge) s'il y a un problème de sélection
        if (!empty($error_message)) {
            echo "
            <div style='padding: 15px; margin-top: 25px; border: 2px solid #dc3545; background-color: #f8d7da; color: #721c24; border-radius: 5px; text-align: center;'>
                <strong>Erreur :</strong> {$error_message}
            </div>
            ";
        }
        ?>

        <?php
        // Affichage du résultat uniquement si un mot de passe a été généré
        if (!empty($password)) {
            echo "
            <div class='result-box'>
                <h3>Mot de passe généré :</h3>
                <p>{$password}</p>
            </div>
            ";
        }
        ?>
    </div>
    
</body>
</html>