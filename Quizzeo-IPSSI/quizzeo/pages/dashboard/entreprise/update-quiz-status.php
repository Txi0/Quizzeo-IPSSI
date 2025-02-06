<?php
session_start();
require_once '../../../includes/auth.php';
require_once '../../../includes/database.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'entreprise') {
    header('Location: ../../login.php');
    exit;
}

if (isset($_GET['id']) && isset($_GET['status'])) {
    $quizDb = new JsonDatabase('quizzes.json');
    $quizId = $_GET['id'];
    $status = $_GET['status'];

    if ($quizDb->update($quizId, ['status' => $status])) {
        header('Location: mes-quiz.php?status_updated=1');
        exit;
    } else {
        echo "Erreur lors de la mise à jour du statut du quiz.";
    }
}
