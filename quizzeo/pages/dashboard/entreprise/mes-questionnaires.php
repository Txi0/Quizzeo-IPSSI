<?php
session_start();
require_once '../../../includes/auth.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'entreprise') {
    header('Location: ../../login.php');
    exit;
}

// Lire les questionnaires depuis le fichier JSON
$filePath = '../../../data/quizzes.json';
$questionnaires = [];

if (file_exists($filePath)) {
    $jsonData = file_get_contents($filePath);
    $questionnaires = json_decode($jsonData, true);
}
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
        <?php include '../../../templates/sidebar.php'; ?>

        <div class="main-content">
            <div class="dashboard-header">
                <h1>Mes Questionnaires</h1>
                <a href="create-quiz.php" class="btn-primary">Nouveau questionnaire</a>
            </div>

            <div class="questionnaires-grid">
                <?php if (!empty($questionnaires)): ?>
                    <?php foreach ($questionnaires as $quiz): ?>
                        <div class="questionnaire-card">
                            <h3><?php echo htmlspecialchars($quiz['titre']); ?></h3>
                            <p>Status : <span class="status-badge"><?php echo htmlspecialchars($quiz['status']); ?></span></p>
                            <p>Réponses : <?php echo intval($quiz['reponses']); ?></p>

                            <div class="quiz-actions">
                                <a href="edit-quiz.php?id=<?php echo $quiz['id']; ?>" class="btn-secondary">Modifier</a>
                                <button class="btn-danger" onclick="deleteQuiz(<?php echo $quiz['id']; ?>)">Supprimer</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Aucun questionnaire trouvé.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function deleteQuiz(id) {
            if (confirm("Êtes-vous sûr de vouloir supprimer ce questionnaire ? Cette action est irréversible.")) {
                window.location.href = "delete-quiz.php?id=" + id;
            }
        }
    </script>
</body>
</html>
