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
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="login-container">
        <img src="../assets/images/logo.png" alt="Quizzeo Logo" class="logo">
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
