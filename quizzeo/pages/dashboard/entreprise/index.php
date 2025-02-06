<?php
// pages/dashboard/entreprise/index.php
session_start();
require_once '../../../includes/auth.php';

// Vérification du rôle
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'entreprise') {
    header('Location: ../../login.php');
    exit;
}

// Récupération des quiz de l'entreprise
$quizDb = new JsonDatabase('quizzes.json');
$responsesDb = new JsonDatabase('responses.json');

$companyQuizzes = array_filter($quizDb->getAll(), function($quiz) {
    return isset($quiz['user_id']) && $quiz['user_id'] === $_SESSION['user']['id'];
});

// Statistiques
$totalQuiz = count($companyQuizzes);
$activeQuiz = count(array_filter($companyQuizzes, function($quiz) {
    return isset($quiz['status']) && $quiz['status'] === 'lancé';
}));
$finishedQuiz = count(array_filter($companyQuizzes, function($quiz) {
    return isset($quiz['status']) && $quiz['status'] === 'terminé';
}));

// Récupérer les quiz récents
$recentQuizzes = array_slice($companyQuizzes, 0, 5);
usort($recentQuizzes, function($a, $b) {
    return strtotime($b['created_at'] ?? 0) - strtotime($a['created_at'] ?? 0);
});

// Calculer le nombre total de réponses
$totalResponses = 0;
$responsesPerQuiz = [];
$allResponses = $responsesDb->getAll();

foreach ($companyQuizzes as $quiz) {
    $quizResponses = array_filter($allResponses, function($response) use ($quiz) {
        return $response['quiz_id'] === $quiz['id'];
    });
    
    $responsesPerQuiz[$quiz['id']] = count($quizResponses);
    $totalResponses += count($quizResponses);
}

// Calcul des compétences évaluées
$competencesEvaluees = [];
foreach ($companyQuizzes as $quiz) {
    foreach ($quiz['questions'] as $question) {
        if (!empty($question['competence'])) {
            $competencesEvaluees[] = $question['competence'];
        }
    }
}
$competencesUniques = array_count_values($competencesEvaluees);
arsort($competencesUniques);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord - Entreprise</title>
    <link rel="stylesheet" href="../../../assets/css/dashboard-entreprise.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <nav class="sidebar">

            <div class="user-info">
                <h3><?php echo htmlspecialchars($_SESSION['user']['nom_entreprise'] ?? 'Mon Entreprise'); ?></h3>
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
            <div class="dashboard-header">
                <h1>Tableau de Bord</h1>
                <a href="create-quiz.php" class="btn-primary">Nouveau Quiz</a>
            </div>

            <!-- Stats Overview -->
            <section class="stats-overview">
                <div class="stat-card">
                    <div class="stat-icon">📊</div>
                    <div class="stat-content">
                        <h3>Total Quiz</h3>
                        <p class="stat-number"><?php echo $totalQuiz; ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">🟢</div>
                    <div class="stat-content">
                        <h3>Quiz Actifs</h3>
                        <p class="stat-number"><?php echo $activeQuiz; ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">✍️</div>
                    <div class="stat-content">
                        <h3>Total Réponses</h3>
                        <p class="stat-number"><?php echo $totalResponses; ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">🏁</div>
                    <div class="stat-content">
                        <h3>Quiz Terminés</h3>
                        <p class="stat-number"><?php echo $finishedQuiz; ?></p>
                    </div>
                </div>
            </section>

            <!-- Compétences Évaluées -->
            <section class="competences-section">
                <div class="section-header">
                    <h2>Compétences Évaluées</h2>
                </div>
                <div class="competences-grid">
                    <?php 
                    $topCompetences = array_slice($competencesUniques, 0, 6);
                    foreach ($topCompetences as $competence => $count): 
                    ?>
                        <div class="competence-card">
                            <h3><?php echo htmlspecialchars($competence); ?></h3>
                            <p><?php echo $count; ?> questions</p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <!-- Recent Quizzes -->
            <section class="recent-quizzes">
                <div class="section-header">
                    <h2>Mes Quiz Récents</h2>
                    <a href="mes-quiz.php" class="btn-secondary">Voir tous</a>
                </div>

                <?php if (empty($recentQuizzes)): ?>
                    <div class="empty-state">
                        <p>Vous n'avez pas encore créé de quiz.</p>
                        <a href="create-quiz.php" class="btn-primary">Créer mon premier quiz</a>
                    </div>
                <?php else: ?>
                    <div class="quiz-grid">
                        <?php foreach ($recentQuizzes as $quiz): ?>
                            <div class="quiz-card">
                                <div class="quiz-header">
                                    <h3><?php echo htmlspecialchars($quiz['titre']); ?></h3>
                                    <span class="status-badge <?php echo $quiz['status']; ?>">
                                        <?php 
                                        switch($quiz['status']) {
                                            case 'en cours d\'écriture': 
                                                echo 'En création'; 
                                                break;
                                            case 'lancé': 
                                                echo 'Actif'; 
                                                break;
                                            case 'terminé': 
                                                echo 'Terminé'; 
                                                break;
                                            default: 
                                                echo htmlspecialchars($quiz['status']);
                                        }
                                        ?>
                                    </span>
                                </div>
                                <div class="quiz-body">
                                    <div class="quiz-stats">
                                        <div class="stat">
                                            <span class="stat-label">Questions :</span>
                                            <span class="stat-value"><?php echo count($quiz['questions']); ?></span>
                                        </div>
                                        <div class="stat">
                                            <span class="stat-label">Réponses :</span>
                                            <span class="stat-value"><?php echo $responsesPerQuiz[$quiz['id']] ?? 0; ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="quiz-footer">
                                    <a href="resultats.php?id=<?php echo $quiz['id']; ?>" class="btn-secondary">
                                        Voir les résultats
                                    </a>
                                    <?php if ($quiz['status'] === 'en cours d\'écriture'): ?>
                                        <a href="edit-quiz.php?id=<?php echo $quiz['id']; ?>" class="btn-primary">
                                            Modifier
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        </main>
    </div>
</body>
</html>