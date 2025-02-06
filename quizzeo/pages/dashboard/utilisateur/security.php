<?php
// pages/dashboard/utilisateur/security.php
session_start();
require_once '../../../includes/auth.php';

// Vérification de la connexion
if (!isset($_SESSION['user'])) {
    header('Location: ../../login.php');
    exit;
}

// Traitement du formulaire de modification de mot de passe
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération des utilisateurs
    $usersDb = new JsonDatabase('users.json');
    $users = $usersDb->getAll();

    // Validation des données
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Trouver l'utilisateur dans la base de données
    $userIndex = array_search($_SESSION['user']['id'], array_column($users, 'id'));

    if ($userIndex === false) {
        $error = 'Utilisateur non trouvé.';
    } else {
        // Vérifier le mot de passe actuel
        if (!password_verify($current_password, $users[$userIndex]['password'])) {
            $error = 'Le mot de passe actuel est incorrect.';
        } elseif (empty($new_password) || strlen($new_password) < 8) {
            $error = 'Le nouveau mot de passe doit contenir au moins 8 caractères.';
        } elseif ($new_password !== $confirm_password) {
            $error = 'Les nouveaux mots de passe ne correspondent pas.';
        } else {
            // Hacher le nouveau mot de passe
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            // Préparer les données à mettre à jour
            $userData = $users[$userIndex];
            $userData['password'] = $hashed_password;

            // Sauvegarder les modifications
            try {
                // Utiliser la méthode update
                $usersDb->update($userData['id'], $userData);
                $success = 'Mot de passe mis à jour avec succès.';
            } catch (Exception $e) {
                $error = 'Erreur lors de la mise à jour du mot de passe : ' . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sécurité du compte</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Variables */
        :root {
            --primary-color: #8B5CF6;
            --secondary-color: #7C3AED;
            --background-color: #f4f6f9;
            --text-color: #1F2937;
            --input-border-color: #e2e8f0;
            --input-focus-color: rgba(139, 92, 246, 0.2);
            --alert-success-bg: #DEF7EC;
            --alert-success-text: #03543F;
            --alert-danger-bg: #FEE2E2;
            --alert-danger-text: #991B1B;
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
            line-height: 1.6;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        /* Conteneur principal */
        .security-container {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
            padding: 40px;
            transition: all 0.3s ease;
        }

        .security-container:hover {
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
            transform: translateY(-5px);
        }

        /* En-tête */
        .security-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .security-header h1 {
            color: var(--primary-color);
            font-size: 24px;
            margin-bottom: 10px;
        }

        .security-header p {
            color: #6B7280;
            font-size: 16px;
        }

        /* Groupes de formulaire */
        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #4A5568;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 1.5px solid var(--input-border-color);
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
            outline: none;
        }

        .form-group input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px var(--input-focus-color);
        }

        .password-requirements {
            font-size: 0.8em;
            color: #6B7280;
            margin-top: 5px;
        }

        /* Bouton principal */
        .btn-primary {
            width: 100%;
            padding: 14px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .btn-primary:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(124, 58, 237, 0.2);
        }

        /* Alertes */
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-size: 14px;
        }

        .alert-success {
            background-color: var(--alert-success-bg);
            color: var(--alert-success-text);
            border: 1px solid rgba(3, 84, 63, 0.2);
        }

        .alert-danger {
            background-color: var(--alert-danger-bg);
            color: var(--alert-danger-text);
            border: 1px solid rgba(153, 27, 27, 0.2);
        }

        /* Lien de retour */
        .back-link {
            text-align: center;
            margin-top: 25px;
        }

        .back-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .back-link a:hover {
            color: var(--secondary-color);
            text-decoration: underline;
        }

        /* Responsive */
        @media (max-width: 600px) {
            .security-container {
                padding: 25px;
                margin: 0 10px;
            }
        }

        /* Animation */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .security-container {
            animation: fadeIn 0.5s ease forwards;
        }
    </style>
</head>
<body>
    <div class="security-container">
        <div class="security-header">
            <h1>Sécurité du compte</h1>
            <p>Modifier votre mot de passe</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="current_password">Mot de passe actuel</label>
                <input type="password" id="current_password" name="current_password" required>
            </div>

            <div class="form-group">
                <label for="new_password">Nouveau mot de passe</label>
                <input type="password" id="new_password" name="new_password" required>
                <div class="password-requirements">
                    Le mot de passe doit contenir au moins 8 caractères
                </div>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirmer le nouveau mot de passe</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>

            <button type="submit" class="btn-primary">Modifier le mot de passe</button>
        </form>

        <div class="back-link">
            <a href="index.php">Retour au tableau de bord</a>
        </div>
    </div>
</body>
</html>