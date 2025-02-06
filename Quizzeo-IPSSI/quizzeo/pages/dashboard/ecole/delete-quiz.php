// delete-quiz.php
<?php
session_start();
require_once '../../../includes/auth.php';

// Vérification du rôle
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'ecole') {
    header('Location: ../../login.php');
    exit;
}

if(isset($_GET['id'])) {
    $quizDb = new JsonDatabase('quizzes.json');
    $quiz = $quizDb->findById($_GET['id']);
    
    if($quiz && $quiz['user_id'] === $_SESSION['user']['id']) {
        $quizDb->delete($quiz['id']);
    }
}

header('Location: index.php');
exit;