<?php
// pages/dashboard/ecole/edit-quiz.php
session_start();
require_once '../../../includes/auth.php';

// Vérification du rôle
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'ecole') {
    header('Location: ../../login.php');
    exit;
}

// Récupération du quiz
$quizId = $_GET['id'] ?? '';
if (empty($quizId)) {
    header('Location: mes-quiz.php');
    exit;
}

$quizDb = new JsonDatabase('quizzes.json');
$quiz = $quizDb->findById($quizId);

// Vérifier que le quiz existe et appartient à l'école
if (!$quiz || $quiz['user_id'] !== $_SESSION['user']['id']) {
    header('Location: mes-quiz.php');
    exit;
}

// Vérifier que le quiz peut être modifié
if ($quiz['status'] !== 'en cours d\'écriture') {
    $_SESSION['error'] = "Seuls les quiz en cours d'écriture peuvent être modifiés.";
    header('Location: mes-quiz.php');
    exit;
}

// Traitement de la modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $updatedQuiz = [
        'titre' => $_POST['titre'],
        'description' => $_POST['description'],
        'questions' => [],
        'points_total' => 0
    ];

    foreach ($_POST['questions'] as $questionData) {
        $question = [
            'id' => $questionData['id'] ?? uniqid(),
            'texte' => $questionData['texte'],
            'points' => (int)$questionData['points'],
            'options' => $questionData['options'],
            'reponse_correcte' => $questionData['reponse_correcte']
        ];
        $updatedQuiz['points_total'] += $question['points'];
        $updatedQuiz['questions'][] = $question;
    }

    if ($quizDb->update($quizId, $updatedQuiz)) {
        $_SESSION['success'] = "Le quiz a été mis à jour avec succès.";
        header('Location: mes-quiz.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier le quiz - École</title>
    <link rel="stylesheet" href="../../../assets/css/create-quiz-ecole.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <nav class="sidebar">
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
                <h1>Modifier le quiz</h1>
            </div>

            <form method="POST" action="" id="quizForm" class="quiz-form">
                <div class="form-section">
                    <h2>Informations générales</h2>
                    <div class="form-group">
                        <label for="titre">Titre du quiz *</label>
                        <input type="text" id="titre" name="titre" 
                               value="<?php echo htmlspecialchars($quiz['titre']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="3"><?php 
                            echo htmlspecialchars($quiz['description'] ?? ''); 
                        ?></textarea>
                    </div>
                </div>

                <div class="form-section">
                    <h2>Questions</h2>
                    <div id="questions-container">
                        <?php foreach ($quiz['questions'] as $index => $question): ?>
                            <div class="question-block">
                                <input type="hidden" name="questions[<?php echo $index; ?>][id]" 
                                       value="<?php echo $question['id']; ?>">
                                <div class="form-group">
                                    <label>Question <?php echo $index + 1; ?></label>
                                    <input type="text" name="questions[<?php echo $index; ?>][texte]" 
                                           value="<?php echo htmlspecialchars($question['texte']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Points</label>
                                    <input type="number" name="questions[<?php echo $index; ?>][points]" 
                                           value="<?php echo $question['points']; ?>" min="1" required>
                                </div>
                                <div class="options-container">
                                    <label>Options de réponse</label>
                                    <div id="options-list-<?php echo $index; ?>">
                                        <?php foreach ($question['options'] as $optionIndex => $option): ?>
                                            <div class="option-item">
                                                <input type="text" 
                                                       name="questions[<?php echo $index; ?>][options][]" 
                                                       value="<?php echo htmlspecialchars($option); ?>" required>
                                                <input type="radio" 
                                                       name="questions[<?php echo $index; ?>][reponse_correcte]" 
                                                       value="<?php echo $optionIndex; ?>"
                                                       <?php echo ($question['reponse_correcte'] == $optionIndex ? 'checked' : ''); ?>>
                                                <label>Réponse correcte</label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <button type="button" class="btn-secondary" 
                                            onclick="addOption(<?php echo $index; ?>)">
                                        + Ajouter une option
                                    </button>
                                </div>
                                <button type="button" class="btn-remove" onclick="removeQuestion(this)">
                                    Supprimer la question
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="btn-add" onclick="addQuestion()">+ Ajouter une question</button>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">Enregistrer les modifications</button>
                    <a href="mes-quiz.php" class="btn-secondary">Annuler</a>
                </div>
            </form>
        </main>
    </div>

    <script>
    let questionCount = <?php echo count($quiz['questions']); ?>;

    // Reprendre les fonctions JavaScript de create-quiz.php
    // (addQuestion, addOption, removeQuestion, updateQuestionNumbers)
    </script>
</body>
</html>