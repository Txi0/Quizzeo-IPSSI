<?php
session_start();
require_once '../../../includes/auth.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'entreprise') {
    header('Location: ../../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = htmlspecialchars($_POST['titre']);
    $description = htmlspecialchars($_POST['description']);
    
    $filePath = '../../../data/quizzes.json';
    $quizzes = file_exists($filePath) ? json_decode(file_get_contents($filePath), true) : [];

    // Créer un nouvel ID unique
    $newId = !empty($quizzes) ? max(array_column($quizzes, 'id')) + 1 : 1;

    // Créer le nouveau quiz
    $newQuiz = [
        'id' => $newId,
        'titre' => $titre,
        'description' => $description,
        'status' => 'Inactif',
        'reponses' => 0
    ];

    $quizzes[] = $newQuiz;

    // Sauvegarder dans le fichier JSON
    file_put_contents($filePath, json_encode($quizzes, JSON_PRETTY_PRINT));

    header('Location: mes-questionnaires.php');
    exit;
}
?>
