<?php
// pages/dashboard/entreprise/index.php
session_start();
require_once '../../../includes/auth.php';

// Vérification de l'authentification et du rôle
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'entreprise') {
    header('Location: ../../login.php');
    exit;
}

// Récupération des quiz de l'entreprise
$quizzes = new JsonDatabase('quizzes.json');
$entrepriseQuizzes = array_filter($quizzes->getAll(), function($quiz) {
    return $quiz['user_id'] === $_SESSION['user']['id'];
});
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Entreprise - Quizzeo</title>
    <link rel="stylesheet" href="../../../assets/css/dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <nav class="sidebar">
            <div class="logo">
                <img src="../../../assets/images/logo.png" alt="Quizzeo">
            </div>
            <div class="user-info">
                <h3><?php echo htmlspecialchars($_SESSION['user']['nom_entreprise']); ?></h3>
                <p>Entreprise</p>
            </div>
            <ul class="menu">
                <li class="active"><a href="index.php">Tableau de bord</a></li>
                <li><a href="create-quiz.php">Créer un questionnaire</a></li>
                <li><a href="mes-quiz.php">Mes questionnaires</a></li>
                <li><a href="analyse.php">Analyses</a></li>
                <li><a href="profile.php">Profil</a></li>
                <li><a href="../../../logout.php">Déconnexion</a></li>
            </ul>
        </nav>

        <!-- Main Content -->
        <main class="main-content">
            <header class="dashboard-header">
                <h1>Tableau de bord</h1>
                <a href="create-quiz.php" class="btn-primary">Nouveau questionnaire</a>
            </header>

            <!-- Statistiques -->
            <div class="stats-container">
                <div class="stat-card">
                    <h3>Total Questionnaires</h3>
                    <p class="stat-number"><?php echo count($entrepriseQuizzes); ?></p>
                </div>
                <div class="stat-card">
                    <h3>Questionnaires Actifs</h3>
                    <p class="stat-number"><?php 
                        echo count(array_filter($entrepriseQuizzes, function($quiz) {
                            return $quiz['status'] === 'lancé';
                        }));
                    ?></p>
                </div>
                <div class="stat-card">
                    <h3>Total Réponses</h3>
                    <p class="stat-number"><?php 
                        echo array_sum(array_column($entrepriseQuizzes, 'nb_reponses'));
                    ?></p>
                </div>
            </div>

            <!-- Questionnaires Récents -->
            <section class="recent-quizzes">
                <h2>Questionnaires Récents</h2>
                <div class="quiz-grid">
                    <?php 
                    $recentQuizzes = array_slice($entrepriseQuizzes, 0, 6);
                    foreach ($recentQuizzes as $quiz): 
                    ?>
                    <div class="quiz-card">
                        <div class="quiz-header">
                            <h3><?php echo htmlspecialchars($quiz['titre']); ?></h3>
                            <span class="status-badge <?php echo $quiz['status']; ?>">
                                <?php echo ucfirst($quiz['status']); ?>
                            </span>
                        </div>
                        <div class="quiz-body">
                            <p>Type: <?php echo $quiz['type'] ?? 'Satisfaction'; ?></p>
                            <p>Réponses reçues: <?php echo $quiz['nb_reponses']; ?></p>
                            <p>Créé le: <?php echo date('d/m/Y', strtotime($quiz['created_at'])); ?></p>
                        </div>
                        <div class="quiz-footer">
                            <a href="voir-quiz.php?id=<?php echo $quiz['id']; ?>" class="btn-secondary">
                                Voir les résultats
                            </a>
                            <?php if ($quiz['status'] === 'en cours d\'écriture'): ?>
                            <a href="modifier-quiz.php?id=<?php echo $quiz['id']; ?>" class="btn-primary">
                                Modifier
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>
        </main>
    </div>
</body>
</html>