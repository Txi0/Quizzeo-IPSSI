<?php
// pages/dashboard/ecole/index.php
session_start();
require_once '../../../includes/auth.php';

// Vérification du rôle
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'ecole') {
    header('Location: ../../login.php');
    exit;
}

// Récupération des quiz de l'école
$quizDb = new JsonDatabase('quizzes.json');
$schoolQuizzes = array_filter($quizDb->getAll(), function($quiz) {
    return isset($quiz['user_id']) && $quiz['user_id'] === $_SESSION['user']['id'];
});

// Statistiques
$totalQuiz = count($schoolQuizzes);
$activeQuiz = count(array_filter($schoolQuizzes, function($quiz) {
    return isset($quiz['status']) && $quiz['status'] === 'lancé';
}));
$finishedQuiz = count(array_filter($schoolQuizzes, function($quiz) {
    return isset($quiz['status']) && $quiz['status'] === 'terminé';
}));

// Récupérer les quiz récents
$recentQuizzes = array_slice($schoolQuizzes, 0, 5);
usort($recentQuizzes, function($a, $b) {
    return strtotime($b['created_at'] ?? 0) - strtotime($a['created_at'] ?? 0);
});
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard École - Quizzeo</title>
    <style>
        /* Variables */
        :root {
            --primary-color: #8B5CF6;
            --secondary-color: #6B48F3;
            --background-color: #f4f4f9;
            --white: #ffffff;
            --text-color: #333;
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
        }

        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 250px;
            background-color: var(--white);
            border-right: 1px solid var(--border-color);
            padding: 20px 0;
        }

        .sidebar .user-info {
            text-align: center;
            padding: 0 20px 20px;
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 20px;
        }

        .sidebar .user-info h3 {
            color: var(--secondary-color);
            font-size: 16px;
        }

        .sidebar .menu {
            list-style-type: none;
        }

        .sidebar .menu li {
            margin-bottom: 5px;
        }

        .sidebar .menu li a {
            display: block;
            padding: 10px 20px;
            color: #6B48F3;
            text-decoration: none;
            transition: background-color 0.3s ease;
            border-left: 4px solid transparent;
        }

        .sidebar .menu li a:hover,
        .sidebar .menu li a.active {
            background-color: rgba(139, 92, 246, 0.1);
            color: var(--primary-color);
            border-left-color: var(--primary-color);
        }

        /* Main Content */
        .main-content {
            flex-grow: 1;
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

        .btn-primary {
            background-color: var(--primary-color);
            color: var(--white);
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }

        .btn-primary:hover {
            background-color: darken(#8B5CF6, 10%);
        }

        /* Stats Container */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background-color: var(--white);
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card h3 {
            color: var(--secondary-color);
            margin-bottom: 10px;
            font-size: 16px;
        }

        .stat-card .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: var(--primary-color);
        }

        /* Recent Quizzes */
        .recent-quizzes {
            background-color: var(--white);
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .recent-quizzes h2 {
            color: var(--primary-color);
            margin-bottom: 20px;
            font-size: 20px;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 10px;
        }

        .empty-state {
            text-align: center;
            padding: 30px;
            color: var(--secondary-color);
        }

        .quiz-card {
            background-color: var(--background-color);
            border-radius: 8px;
            border: 1px solid var(--border-color);
            margin-bottom: 15px;
            overflow: hidden;
        }

        .quiz-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            background-color: var(--white);
            border-bottom: 1px solid var(--border-color);
        }

        .quiz-header h3 {
            color: var(--primary-color);
            font-size: 16px;
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.8em;
            font-weight: bold;
        }

        .status-badge.en\ cours\ d\'écriture {
            background-color: #FFA500;
            color: var(--white);
        }

        .status-badge.lancé {
            background-color: #28a745;
            color: var(--white);
        }

        .status-badge.terminé {
            background-color: #6c757d;
            color: var(--white);
        }

        .quiz-body {
            padding: 15px;
        }

        .quiz-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
        }

        .quiz-info p {
            color: var(--secondary-color);
        }

        .quiz-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .btn-secondary, .btn-danger, .btn-warning {
            padding: 8px 15px;
            border-radius: 4px;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        .btn-secondary {
            background-color: var(--secondary-color);
            color: var(--white);
        }

        .btn-danger {
            background-color: #dc3545;
            color: var(--white);
        }

        .btn-warning {
            background-color: #FFA500;
            color: var(--white);
        }

        .share-link {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .share-link input {
            flex-grow: 1;
            padding: 8px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
        }

        .btn-copy {
            background-color: var(--primary-color);
            color: var(--white);
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
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

            .stats-container {
                grid-template-columns: 1fr;
            }

            .header {
                flex-direction: column;
                align-items: flex-start;
            }

            .header h1 {
                margin-bottom: 15px;
            }

            .quiz-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <nav class="sidebar">
            <div class="user-info">
                <h3><?php echo htmlspecialchars($_SESSION['user']['nom_etablissement'] ?? ''); ?></h3>
            </div>
            <ul class="menu">
                <li><a href="index.php" class="active">Tableau de bord</a></li>
                <li><a href="create-quiz.php">Créer un quiz</a></li>
                <li><a href="mes-quiz.php">Mes quiz</a></li>
                <li><a href="resultats.php">Résultats</a></li>
                <li><a href="../../../logout.php">Déconnexion</a></li>
            </ul>
        </nav>

        <!-- Main Content -->
        <main class="main-content">
            <div class="header">
                <h1>Tableau de bord</h1>
                <a href="create-quiz.php" class="btn-primary">Nouveau Quiz</a>
            </div>

            <!-- Stats Cards -->
            <div class="stats-container">
                <div class="stat-card">
                    <h3>Total Quiz</h3>
                    <div class="stat-value"><?php echo $totalQuiz; ?></div>
                </div>
                <div class="stat-card">
                    <h3>Quiz Actifs</h3>
                    <div class="stat-value"><?php echo $activeQuiz; ?></div>
                </div>
                <div class="stat-card">
                    <h3>Quiz Terminés</h3>
                    <div class="stat-value"><?php echo $finishedQuiz; ?></div>
                </div>
            </div>

            <!-- Recent Quizzes -->
            <section class="recent-quizzes">
                <h2>Quiz Récents</h2>
                <?php if (empty($recentQuizzes)): ?>
                    <div class="empty-state">
                        <p>Vous n'avez pas encore créé de quiz.</p>
                        <a href="create-quiz.php" class="btn-primary">Créer mon premier quiz</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($recentQuizzes as $quiz): ?>
                        <div class="quiz-card">
                            <div class="quiz-header">
                                <h3><?php echo htmlspecialchars($quiz['titre']); ?></h3>
                                <span class="status-badge <?php echo $quiz['status']; ?>">
                                    <?php echo ucfirst($quiz['status']); ?>
                                </span>
                            </div>
                            <div class="quiz-body">
                                <div class="quiz-info">
                                    <p>Questions: <?php echo count($quiz['questions'] ?? []); ?></p>
                                    <p>Réponses: <?php echo $quiz['nb_reponses'] ?? 0; ?></p>
                                </div>
                                <div class="quiz-actions">
                                    <!-- Actions en fonction du statut -->
                                    <?php if ($quiz['status'] === 'en cours d\'écriture'): ?>
                                        <a href="edit-quiz.php?id=<?php echo $quiz['id']; ?>" class="btn-secondary">Modifier</a>
                                        <a href="update-quiz-status.php?id=<?php echo $quiz['id']; ?>&status=lancé" class="btn-primary">Lancer</a>
                                        <a href="delete-quiz.php?id=<?php echo $quiz['id']; ?>" class="btn-danger" 
                                           onclick="return confirm('Voulez-vous vraiment supprimer ce quiz ?')">Supprimer</a>
                                    <?php elseif ($quiz['status'] === 'lancé'): ?>
                                        <div class="share-link">
                                            <?php
                                            if (!isset($quiz['share_token'])) {
                                                $quiz['share_token'] = bin2hex(random_bytes(16));
                                                $quizDb->update($quiz['id'], ['share_token' => $quiz['share_token']]);
                                            }
                                            $shareLink = "http://" . $_SERVER['HTTP_HOST'] . "/Quizzeo-IPSSI/quizzeo/repondre.php?token=" . $quiz['share_token'];
                                            ?>
                                            <input type="text" value="<?php echo $shareLink; ?>" id="shareLink_<?php echo $quiz['id']; ?>" readonly>
                                            <button onclick="copyLink('<?php echo $quiz['id']; ?>')" class="btn-copy">Copier le lien</button>
                                        </div>
                                        <a href="update-quiz-status.php?id=<?php echo $quiz['id']; ?>&status=terminé" class="btn-warning">Terminer</a>
                                    <?php endif; ?>
                                    
                                    <!-- Voir les résultats si le quiz est lancé ou terminé -->
                                    <?php if ($quiz['status'] !== 'en cours d\'écriture'): ?>
                                        <a href="resultats.php?id=<?php echo $quiz['id']; ?>" class="btn-secondary">Voir les résultats</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </section>
        </main>
    </div>

    <script>
    function copyLink(quizId) {
        const input = document.getElementById('shareLink_' + quizId);
        input.select();
        document.execCommand('copy');
        alert('Lien copié dans le presse-papiers !');
    }
    </script>
</body>
</html>