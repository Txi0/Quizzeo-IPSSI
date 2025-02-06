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
    <link rel="stylesheet" href="../../../assets/css/dashboard-ecole.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <nav class="sidebar">
            <div class="logo">
                <img src="../../../assets/images/logo.png" alt="Quizzeo">
            </div>
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
