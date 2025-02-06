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
            --white: #ffffff;
            --border-color: #e0e0e0;
        }

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
        }

        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 250px;
            background: var(--white);
            padding: 20px;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
            border-right: 1px solid var(--border-color);
        }

        .user-info {
            text-align: center;
            padding: 20px;
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 20px;
        }

        .user-info h3 {
            color: var(--primary-color);
            font-size: 18px;
        }

        .main-content {
            flex: 1;
            padding: 30px;
            background-color: var(--background-color);
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 15px;
        }

        .header h1 {
            color: var(--primary-color);
            font-size: 24px;
        }

        /* Profile Dropdown */
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
            background: var(--white);
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            z-index: 1000;
            min-width: 200px;
            border: 1px solid var(--border-color);
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
            border-bottom: 1px solid var(--border-color);
        }

        .dropdown-item:last-child {
            border-bottom: none;
        }

        .dropdown-item:hover {
            background-color: rgba(139, 92, 246, 0.1);
        }

        .dropdown-item i {
            margin-right: 10px;
            color: var(--primary-color);
        }

        /* Menu */
        .menu {
            list-style-type: none;
        }

        .menu li a {
            display: block;
            padding: 10px 15px;
            color: var(--secondary-color);
            text-decoration: none;
            transition: all 0.3s ease;
            border-radius: 4px;
        }

        .menu li a:hover,
        .menu li a.active {
            background-color: var(--primary-color);
            color: var(--white);
        }

        /* Quiz Input */
        .quiz-input {
            background: var(--white);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .quiz-input input {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
        }

        .btn-primary {
            background: var(--primary-color);
            color: var(--white);
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            width: 100%;
        }

        .btn-primary:hover {
            background: var(--secondary-color);
        }

        /* Responses Grid */
        .responses-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }

        .response-card {
            background: var(--white);
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .response-card:hover {
            transform: translateY(-5px);
        }

        .response-card h3 {
            color: var(--primary-color);
            margin-bottom: 10px;
        }

        .score {
            font-size: 1.2em;
            font-weight: bold;
            color: var(--secondary-color);
            margin: 10px 0;
        }

        .date {
            color: #6B7280;
            font-size: 0.9em;
        }

        .success-message {
            background: #DEF7EC;
            color: #03543F;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .dashboard-container {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                border-right: none;
                border-bottom: 1px solid var(--border-color);
            }

            .sidebar .menu {
                display: flex;
                justify-content: space-around;
            }

            .responses-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <nav class="sidebar">
            <div class="user-info">
                <h3>👤 <?php echo htmlspecialchars($_SESSION['user']['nom'] . ' ' . $_SESSION['user']['prenom']); ?></h3>
            </div>
            <ul class="menu">
                <li><a href="index.php" class="active">📊 Tableau de bord</a></li>
                <li><a href="historique.php">📜 Historique</a></li>
                <li><a href="../../../logout.php">🚪 Déconnexion</a></li>
            </ul>
        </nav>

        <!-- Main Content -->
        <main class="main-content">
            <div class="header">
                <h1>🏠 Tableau de bord</h1>
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
                    ✅ Vos réponses ont été enregistrées avec succès !
                </div>
            <?php endif; ?>

            <!-- Input pour le lien du quiz -->
            <div class="quiz-input">
                <h2>🔍 Répondre à un questionnaire</h2>
                <form action="../../../repondre.php" method="GET">
                    <input type="text" name="token" placeholder="Collez le token du questionnaire ici 🔑">
                    <button type="submit" class="btn-primary">Accéder au questionnaire 📝</button>
                </form>
            </div>

            <!-- Réponses récentes -->
            <h2>📋 Vos réponses récentes</h2>
            <div class="responses-grid">
                <?php if (empty($reponseDetails)): ?>
                    <div class="response-card">
                        <p>🌱 Vous n'avez pas encore de réponses à des questionnaires.</p>
                        <p>Commencez par répondre à un quiz !</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($reponseDetails as $detail): ?>
                        <div class="response-card">
                            <h3>📋 <?php echo htmlspecialchars($detail['quiz']['titre']); ?></h3>
                            <?php if (isset($detail['reponse']['score'])): ?>
                                <div class="score">
                                    🏆 Score : <?php echo $detail['reponse']['score']; ?> / <?php echo $detail['quiz']['points_total']; ?>
                                </div>
                            <?php endif; ?>
                            <div class="date">
                                📅 Répondu le <?php echo date('d/m/Y à H:i', strtotime($detail['reponse']['date'])); ?>
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