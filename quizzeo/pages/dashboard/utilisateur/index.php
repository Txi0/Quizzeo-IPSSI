<?php
// pages/dashboard/utilisateur/index.php
session_start();
require_once '../../../includes/auth.php';

// Vérification du rôle
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'utilisateur') {
    header('Location: ../../login.php');
    exit;
}

// Récupération des réponses de l'utilisateur
$responsesDb = new JsonDatabase('responses.json');
$quizDb = new JsonDatabase('quizzes.json');

$userResponses = array_filter($responsesDb->getAll(), function($response) {
    return isset($response['user_id']) && $response['user_id'] === $_SESSION['user']['id'];
});

// Pour chaque réponse, récupérer les détails du quiz
$reponseDetails = [];
foreach ($userResponses as $response) {
    $quiz = $quizDb->findById($response['quiz_id']);
    if ($quiz) {
        $reponseDetails[] = [
            'quiz' => $quiz,
            'reponse' => $response
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - Utilisateur</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary-color: #8B5CF6;
            --secondary-color: #7C3AED;
            --background-color: #F3F4F6;
            --text-color: #1F2937;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--background-color);
            color: var(--text-color);
        }

        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background: white;
            padding: 20px;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        }

        .main-content {
            flex: 1;
            padding: 20px;
            position: relative;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        /* Profile Dropdown Styles */
        .profile-actions {
            position: relative;
        }

        .profile-icon {
            font-size: 24px;
            color: var(--primary-color);
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .profile-icon:hover {
            color: var(--secondary-color);
        }

        .profile-dropdown {
            display: none;
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            z-index: 1000;
            min-width: 200px;
        }

        .profile-dropdown.show {
            display: block;
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            padding: 10px 15px;
            text-decoration: none;
            color: var(--text-color);
            transition: background-color 0.3s ease;
        }

        .dropdown-item:hover {
            background-color: #F3F4F6;
        }

        .dropdown-item i {
            margin-right: 10px;
            color: var(--primary-color);
        }

        .quiz-input {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .quiz-input input {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #E5E7EB;
            border-radius: 6px;
        }

        .responses-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }

        .response-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .success-message {
            background: #DEF7EC;
            color: #03543F;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            display: inline-block;
            border: none;
            cursor: pointer;
        }

        .menu {
            list-style: none;
            padding: 0;
            margin: 20px 0;
        }

        .menu li a {
            display: block;
            padding: 10px;
            color: var(--text-color);
            text-decoration: none;
            border-radius: 6px;
        }

        .menu li a:hover,
        .menu li a.active {
            background: var(--primary-color);
            color: white;
        }

        .score {
            font-size: 1.2em;
            font-weight: bold;
            color: var(--primary-color);
            margin: 10px 0;
        }

        .date {
            color: #6B7280;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <nav class="sidebar">
 
            <div class="user-info">
                <h3><?php echo htmlspecialchars($_SESSION['user']['nom'] . ' ' . $_SESSION['user']['prenom']); ?></h3>
            </div>
            <ul class="menu">
                <li><a href="index.php" class="active">Tableau de bord</a></li>
                <li><a href="historique.php">Historique</a></li>
                <li><a href="../../../logout.php">Déconnexion</a></li>
            </ul>
        </nav>

        <!-- Main Content -->
        <main class="main-content">
            <div class="header">
                <h1>Tableau de bord</h1>
                <div class="profile-actions">
                    <div class="profile-icon" onclick="toggleProfileMenu()">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <div id="profile-dropdown" class="profile-dropdown">
                        <a href="profil.php" class="dropdown-item">
                            <i class="fas fa-user"></i> Modifier mon profil
                        </a>
                        <a href="security.php" class="dropdown-item">
                            <i class="fas fa-lock"></i> Sécurité
                        </a>
                    </div>
                </div>
            </div>

            <?php if (isset($_GET['success'])): ?>
                <div class="success-message">
                    Vos réponses ont été enregistrées avec succès !
                </div>
            <?php endif; ?>

            <!-- Input pour le lien du quiz -->
            <div class="quiz-input">
                <h2>Répondre à un questionnaire</h2>
                <form action="../../../repondre.php" method="GET">
                    <input type="text" name="token" placeholder="Collez le token du questionnaire ici">
                    <button type="submit" class="btn-primary">Accéder au questionnaire</button>
                </form>
            </div>

            <!-- Réponses récentes -->
            <h2>Vos réponses récentes</h2>
            <div class="responses-grid">
                <?php if (empty($reponseDetails)): ?>
                    <p>Vous n'avez pas encore de réponses à des questionnaires.</p>
                <?php else: ?>
                    <?php foreach ($reponseDetails as $detail): ?>
                        <div class="response-card">
                            <h3><?php echo htmlspecialchars($detail['quiz']['titre']); ?></h3>
                            <?php if (isset($detail['reponse']['score'])): ?>
                                <div class="score">
                                    Score : <?php echo $detail['reponse']['score']; ?> / <?php echo $detail['quiz']['points_total']; ?>
                                </div>
                            <?php endif; ?>
                            <div class="date">
                                Répondu le <?php echo date('d/m/Y à H:i', strtotime($detail['reponse']['date'])); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
    function toggleProfileMenu() {
        const dropdown = document.getElementById('profile-dropdown');
        dropdown.classList.toggle('show');
    }

    // Fermer le menu si on clique en dehors
    window.onclick = function(event) {
        if (!event.target.matches('.profile-icon i')) {
            const dropdown = document.getElementById('profile-dropdown');
            if (dropdown.classList.contains('show')) {
                dropdown.classList.remove('show');
            }
        }
    }
    </script>
</body>
</html>