<?php
// repondre.php
session_start();
require_once __DIR__ . '/../../../includes/auth.php';


// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: pages/login.php');
    exit;
}

// Récupérer l'ID du quiz
$quizId = $_GET['id'] ?? '';
if (empty($quizId)) {
    die("Lien invalide ou expiré");
}

// Récupérer le quiz
$quizDb = new JsonDatabase('quizzes.json');
$quiz = $quizDb->findById($quizId);

// Vérifier si le quiz existe et est actif
if (!$quiz || $quiz['status'] !== 'lancé') {
    die("Ce questionnaire n'est pas disponible");
}

// Vérifier si l'utilisateur a déjà répondu
$responsesDb = new JsonDatabase('responses.json');
$userResponses = array_filter($responsesDb->getAll(), function($response) use ($quizId) {
    return $response['quiz_id'] === $quizId && 
           $response['user_id'] === $_SESSION['user']['id'];
});

if (!empty($userResponses)) {
    die("Vous avez déjà répondu à ce questionnaire");
}

// Traitement de la soumission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = [
        'id' => uniqid(),
        'quiz_id' => $quiz['id'],
        'user_id' => $_SESSION['user']['id'],
        'user_name' => $_SESSION['user']['nom'] . ' ' . $_SESSION['user']['prenom'],
        'reponses' => $_POST['reponses'],
        'date' => date('Y-m-d H:i:s')
    ];

    // Calcul du score pour les quiz d'école
    if (isset($quiz['user_role']) && $quiz['user_role'] === 'ecole') {
        $score = 0;
        foreach ($quiz['questions'] as $index => $question) {
            if (isset($_POST['reponses'][$index]) && 
                $_POST['reponses'][$index] === $question['reponse_correcte']) {
                $score += $question['points'];
            }
        }
        $response['score'] = $score;
    }

    if ($responsesDb->insert($response)) {
        // Mettre à jour le nombre de réponses du quiz
        $quiz['nb_reponses'] = ($quiz['nb_reponses'] ?? 0) + 1;
        $quizDb->update($quiz['id'], ['nb_reponses' => $quiz['nb_reponses']]);
        
        header('Location: pages/dashboard/utilisateur/index.php?success=1');
        exit;
    }
}
?>

<!-- Reste du code HTML pour l'affichage du formulaire -->