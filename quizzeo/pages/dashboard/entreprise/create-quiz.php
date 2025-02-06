<?php
// pages/dashboard/entreprise/create-quiz.php
session_start();
require_once '../../../includes/auth.php';

// Au début du fichier, après les require
$quizzesFile = 'quizzes.json';
if (!file_exists($quizzesFile)) {
    file_put_contents($quizzesFile, '[]');
}
if (!is_writable($quizzesFile)) {
    die('Erreur : Le fichier quizzes.json n\'est pas accessible en écriture');
}

// Dans la partie traitement POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $quizDb = new JsonDatabase('quizzes.json');
        
        // Validation des données
        if (empty($_POST['titre'])) {
            throw new Exception('Le titre est obligatoire');
        }
        
        if (empty($_POST['questions'])) {
            throw new Exception('Au moins une question est requise');
        }

        $quiz = [
            'id' => uniqid(),
            'user_id' => $_SESSION['user']['id'],
            'titre' => htmlspecialchars($_POST['titre']),
            'description' => htmlspecialchars($_POST['description']),
            'questions' => [],
            'status' => 'en cours d\'écriture',
            'created_at' => date('Y-m-d H:i:s'),
            'points_total' => 0,
            'type_reponses' => $_POST['type_reponses'],
            'objectif' => htmlspecialchars($_POST['objectif'])
        ];

        foreach ($_POST['questions'] as $questionData) {
            // Validation de la question
            if (empty($questionData['texte'])) {
                throw new Exception('Le texte de la question est obligatoire');
            }

            $question = [
                'id' => uniqid(),
                'texte' => htmlspecialchars($questionData['texte']),
                'points' => (int)$questionData['points'],
                'type' => $questionData['type'],
                'competence' => isset($questionData['competence']) ? htmlspecialchars($questionData['competence']) : '',
                'options' => [],
                'reponse_correcte' => null
            ];

            if ($questionData['type'] === 'qcm') {
                if (empty($questionData['options'])) {
                    throw new Exception('Les options sont requises pour une question QCM');
                }
                $question['options'] = array_map('htmlspecialchars', $questionData['options']);
                $question['reponse_correcte'] = $questionData['reponse_correcte'];
            }

            $quiz['points_total'] += $question['points'];
            $quiz['questions'][] = $question;
        }

        if (!$quizDb->insert($quiz)) {
            throw new Exception('Erreur lors de la sauvegarde du quiz');
        }

        $_SESSION['success'] = "Le quiz a été créé avec succès !";
        header('Location: mes-quiz.php');
        exit;

    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
}

// Liste prédéfinie de compétences
$competences = [
    'Communication', 
    'Leadership', 
    'Travail d\'équipe', 
    'Résolution de problèmes', 
    'Adaptabilité', 
    'Gestion du temps', 
    'Créativité', 
    'Analyse',
    'Autre'
];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un quiz - Entreprise</title>
    <link rel="stylesheet" href="../../../assets/css/create-quiz-entreprise.css">
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
                        <input type="text" id="titre" name="titre" required placeholder="Titre du quiz">
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="3" placeholder="Description du quiz"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="objectif">Objectif du quiz</label>
                        <input type="text" id="objectif" name="objectif" placeholder="Objectif de l'évaluation">
                    </div>

                    <div class="form-group">
                        <label for="type_reponses">Type de réponses</label>
                        <select id="type_reponses" name="type_reponses" required>
                            <option value="mixte">Mixte (QCM et réponses libres)</option>
                            <option value="qcm">Uniquement QCM</option>
                            <option value="libre">Uniquement réponses libres</option>
                        </select>
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
    const competences = <?php echo json_encode($competences); ?>;

    function updateQuestionType(selectElement, questionIndex) {
        const optionsContainer = document.getElementById(`options-container-${questionIndex}`);
        const competenceSelect = document.getElementById(`competence-${questionIndex}`);
        const questionType = selectElement.value;

        // Masquer/afficher les options selon le type de question
        optionsContainer.style.display = questionType === 'qcm' ? 'block' : 'none';
    }

    function addQuestion() {
        const container = document.getElementById('questions-container');
        const quizTypeSelect = document.getElementById('type_reponses');
        const currentType = quizTypeSelect.value;

        const questionBlock = document.createElement('div');
        questionBlock.className = 'question-block';
        questionBlock.innerHTML = `
            <div class="form-group">
                <label>Question ${questionCount + 1}</label>
                <input type="text" name="questions[${questionCount}][texte]" required placeholder="Libellé de la question">
            </div>
            <div class="form-group">
                <label>Points</label>
                <input type="number" name="questions[${questionCount}][points]" min="1" value="1" required>
            </div>
            <div class="form-group">
                <label>Compétence évaluée</label>
                <select id="competence-${questionCount}" name="questions[${questionCount}][competence]">
                    ${competences.map(competence => 
                        `<option value="${competence}">${competence}</option>`
                    ).join('')}
                </select>
            </div>
            <div class="form-group">
                <label>Type de question</label>
                <select name="questions[${questionCount}][type]" onchange="updateQuestionType(this, ${questionCount})">
                    <option value="qcm" ${currentType === 'libre' ? 'disabled' : ''}>QCM</option>
                    <option value="libre" ${currentType === 'qcm' ? 'disabled' : ''}>Réponse libre</option>
                </select>
            </div>
            <div id="options-container-${questionCount}" class="options-container">
                <label>Options de réponse</label>
                <div id="options-list-${questionCount}">
                    <div class="option-item">
                        <input type="text" name="questions[${questionCount}][options][]" required placeholder="Libellé de l'option">
                        <input type="radio" name="questions[${questionCount}][reponse_correcte]" value="0" required>
                        <label>Réponse correcte</label>
                        <button type="button" class="btn-remove-option" onclick="this.parentElement.remove()">×</button>
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
        
        // Masquer les options si le type de quiz est "libre"
        if (currentType === 'libre') {
            document.getElementById(`options-container-${questionCount}`).style.display = 'none';
        }
        
        questionCount++;
    }

    function addOption(questionId) {
        const optionsList = document.getElementById(`options-list-${questionId}`);
        const optionCount = optionsList.children.length;
        const optionDiv = document.createElement('div');
        optionDiv.className = 'option-item';
        optionDiv.innerHTML = `
            <input type="text" name="questions[${questionId}][options][]" required placeholder="Libellé de l'option">
            <input type="radio" name="questions[${questionId}][reponse_correcte]" value="${optionCount}">
            <label>Réponse correcte</label>
            <button type="button" class="btn-remove-option" onclick="this.parentElement.remove()">×</button>
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