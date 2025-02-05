<?php
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
    <link rel="stylesheet" href="../../../assets/css/base.css">
    <link rel="stylesheet" href="../../../assets/css/create-quiz.css">
</head>
<body>
    <div class="dashboard-container">
        <?php include '../../../templates/sidebar.php'; ?>

        <div class="main-content">
            <div class="dashboard-header">
                <h1>Créer un nouveau questionnaire</h1>
            </div>

            <form class="quiz-form" method="POST" action="save-quiz.php">
                <div class="form-section">
                    <h2>Informations générales</h2>
                    <div class="form-group">
                        <label for="titre">Titre du questionnaire *</label>
                        <input type="text" id="titre" name="titre" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" placeholder="Décrivez l'objectif de ce questionnaire..."></textarea>
                    </div>
                </div>

                <div class="form-section">
                    <h2>Questions</h2>
                    <div id="questions-container"></div>
                    <button type="button" class="btn-secondary" onclick="addQuestion()">+ Ajouter une question</button>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">Enregistrer le questionnaire</button>
                    <a href="index.php" class="btn-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>

    <script src="../../../assets/js/main.js"></script>
</body>
</html>
