// update-quiz-status.php
<?php
session_start();
require_once '../../../includes/auth.php';

// Vérification du rôle
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'ecole') {
    header('Location: ../../login.php');
    exit;
}

if(isset($_GET['id']) && isset($_GET['status'])) {
    $quizDb = new JsonDatabase('quizzes.json');
    $quiz = $quizDb->findById($_GET['id']);
    
    if($quiz && $quiz['user_id'] === $_SESSION['user']['id']) {
        // Mettre à jour le statut
        $quiz['status'] = $_GET['status'];
        
        // Générer un token de partage si le quiz est lancé
        if($_GET['status'] === 'lancé' && !isset($quiz['share_token'])) {
            $quiz['share_token'] = bin2hex(random_bytes(16));
        }
        
        $quizDb->update($quiz['id'], $quiz);
    }
}

header('Location: index.php');
exit;