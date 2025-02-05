<?php
// pages/dashboard/entreprise/create-quiz.php
session_start();
require_once '../../../includes/auth.php';

// Vérification du rôle
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'entreprise') {
    header('Location: ../../login.php');
    exit;
}

// Types de questions disponibles pour les entreprises
$questionTypes = [
    'rating' => 'Note de satisfaction (1-5)',
    'qcm' => 'Choix multiples',
    'text' => 'Réponse libre'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quizDb = new JsonDatabase('quizzes.json');
    
    $quiz = [
        'id' => uniqid(),
        'user_id' => $_SESSION['user']['id'],
        'titre' => $_POST['titre'],
        'description' => $_POST['description'],
        'type' => 'satisfaction',
        'questions' => [],
        'status' => 'en cours d\'écriture',
        'nb_reponses' => 0,
        'created_at' => date('Y-m-d H:i:s')
    ];

    foreach ($_POST['questions'] as $questionData) {
        $question = [
            'id' => uniqid(),
            'texte' => $questionData['texte'],
            'type' => $questionData['type']
        ];

        // Gestion spécifique selon le type de question
        switch ($questionData['type']) {
            case 'rating':
                $question['max_rating'] = 5;
                break;
            case 'qcm':
                $question['options'] = $questionData['options'] ?? [];
                break;
            case 'text':
                $question['placeholder'] = $questionData['placeholder'] ?? '';
                break;
        }

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
    <title>Créer un questionnaire - Quizzeo</title>
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
                <h3><?php echo htmlspecialchars($_SESSION['user']['nom_entreprise']); ?></h3>
            </div>
            <ul class="menu">
                <li><a href="index.php">Tableau de bord</a></li>
                <li class="active"><a href="create-quiz.php">Créer un questionnaire</a></li>
                <li><a href="mes-quiz.php">Mes questionnaires</a></li>
                <li><a href="analyse.php">Analyses</a></li>
                <li><a href="../../../logout.php">Déconnexion</a></li>
            </ul>
        </nav>

        <!-- Main Content -->
        <main class="main-content">
            <header class="dashboard-header">
                <h1>Créer un nouveau questionnaire</h1>
            </header>

            <div class="content-body">
                <form method="POST" action="" id="quizForm" class="quiz-form">
                    <!-- Informations générales -->
                    <div class="form-section">
                        <h2>Informations générales</h2>
                        <div class="form-group">
                            <label for="titre">Titre du questionnaire *</label>
                            <input type="text" id="titre" name="titre" required>
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" rows="3"
                                placeholder="Décrivez l'objectif de ce questionnaire..."></textarea>
                        </div>
                    </div>

                    <!-- Questions -->
                    <div class="form-section">
                        <h2>Questions</h2>
                        <div id="questions-container"></div>
                        <button type="button" class="btn-secondary" onclick="addQuestion()">
                            + Ajouter une question
                        </button>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-primary">Enregistrer le questionnaire</button>
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
                <input type="text" name="questions[${questionCount}][texte]" required
                       placeholder="Saisissez votre question...">
            </div>
            <div class="form-group">
                <label>Type de réponse</label>
                <select name="questions[${questionCount}][type]" onchange="handleQuestionType(this, ${questionCount})">
                    <option value="rating">Note de satisfaction (1-5)</option>
                    <option value="qcm">Choix multiples</option>
                    <option value="text">Réponse libre</option>
                </select>
            </div>
            <div id="options-container-${questionCount}" class="options-container" style="display: none;">
                <!-- Les options seront ajoutées ici dynamiquement -->
            </div>
            <button type="button" class="btn-danger btn-small" onclick="removeQuestion(this)">
                Supprimer la question
            </button>
        `;
        container.appendChild(questionDiv);
        questionCount++;
    }

    function handleQuestionType(select, questionId) {
        const optionsContainer = document.getElementById(`options-container-${questionId}`);
        optionsContainer.innerHTML = '';

        switch(select.value) {
            case 'qcm':
                optionsContainer.style.display = 'block';
                optionsContainer.innerHTML = `
                    <div class="form-group">
                        <label>Options de réponse</label>
                        <div class="options-list" id="options-list-${questionId}">
                            <input type="text" name="questions[${questionId}][options][]" 
                                   placeholder="Option 1" required>
                        </div>
                        <button type="button" class="btn-secondary btn-small" 
                                onclick="addOption(${questionId})">
                            + Ajouter une option
                        </button>
                    </div>
                `;
                break;
            case 'text':
                optionsContainer.style.display = 'block';
                optionsContainer.innerHTML = `
                    <div class="form-group">
                        <label>Texte d'aide (placeholder)</label>
                        <input type="text" name="questions[${questionId}][placeholder]"
                               placeholder="Ex: Donnez votre avis...">
                    </div>
                `;
                break;
            default:
                optionsContainer.style.display = 'none';
        }
    }

    function addOption(questionId) {
        const optionsList = document.getElementById(`options-list-${questionId}`);
        const newOption = document.createElement('input');
        newOption.type = 'text';
        newOption.name = `questions[${questionId}][options][]`;
        newOption.placeholder = `Option ${optionsList.children.length + 1}`;
        newOption.required = true;
        optionsList.appendChild(newOption);
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