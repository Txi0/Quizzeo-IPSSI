<?php
session_start();
require_once '../../../includes/auth.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'entreprise') {
    header('Location: ../../login.php');
    exit;
}

if (isset($_GET['id'])) {
    $quizId = intval($_GET['id']);
    $filePath = '../../../data/quizzes.json';

    if (file_exists($filePath)) {
        $quizzes = json_decode(file_get_contents($filePath), true);

        // Supprimer le quiz avec l'ID correspondant
        $quizzes = array_filter($quizzes, function($quiz) use ($quizId) {
            return $quiz['id'] != $quizId;
        });

        // Réindexer et sauvegarder
        file_put_contents($filePath, json_encode(array_values($quizzes), JSON_PRETTY_PRINT));
    }

    header('Location: mes-questionnaires.php');
    exit;
} else {
    echo "ID de quiz non spécifié.";
}
?>
