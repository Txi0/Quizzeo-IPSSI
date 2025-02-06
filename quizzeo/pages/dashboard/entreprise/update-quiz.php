<?php
session_start();
require_once '../../../includes/auth.php';
require_once '../../../includes/database.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'entreprise') {
    header('Location: ../../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quizDb = new JsonDatabase('quizzes.json');
    $quizId = $_POST['id'];

    $quiz = [
        'titre' => $_POST['titre'],
        'description' => $_POST['description'],
        'questions' => $_POST['questions'],
    ];

    if ($quizDb->update($quizId, $quiz)) {
        header('Location: mes-quiz.php?updated=1');
        exit;
    } else {
        echo "Erreur lors de la mise à jour du quiz.";
    }
}
