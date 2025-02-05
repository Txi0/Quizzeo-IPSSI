<?php
session_start();
require_once '../../../includes/auth.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'entreprise') {
    header('Location: ../../login.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: mes-questionnaires.php');
    exit;
}

$quizId = intval($_GET['id']);
$filePath = '../../../data/quizzes.json';

// Lire le fichier JSON
if (file_exists($filePath)) {
    $quizzes = json_decode(file_get_contents($filePath), true);
    $quizToEdit = null;

    // Rechercher le quiz à modifier
    foreach ($quizzes as $quiz) {
        if ($quiz['id'] === $quizId) {
            $quizToEdit = $quiz;
            break;
        }
    }

    if (!$quizToEdit) {
        echo "Questionnaire introuvable.";
        exit;
    }
} else {
    echo "Aucun fichier de questionnaires trouvé.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier le questionnaire - Quizzeo</title>
    <link rel="stylesheet" href="../../../assets/css/base.css">
    <link rel="stylesheet" href="../../../assets/css/create-quiz.css">
</head>
<body>
    <div class="dashboard-container">
        <?php include '../../../templates/sidebar.php'; ?>

        <div class="main-content">
            <div class="dashboard-header">
                <h1>Modifier le questionnaire</h1>
            </div>

            <form class="quiz-form" method="POST" action="update-quiz.php">
                <input type="hidden" name="id" value="<?php echo $quizToEdit['id']; ?>">

                <div class="form-group">
                    <label for="titre">Titre du questionnaire *</label>
                    <input type="text" id="titre" name="titre" value="<?php echo htmlspecialchars($quizToEdit['titre']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description"><?php echo htmlspecialchars($quizToEdit['description']); ?></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">Enregistrer les modifications</button>
                    <a href="mes-questionnaires.php" class="btn-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
