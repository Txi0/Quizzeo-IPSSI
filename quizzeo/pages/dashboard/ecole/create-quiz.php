<?php
// pages/dashboard/ecole/create-quiz.php
session_start();
require_once '../../../includes/auth.php';

// Vérification du rôle
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'ecole') {
    header('Location: ../../login.php');
    exit;
}

// Traitement de la création du quiz
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quizDb = new JsonDatabase('quizzes.json');
    
    $quiz = [
        'id' => uniqid(),
        'user_id' => $_SESSION['user']['id'],
        'titre' => $_POST['titre'],
        'description' => $_POST['description'],
        'questions' => [],
        'status' => 'en cours d\'écriture',
        'created_at' => date('Y-m-d H:i:s'),
        'points_total' => 0
    ];

    foreach ($_POST['questions'] as $questionData) {
        $question = [
            'id' => uniqid(),
            'texte' => $questionData['texte'],
            'points' => (int)$questionData['points'],
            'options' => $questionData['options'],
            'reponse_correcte' => $questionData['reponse_correcte']
        ];
        $quiz['points_total'] += $question['points'];
        $quiz['questions'][] = $question;
    }

    if ($quizDb->insert($quiz)) {
        header('Location: mes-quiz.php?success=1');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un quiz - École</title>
    <link rel="stylesheet" href="../../../assets/css/create-quiz-ecole.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <nav class="sidebar">
            <ul class="menu">
                <li><a href="index.php">Tableau de bord</a></li>
                <li class="active"><a href="create-quiz.php">Créer un quiz</a></li>
                <li><a href="mes-quiz.php">Mes quiz</a></li>
                <li><a href="resultats.php">Résultats</a></li>
                <li><a href="../../../logout.php">Déconnexion</a></li>
            </ul>
        </nav>

        <main class="main-content">
            <div class="header">
                <h1>Créer un nouveau quiz</h1>
            </div>

            <form method="POST" action="" id="quizForm" class="quiz-form">
                <div class="form-section">
                    <h2>Informations générales</h2>
                    <div class="form-group">
                        <label for="titre">Titre du quiz *</label>
                        <input type="text" id="titre" name="titre" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="3"></textarea>
                    </div>
                </div>

                <div class="form-section">
                    <h2>Questions</h2>
                    <div id="questions-container"></div>
                    <button type="button" class="btn-add" onclick="addQuestion()">+ Ajouter une question</button>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">Enregistrer le quiz</button>
                    <a href="mes-quiz.php" class="btn-secondary">Annuler</a>
                </div>
            </form>
        </main>
    </div>

    <script>
    let questionCount = 0;

    function addQuestion() {
        const container = document.getElementById('questions-container');
        const questionBlock = document.createElement('div');
        questionBlock.className = 'question-block';
        questionBlock.innerHTML = `
            <div class="form-group">
                <label>Question ${questionCount + 1}</label>
                <input type="text" name="questions[${questionCount}][texte]" required>
            </div>
            <div class="form-group">
                <label>Points</label>
                <input type="number" name="questions[${questionCount}][points]" min="1" value="1" required>
            </div>
            <div class="options-container">
                <label>Options de réponse</label>
                <div id="options-list-${questionCount}">
                    <div class="option-item">
                        <input type="text" name="questions[${questionCount}][options][]" required>
                        <input type="radio" name="questions[${questionCount}][reponse_correcte]" value="0" required>
                        <label>Réponse correcte</label>
                    </div>
                </div>
                <button type="button" class="btn-secondary" onclick="addOption(${questionCount})">
                    + Ajouter une option
                </button>
            </div>
            <button type="button" class="btn-remove" onclick="removeQuestion(this)">
                Supprimer la question
            </button>
        `;
        container.appendChild(questionBlock);
        questionCount++;
    }

    function addOption(questionId) {
        const optionsList = document.getElementById(`options-list-${questionId}`);
        const optionCount = optionsList.children.length;
        const optionDiv = document.createElement('div');
        optionDiv.className = 'option-item';
        optionDiv.innerHTML = `
            <input type="text" name="questions[${questionId}][options][]" required>
            <input type="radio" name="questions[${questionId}][reponse_correcte]" value="${optionCount}">
            <label>Réponse correcte</label>
        `;
        optionsList.appendChild(optionDiv);
    }

    function removeQuestion(button) {
        button.parentElement.remove();
        updateQuestionNumbers();
    }

    function updateQuestionNumbers() {
        const questions = document.querySelectorAll('.question-block');
        questions.forEach((question, index) => {
            question.querySelector('label').textContent = `Question ${index + 1}`;
        });
        questionCount = questions.length;
    }

    // Ajouter une première question au chargement
    document.addEventListener('DOMContentLoaded', addQuestion);
    </script>
</body>
</html>