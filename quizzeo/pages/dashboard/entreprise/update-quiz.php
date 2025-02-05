<?php
session_start();
require_once '../../../includes/auth.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'entreprise') {
    header('Location: ../../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quizId = intval($_POST['id']);
    $titre = htmlspecialchars($_POST['titre']);
    $description = htmlspecialchars($_POST['description']);

    $filePath = '../../../data/quizzes.json';

    if (file_exists($filePath)) {
        $quizzes = json_decode(file_get_contents($filePath), true);

        foreach ($quizzes as &$quiz) {
            if ($quiz['id'] === $quizId) {
                $quiz['titre'] = $titre;
                $quiz['description'] = $description;
                break;
            }
        }

        file_put_contents($filePath, json_encode($quizzes, JSON_PRETTY_PRINT));
        header('Location: mes-questionnaires.php');
        exit;
    } else {
        echo "Le fichier de questionnaires est introuvable.";
    }
} else {
    echo "Requête invalide.";
}
