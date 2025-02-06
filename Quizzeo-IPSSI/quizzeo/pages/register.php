<?php
// pages/register.php
session_start();
require_once '../includes/auth.php';

// Définition des rôles disponibles
$roles = [
    'ecole' => 'École',
    'entreprise' => 'Entreprise',
    'utilisateur' => 'Simple Utilisateur'
];

// Générer un nouveau captcha
if (!isset($_SESSION['captcha'])) {
    $num1 = rand(1, 10);
    $num2 = rand(1, 10);
    $_SESSION['captcha'] = [
        'question' => "$num1 + $num2",
        'answer' => $num1 + $num2
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier le captcha
    if (!isset($_POST['captcha']) || intval($_POST['captcha']) !== $_SESSION['captcha']['answer']) {
        $error = "Le calcul de vérification est incorrect";
    } else {
        $auth = new Auth();
        $result = $auth->register($_POST);
        
        if ($result['success']) {
            $_SESSION['flash_message'] = "Inscription réussie ! Vous pouvez maintenant vous connecter.";
            header('Location: login.php');
            exit;
        } else {
            $error = $result['message'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Quizzeo</title>
    <style>
        /* Variables */
        :root {
            --primary-color: #8B5CF6;
            --secondary-color: #7C3AED;
            --background-color: #f4f6f9;
            --text-color: #333;
            --white: #ffffff;
            --input-border: #e2e8f0;
            --error-bg: #FEE2E2;
            --error-text: #991B1B;
        }

        /* Reset et base */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Arial', sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        /* Conteneur d'inscription */
        .register-container {
            background-color: var(--white);
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
            padding: 40px;
            text-align: center;
            transition: transform 0.3s ease;
        }

        .register-container:hover {
            transform: translateY(-5px);
        }

        /* Logo */
        .logo {
            max-width: 200px;
            margin-bottom: 30px;
            height: auto;
        }

        /* Titre */
        h1 {
            color: var(--primary-color);
            margin-bottom: 25px;
        }

        /* Groupes de formulaire */
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #4a5568;
        }

        .form-group input, 
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1.5px solid var(--input-border);
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .form-group input:focus, 
        .form-group select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.2);
        }

        .captcha-question {
            background-color: #f0f0f0;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 10px;
            text-align: center;
            font-weight: bold;
        }

        /* Bouton d'inscription */
        .btn-primary {
            width: 100%;
            padding: 14px;
            background-color: var(--primary-color);
            color: var(--white);
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .btn-primary:hover {
            background-color: var(--secondary-color);
        }

        /* Message d'erreur */
        .error-message {
            background-color: var(--error-bg);
            color: var(--error-text);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }

        /* Lien de connexion */
        .login-link {
            margin-top: 20px;
            color: #6b7280;
        }

        .login-link a {
            color: var(--primary-color);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .login-link a:hover {
            color: var(--secondary-color);
            text-decoration: underline;
        }

        /* Champs spécifiques aux rôles */
        .role-specific {
            display: none;
        }

        /* Design responsive */
        @media (max-width: 480px) {
            .register-container {
                padding: 30px 20px;
                width: 100%;
                max-width: 100%;
            }

            .logo {
                max-width: 150px;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
    <img src="../logo.png" alt="Quizzeo Logo" class="logo">
        <h1>Inscription</h1>
        
        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="" id="registerForm">
            <div class="form-group">
                <label for="role">Type de compte *</label>
                <select name="role" id="role" required>
                    <option value="">Sélectionnez un type de compte</option>
                    <?php foreach ($roles as $value => $label): ?>
                        <option value="<?php echo $value; ?>"><?php echo $label; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="nom">Nom *</label>
                <input type="text" id="nom" name="nom" required>
            </div>

            <div class="form-group">
                <label for="prenom">Prénom *</label>
                <input type="text" id="prenom" name="prenom" required>
            </div>

            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="password">Mot de passe *</label>
                <input type="password" id="password" name="password" required 
                       minlength="8" 
                       title="Le mot de passe doit contenir au moins 8 caractères">
            </div>

            <!-- Champs spécifiques pour les écoles -->
            <div class="form-group role-specific ecole">
                <label for="nom_etablissement">Nom de l'établissement *</label>
                <input type="text" id="nom_etablissement" name="nom_etablissement">
            </div>

            <!-- Champs spécifiques pour les entreprises -->
            <div class="form-group role-specific entreprise">
                <label for="nom_entreprise">Nom de l'entreprise *</label>
                <input type="text" id="nom_entreprise" name="nom_entreprise">
            </div>

            <div class="form-group">
                <label>Vérification anti-robot *</label>
                <p class="captcha-question"><?php echo $_SESSION['captcha']['question']; ?></p>
                <input type="number" name="captcha" required>
            </div>

            <button type="submit" class="btn-primary">S'inscrire</button>
        </form>

        <p class="login-link">
            Déjà inscrit ? <a href="login.php">Se connecter</a>
        </p>
    </div>

    <script>
    document.getElementById('role').addEventListener('change', function() {
        // Cacher tous les champs spécifiques
        document.querySelectorAll('.role-specific').forEach(el => {
            el.style.display = 'none';
            el.querySelectorAll('input').forEach(input => input.required = false);
        });
        
        // Afficher les champs spécifiques au rôle sélectionné
        if (this.value) {
            const roleFields = document.querySelector('.role-specific.' + this.value);
            if (roleFields) {
                roleFields.style.display = 'block';
                roleFields.querySelectorAll('input').forEach(input => input.required = true);
            }
        }
    });
    </script>
</body>
</html>