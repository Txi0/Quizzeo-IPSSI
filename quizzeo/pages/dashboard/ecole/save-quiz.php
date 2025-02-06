<?php
session_start();
require_once '../../../includes/auth.php';
require_once '../../../includes/database.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'ecole') {
    header('Location: ../../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quizDb = new JsonDatabase('quizzes.json');

    $quiz = [
        'id' => uniqid(),
        'user_id' => $_SESSION['user']['id'],
        'titre' => $_POST['titre'],
        'description' => $_POST['description'],
        'questions' => $_POST['questions'],
        'status' => 'en cours d\'écriture',
        'created_at' => date('Y-m-d H:i:s'),
    ];

    $quizDb->insert($quiz);
    header('Location: mes-quiz.php?success=1');
    exit;
}
?>
