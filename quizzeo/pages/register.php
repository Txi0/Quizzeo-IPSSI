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
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="register-container">
        <img src="../assets/images/logo.png" alt="Quizzeo Logo" class="logo">
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
            <div class="form-group role-specific ecole" style="display: none;">
                <label for="nom_etablissement">Nom de l'établissement *</label>
                <input type="text" id="nom_etablissement" name="nom_etablissement">
            </div>

            <!-- Champs spécifiques pour les entreprises -->
            <div class="form-group role-specific entreprise" style="display: none;">
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