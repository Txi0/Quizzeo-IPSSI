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
    <style>
        /* Variables */
        :root {
            --primary-color: #8B5CF6;
            --secondary-color: #6B48F3;
            --background-color: #f4f4f9;
            --white: #ffffff;
            --text-color: #333;
            --border-color: #e0e0e0;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Arial', sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
        }

        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 250px;
            background-color: var(--white);
            border-right: 1px solid var(--border-color);
            padding: 20px 0;
        }

        .sidebar .menu {
            list-style-type: none;
        }

        .sidebar .menu li {
            margin-bottom: 5px;
        }

        .sidebar .menu li a {
            display: block;
            padding: 10px 20px;
            color: #6B48F3;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        .sidebar .menu li a:hover,
        .sidebar .menu li a.active {
            background-color: rgba(139, 92, 246, 0.1);
            color: var(--primary-color);
        }

        /* Main Content */
        .main-content {
            flex-grow: 1;
            padding: 30px;
            background-color: var(--background-color);
        }

        .header {
            margin-bottom: 30px;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 15px;
        }

        .header h1 {
            color: var(--primary-color);
            font-size: 24px;
        }

        /* Quiz Form */
        .quiz-form {
            background-color: var(--white);
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .form-section {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border-color);
        }

        .form-section h2 {
            color: var(--primary-color);
            margin-bottom: 20px;
            font-size: 18px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #4a5568;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.2);
        }

        /* Question Block */
        .question-block {
            background-color: var(--background-color);
            border: 1px solid var(--border-color);
            border-radius: 6px;
            padding: 20px;
            margin-bottom: 20px;
            position: relative;
        }

        .options-container {
            margin-top: 15px;
        }

        .option-item {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            gap: 10px;
        }

        .option-item input[type="text"] {
            flex-grow: 1;
        }

        .option-item input[type="radio"] {
            margin: 0 10px;
        }

        /* Buttons */
        .btn-add,
        .btn-secondary,
        .btn-primary,
        .btn-remove {
            padding: 10px 15px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .btn-add {
            background-color: var(--primary-color);
            color: var(--white);
            margin-top: 15px;
        }

        .btn-secondary {
            background-color: var(--secondary-color);
            color: var(--white);
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: var(--white);
        }

        .btn-remove {
            background-color: #dc3545;
            color: var(--white);
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 5px 10px;
            font-size: 12px;
        }

        .form-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .dashboard-container {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                border-right: none;
                border-bottom: 1px solid var(--border-color);
            }

            .sidebar .menu {
                display: flex;
                justify-content: space-around;
            }

            .quiz-form {
                padding: 15px;
            }
        }
    </style>
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