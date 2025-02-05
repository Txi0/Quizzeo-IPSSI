<?php
// pages/dashboard/ecole/create-quiz.php
session_start();
require_once '../../../includes/auth.php';

// Vérification du rôle
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'ecole') {
    header('Location: ../../login.php');
    exit;
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quizDb = new JsonDatabase('quizzes.json');
    
    $quiz = [
        'id' => uniqid(),
        'user_id' => $_SESSION['user']['id'],
        'titre' => $_POST['titre'],
        'description' => $_POST['description'],
        'type' => 'examen',
        'questions' => [],
        'status' => 'en cours d\'écriture',
        'nb_reponses' => 0,
        'created_at' => date('Y-m-d H:i:s'),
        'points_total' => 0
    ];

    // Traitement des questions
    foreach ($_POST['questions'] as $questionData) {
        $question = [
            'id' => uniqid(),
            'texte' => $questionData['texte'],
            'points' => intval($questionData['points']),
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
    <title>Créer un Quiz - École - Quizzeo</title>
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
                <h3><?php echo htmlspecialchars($_SESSION['user']['nom_etablissement']); ?></h3>
            </div>
            <ul class="menu">
                <li><a href="index.php">Tableau de bord</a></li>
                <li class="active"><a href="create-quiz.php">Créer un quiz</a></li>
                <li><a href="mes-quiz.php">Mes quiz</a></li>
                <li><a href="resultats.php">Résultats</a></li>
                <li><a href="../../../logout.php">Déconnexion</a></li>
            </ul>
        </nav>

        <!-- Main Content -->
        <main class="main-content">
            <header class="dashboard-header">
                <h1>Créer un nouveau quiz</h1>
            </header>

            <div class="content-body">
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
                        <button type="button" class="btn-secondary" onclick="addQuestion()">
                            + Ajouter une question
                        </button>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-primary">Enregistrer le quiz</button>
                        <a href="mes-quiz.php" class="btn-secondary">Annuler</a>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
    let questionCount = 0;

    function addQuestion() {
        const container = document.getElementById('questions-container');
        const questionDiv = document.createElement('div');
        questionDiv.className = 'question-block';
        questionDiv.innerHTML = `
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
                <div class="options-list" id="options-${questionCount}">
                    <div class="option-item">
                        <input type="text" name="questions[${questionCount}][options][]" required>
                        <input type="radio" name="questions[${questionCount}][reponse_correcte]" value="0" required>
                    </div>
                </div>
                <button type="button" class="btn-secondary btn-small" onclick="addOption(${questionCount})">
                    + Ajouter une option
                </button>
            </div>
            <button type="button" class="btn-danger btn-small" onclick="removeQuestion(this)">
                Supprimer la question
            </button>
        `;
        container.appendChild(questionDiv);
        questionCount++;
    }

    function addOption(questionId) {
        const optionsList = document.getElementById(`options-${questionId}`);
        const optionCount = optionsList.children.length;
        const optionDiv = document.createElement('div');
        optionDiv.className = 'option-item';
        optionDiv.innerHTML = `
            <input type="text" name="questions[${questionId}][options][]" required>
            <input type="radio" name="questions[${questionId}][reponse_correcte]" value="${optionCount}" required>
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