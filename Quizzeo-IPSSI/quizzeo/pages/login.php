<?php
session_start();
require_once '../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérification manuelle des identifiants Admin
    $email = $_POST['email'];
    $password = $_POST['password'];

    if ($email == 'Admin@gmail.com' && $password == 'Admin123') {
        // Si les identifiants sont corrects pour l'admin
        $_SESSION['user'] = [
            'email' => $email,
            'role' => 'admin'
        ];
        header('Location: dashboard/admin/admin.php'); // Rediriger vers la page admin
        exit;
    }

    // Sinon, vérifier avec la classe Auth pour les autres utilisateurs
    $auth = new Auth();
    $result = $auth->login($email, $password);

    if (isset($result['success']) && $result['success']) {
        // Redirection selon le rôle
        switch ($_SESSION['user']['role']) {
            case 'ecole':
                header('Location: dashboard/ecole/index.php');
                break;
            case 'entreprise':
                header('Location: dashboard/entreprise/index.php');
                break;
            case 'utilisateur':
                header('Location: dashboard/utilisateur/index.php');
                break;
            case 'admin':
                header('Location: dashboard/admin/admin.php');
                break;
        }
        exit;
    } else {
        // S'il y a une erreur (mot de passe incorrect ou compte désactivé)
        $error = $result['message'];  // Le message peut être "Email ou mot de passe incorrect" ou "Ce compte est désactivé"
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Quizzeo</title>
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

        /* Conteneur de connexion */
        .login-container {
            background-color: var(--white);
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            padding: 40px;
            text-align: center;
            transition: transform 0.3s ease;
        }

        .login-container:hover {
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

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1.5px solid var(--input-border);
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.2);
        }

        /* Bouton de connexion */
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

        /* Lien d'inscription */
        .register-link {
            margin-top: 20px;
            color: #6b7280;
        }

        .register-link a {
            color: var(--primary-color);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .register-link a:hover {
            color: var(--secondary-color);
            text-decoration: underline;
        }

        /* Design responsive */
        @media (max-width: 480px) {
            .login-container {
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
    <div class="login-container">
        <img src="../logo.png" alt="Quizzeo Logo" class="logo">
        <h1>Connexion</h1>

        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="btn-primary">Se connecter</button>
        </form>

        <p class="register-link">
            Pas encore de compte ? <a href="register.php">Créer un compte</a>
        </p>
    </div>
</body>
</html>