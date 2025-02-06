<?php
// pages/dashboard/ecole/mes-quiz.php
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
    return isset($quiz['user_id']) && $quiz['user_id'] === $_SESSION['user']['id'];
});

// Fonction pour générer un lien de partage
function generateShareToken() {
    return bin2hex(random_bytes(16));
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Quiz - École</title>
    <link rel="stylesheet" href="../../../assets/css/mes-quiz-ecole.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <nav class="sidebar">
            <div class="logo">
                <img src="../../../assets/images/logo.png" alt="Quizzeo">
            </div>
            <ul class="menu">
                <li><a href="index.php">Tableau de bord</a></li>
                <li><a href="create-quiz.php">Créer un quiz</a></li>
                <li class="active"><a href="mes-quiz.php">Mes quiz</a></li>
                <li><a href="resultats.php">Résultats</a></li>
                <li><a href="../../../logout.php">Déconnexion</a></li>
            </ul>
        </nav>

        <main class="main-content">
            <div class="header">
                <h1>Mes Quiz</h1>
                <a href="create-quiz.php" class="btn-primary">Nouveau Quiz</a>
            </div>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    Le quiz a été enregistré avec succès !
                </div>
            <?php endif; ?>

            <div class="quiz-list">
                <?php if (empty($schoolQuizzes)): ?>
                    <div class="empty-state">
                        <p>Vous n'avez pas encore créé de quiz.</p>
                        <a href="create-quiz.php" class="btn-primary">Créer mon premier quiz</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($schoolQuizzes as $quiz): ?>
                        <div class="quiz-card">
                            <div class="quiz-header">
                                <h3><?php echo htmlspecialchars($quiz['titre']); ?></h3>
                                <span class="status-badge <?php echo $quiz['status']; ?>">
                                    <?php echo ucfirst($quiz['status']); ?>
                                </span>
                            </div>

                            <div class="quiz-info">
                                <p>Questions: <?php echo count($quiz['questions']); ?></p>
                                <p>Points total: <?php echo $quiz['points_total']; ?></p>
                                <p>Réponses: <?php echo $quiz['nb_reponses'] ?? 0; ?></p>
                            </div>

                            <div class="quiz-actions">
                                <?php if ($quiz['status'] === 'en cours d\'écriture'): ?>
                                    <a href="edit-quiz.php?id=<?php echo $quiz['id']; ?>" 
                                       class="btn-secondary">Modifier</a>
                                    <button onclick="lancerQuiz('<?php echo $quiz['id']; ?>')" 
                                            class="btn-primary">Lancer</button>
                                <?php elseif ($quiz['status'] === 'lancé'): ?>
                                    <div class="share-link">
                                        <?php
                                        if (!isset($quiz['share_token'])) {
                                            $quiz['share_token'] = generateShareToken();
                                            $quizDb->update($quiz['id'], ['share_token' => $quiz['share_token']]);
                                        }
                                        $shareLink = "http://" . $_SERVER['HTTP_HOST'] . "/repondre.php?token=" . $quiz['share_token'];
                                        ?>
                                        <input type="text" value="<?php echo $shareLink; ?>" 
                                               id="shareLink_<?php echo $quiz['id']; ?>" readonly>
                                        <button onclick="copyLink('<?php echo $quiz['id']; ?>')" 
                                                class="btn-copy">Copier le lien</button>
                                    </div>
                                    <button onclick="terminerQuiz('<?php echo $quiz['id']; ?>')" 
                                            class="btn-warning">Terminer</button>
                                <?php endif; ?>
                                
                                <a href="resultats.php?id=<?php echo $quiz['id']; ?>" 
                                   class="btn-secondary">Voir les résultats</a>
                                
                                <?php if ($quiz['status'] === 'en cours d\'écriture'): ?>
                                    <button onclick="supprimerQuiz('<?php echo $quiz['id']; ?>')" 
                                            class="btn-danger">Supprimer</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
    function copyLink(quizId) {
        const input = document.getElementById('shareLink_' + quizId);
        input.select();
        document.execCommand('copy');
        alert('Lien copié !');
    }

    function lancerQuiz(quizId) {
        if (confirm('Voulez-vous vraiment lancer ce quiz ?')) {
            window.location.href = 'update-quiz-status.php?id=' + quizId + '&status=lancé';
        }
    }

    function terminerQuiz(quizId) {
        if (confirm('Voulez-vous vraiment terminer ce quiz ? Les étudiants ne pourront plus y répondre.')) {
            window.location.href = 'update-quiz-status.php?id=' + quizId + '&status=terminé';
        }
    }

    function supprimerQuiz(quizId) {
        if (confirm('Voulez-vous vraiment supprimer ce quiz ? Cette action est irréversible.')) {
            window.location.href = 'delete-quiz.php?id=' + quizId;
        }
    }
    </script>
</body>
</html>