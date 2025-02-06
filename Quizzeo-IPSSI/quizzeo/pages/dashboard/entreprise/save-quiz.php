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

    $quiz = [
        'id' => uniqid(),
        'user_id' => $_SESSION['user']['id'],
        'titre' => $_POST['titre'],
        'description' => $_POST['description'],
        'status' => 'en cours d\'écriture',
        'nb_reponses' => 0,
        'questions' => [],
        'created_at' => date('Y-m-d H:i:s')
    ];

    foreach ($_POST['questions'] as $questionData) {
        $question = [
            'id' => uniqid(),
            'texte' => $questionData['texte'],
            'type' => $questionData['type'],
            'options' => $questionData['options'] ?? [],
        ];
        $quiz['questions'][] = $question;
    }

    if ($quizDb->insert($quiz)) {
        header('Location: mes-quiz.php?success=1');
        exit;
    } else {
        echo "Erreur lors de l'enregistrement du quiz.";
    }
}
