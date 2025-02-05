<?php
// pages/dashboard/entreprise/create-quiz.php
session_start();
require_once '../../../includes/auth.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'entreprise') {
    header('Location: ../../login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un questionnaire - Quizzeo</title>
    <link rel="stylesheet" href="../../../assets/css/dashboard-entreprise.css">
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
            <ul class="sidebar-menu">
                <li><a href="index.php">Tableau de bord</a></li>
                <li><a href="create-quiz.php" class="active">Créer un questionnaire</a></li>
                <li><a href="mes-questionnaires.php">Mes questionnaires</a></li>
                <li><a href="analyses.php">Analyses</a></li>
                <li><a href="../../../logout.php">Déconnexion</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1>Créer un nouveau questionnaire</h1>
            </div>

            <form class="quiz-form" method="POST" action="save-quiz.php">
                <!-- Informations générales -->
                <section>
                    <h2>Informations générales</h2>
                    <div class="form-group">
                        <label for="titre">Titre du questionnaire *</label>
                        <input type="text" id="titre" name="titre" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" 
                                placeholder="Décrivez l'objectif de ce questionnaire..."></textarea>
                    </div>
                </section>

                <!-- Questions -->
                <section class="questions-section">
                    <h2>Questions</h2>
                    <div id="questions-container"></div>
                    
                    <button type="button" class="btn btn-secondary" onclick="addQuestion()">
                        + Ajouter une question
                    </button>
                </section>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Enregistrer le questionnaire</button>
                    <a href="index.php" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>
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
                <label>Type de réponse</label>
                <select name="questions[${questionCount}][type]" onchange="handleQuestionType(this, ${questionCount})">
                    <option value="rating">Note de satisfaction (1-5)</option>
                    <option value="qcm">Choix multiples</option>
                    <option value="text">Réponse libre</option>
                </select>
            </div>
            <div id="options-container-${questionCount}"></div>
            <button type="button" class="btn btn-secondary" onclick="removeQuestion(this)">
                Supprimer la question
            </button>
        `;
        container.appendChild(questionBlock);
        questionCount++;
    }

    function handleQuestionType(select, questionIndex) {
        const optionsContainer = document.getElementById(`options-container-${questionIndex}`);
        optionsContainer.innerHTML = '';

        if (select.value === 'qcm') {
            addOptionField(optionsContainer, questionIndex);
        }
    }

    function addOptionField(container, questionIndex) {
        const optionBlock = document.createElement('div');
        optionBlock.className = 'form-group';
        optionBlock.innerHTML = `
            <input type="text" name="questions[${questionIndex}][options][]" placeholder="Option" required>
            <button type="button" class="btn btn-secondary" onclick="removeOption(this)">
                Supprimer l'option
            </button>
        `;
        container.appendChild(optionBlock);

        const addMoreButton = container.querySelector('.add-more-options');
        if (!addMoreButton) {
            const addButton = document.createElement('button');
            addButton.type = 'button';
            addButton.className = 'btn btn-secondary add-more-options';
            addButton.textContent = '+ Ajouter une option';
            addButton.onclick = () => addOptionField(container, questionIndex);
            container.appendChild(addButton);
        }
    }

    function removeOption(button) {
        button.parentElement.remove();
    }

    function removeQuestion(button) {
        button.parentElement.remove();
        questionCount--;
        updateQuestionLabels();
    }

    function updateQuestionLabels() {
        const questions = document.querySelectorAll('.question-block');
        questions.forEach((question, index) => {
            question.querySelector('label').textContent = `Question ${index + 1}`;
        });
    }
    </script>
</body>
</html>
