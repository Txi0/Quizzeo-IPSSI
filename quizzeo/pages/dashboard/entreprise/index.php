<?php
// pages/dashboard/entreprise/index.php
session_start();
require_once '../../../includes/auth.php';

// Vérification du rôle
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'entreprise') {
    header('Location: ../../login.php');
    exit;
}

// Récupération des questionnaires
$quizDb = new JsonDatabase('quizzes.json');
$entrepriseQuizzes = array_filter($quizDb->getAll(), function($quiz) {
    return $quiz['user_id'] === $_SESSION['user']['id'];
});
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - Quizzeo</title>
    <link rel="stylesheet" href="../../../assets/css/dashboard-entreprise.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="logo">
                <img src="../../../assets/images/logo.png" alt="Quizzeo">
            </div>
            <div class="user-info">
                <h3><?php echo htmlspecialchars($_SESSION['user']['nom_entreprise']); ?></h3>
                <p>Entreprise</p>
            </div>
            <ul class="sidebar-menu">
                <li><a href="index.php" class="active">Tableau de bord</a></li>
                <li><a href="create-quiz.php">Créer un questionnaire</a></li>
                <li><a href="mes-questionnaires.php">Mes questionnaires</a></li>
                <li><a href="analyses.php">Analyses</a></li>
                <li><a href="../../../logout.php">Déconnexion</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1>Tableau de bord</h1>
                <a href="create-quiz.php" class="btn btn-primary">Nouveau questionnaire</a>
            </div>

            <!-- Stats -->
            <div class="stats-container">
                <div class="stat-card">
                    <h3>Total Questionnaires</h3>
                    <div class="stat-value"><?php echo count($entrepriseQuizzes); ?></div>
                </div>
                <div class="stat-card">
                    <h3>Questionnaires Actifs</h3>
                    <div class="stat-value">
                        <?php 
                        echo count(array_filter($entrepriseQuizzes, function($quiz) {
                            return $quiz['status'] === 'lancé';
                        })); 
                        ?>
                    </div>
                </div>
                <div class="stat-card">
                    <h3>Total Réponses</h3>
                    <div class="stat-value">
                        <?php 
                        echo array_sum(array_map(function($quiz) {
                            return $quiz['nb_reponses'] ?? 0;
                        }, $entrepriseQuizzes)); 
                        ?>
                    </div>
                </div>
            </div>

            <!-- Questionnaires Récents -->
            <section class="recent-questionnaires">
                <h2>Questionnaires Récents</h2>
                <div class="questionnaires-grid">
                    <?php 
                    $recentQuizzes = array_slice($entrepriseQuizzes, 0, 6);
                    foreach ($recentQuizzes as $quiz): 
                    ?>
                    <div class="quiz-card">
                        <h3><?php echo htmlspecialchars($quiz['titre']); ?></h3>
                        <p class="status"><?php echo ucfirst($quiz['status']); ?></p>
                        <div class="quiz-info">
                            <span>Réponses: <?php echo $quiz['nb_reponses'] ?? 0; ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>
        </div>
    </div>
</body>
</html>