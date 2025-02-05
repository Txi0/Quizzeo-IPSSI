<?php
// pages/dashboard/entreprise/mes-questionnaires.php
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
    <title>Mes Questionnaires - Quizzeo</title>
    <link rel="stylesheet" href="../../../assets/css/base.css">
    <link rel="stylesheet" href="../../../assets/css/mes-questionnaires.css">
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
            <ul class="menu">
                <li><a href="index.php">Tableau de bord</a></li>
                <li><a href="create-quiz.php">Créer un questionnaire</a></li>
                <li><a href="mes-questionnaires.php" class="active">Mes questionnaires</a></li>
                <li><a href="analyses.php">Analyses</a></li>
                <li><a href="../../../logout.php">Déconnexion</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1>Mes Questionnaires</h1>
                <a href="create-quiz.php" class="btn-primary">Nouveau questionnaire</a>
            </div>

            <div class="quiz-list">
                <?php if (empty($entrepriseQuizzes)): ?>
                    <div class="empty-state">
                        <p>Vous n'avez pas encore créé de questionnaire.</p>
                        <a href="create-quiz.php" class="btn-primary">Créer mon premier questionnaire</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($entrepriseQuizzes as $quiz): ?>
                        <div class="quiz-card">
                            <div class="quiz-header">
                                <h3><?php echo htmlspecialchars($quiz['titre']); ?></h3>
                                <span class="status-badge <?php echo $quiz['status']; ?>">
                                    <?php echo ucfirst($quiz['status']); ?>
                                </span>
                            </div>
                            <div class="quiz-body">
                                <?php if (!empty($quiz['description'])): ?>
                                    <p><?php echo htmlspecialchars($quiz['description']); ?></p>
                                <?php endif; ?>
                                <div class="quiz-stats">
                                    <span>Réponses: <?php echo $quiz['nb_reponses'] ?? 0; ?></span>
                                    <span>Questions: <?php echo count($quiz['questions'] ?? []); ?></span>
                                </div>
                            </div>
                            <div class="quiz-actions">
                                <?php if ($quiz['status'] === 'en cours d\'écriture'): ?>
                                    <a href="edit-quiz.php?id=<?php echo $quiz['id']; ?>" class="btn-secondary">Modifier</a>
                                    <button onclick="lancerQuiz('<?php echo $quiz['id']; ?>')" class="btn-primary">Lancer</button>
                                <?php elseif ($quiz['status'] === 'lancé'): ?>
                                    <button onclick="copierLien('<?php echo $quiz['id']; ?>')" class="btn-secondary">Copier le lien</button>
                                    <button onclick="terminerQuiz('<?php echo $quiz['id']; ?>')" class="btn-primary">Terminer</button>
                                <?php endif; ?>
                                <a href="analyse.php?id=<?php echo $quiz['id']; ?>" class="btn-secondary">Voir les résultats</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
    function lancerQuiz(id) {
        if (confirm('Voulez-vous vraiment lancer ce questionnaire ?')) {
            // Appel AJAX pour changer le statut
            // À implémenter
        }
    }

    function terminerQuiz(id) {
        if (confirm('Voulez-vous vraiment terminer ce questionnaire ?')) {
            // Appel AJAX pour changer le statut
            // À implémenter
        }
    }

    function copierLien(id) {
        // Copier le lien dans le presse-papier
        const lien = `${window.location.origin}/repondre.php?id=${id}`;
        navigator.clipboard.writeText(lien).then(() => {
            alert('Lien copié dans le presse-papier !');
        });
    }
    </script>
</body>
</html>