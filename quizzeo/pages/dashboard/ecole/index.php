<?php
// pages/dashboard/ecole/index.php
session_start();
require_once '../../../includes/auth.php';

// Vérification du rôle
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'ecole') {
    header('Location: ../../login.php');
    exit;
}

// Récupération des quiz
$quizDb = new JsonDatabase('quizzes.json');
$schoolQuizzes = array_filter($quizDb->getAll(), function($quiz) {
    return $quiz['user_id'] === $_SESSION['user']['id'];
});

// Statistiques
$totalQuiz = count($schoolQuizzes);
$activeQuiz = count(array_filter($schoolQuizzes, fn($q) => $q['status'] === 'lancé'));
$completedQuiz = count(array_filter($schoolQuizzes, fn($q) => $q['status'] === 'terminé'));
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard École - Quizzeo</title>
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
                <h3>École: <?php echo htmlspecialchars($_SESSION['user']['nom_etablissement']); ?></h3>
            </div>
            <ul class="menu">
                <li class="active"><a href="index.php">Tableau de bord</a></li>
                <li><a href="create-quiz.php">Créer un quiz</a></li>
                <li><a href="mes-quiz.php">Mes quiz</a></li>
                <li><a href="resultats.php">Résultats</a></li>
                <li><a href="../../../logout.php">Déconnexion</a></li>
            </ul>
        </nav>

        <!-- Main Content -->
        <main class="main-content">
            <header class="dashboard-header">
                <h1>Tableau de bord</h1>
                <a href="create-quiz.php" class="btn-primary">Nouveau Quiz</a>
            </header>

            <!-- Statistiques -->
            <div class="stats-container">
                <div class="stat-card">
                    <h3>Total Quiz</h3>
                    <p class="stat-number"><?php echo $totalQuiz; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Quiz Actifs</h3>
                    <p class="stat-number"><?php echo $activeQuiz; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Quiz Terminés</h3>
                    <p class="stat-number"><?php echo $completedQuiz; ?></p>
                </div>
            </div>

            <!-- Quiz Récents -->
            <section class="recent-quizzes">
                <h2>Quiz Récents</h2>
                <div class="quiz-grid">
                    <?php 
                    $recentQuizzes = array_slice($schoolQuizzes, 0, 6);
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
                            <p>Nombre de questions: <?php echo count($quiz['questions'] ?? []); ?></p>
                            <p>Réponses: <?php echo $quiz['nb_reponses'] ?? 0; ?></p>
                            <?php if ($quiz['status'] === 'lancé'): ?>
                                <p>Moyenne: <?php echo number_format($quiz['moyenne'] ?? 0, 2); ?>/20</p>
                            <?php endif; ?>
                        </div>
                        <div class="quiz-footer">
                            <a href="voir-quiz.php?id=<?php echo $quiz['id']; ?>" class="btn-secondary btn-small">
                                Voir les détails
                            </a>
                            <?php if ($quiz['status'] === 'lancé'): ?>
                                <button onclick="partagerQuiz('<?php echo $quiz['id']; ?>')" class="btn-primary btn-small">
                                    Partager
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>
        </main>
    </div>

    <script>
    function partagerQuiz(quizId) {
        // Générer le lien de partage
        const shareLink = `${window.location.origin}/repondre.php?id=${quizId}`;
        
        // Copier dans le presse-papiers
        navigator.clipboard.writeText(shareLink).then(() => {
            alert('Lien copié dans le presse-papier !');
        });
    }
    </script>
</body>
</html>