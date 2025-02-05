<?php
// repondre.php
session_start();
require_once __DIR__ . '/includes/auth.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: pages/login.php');
    exit;
}

// Récupérer l'ID du quiz via le paramètre GET
$quizId = $_GET['id'] ?? '';
if (empty($quizId)) {
    die("Lien invalide ou expiré");
}

// Récupérer le quiz par son ID
$quizDb = new JsonDatabase('data/quizzes.json'); // Assurez-vous du chemin correct
$quiz = $quizDb->findById($quizId); // Cette méthode doit parcourir le JSON et retourner le quiz correspondant
if (!$quiz) {
    die("Ce quiz n'existe pas");
}

// Vérifier que le quiz est lancé
if ($quiz['status'] !== 'lancé') {
    die("Ce questionnaire n'est pas disponible");
}

// Vérifier si l'utilisateur a déjà répondu
$responsesDb = new JsonDatabase('data/responses.json'); // Assurez-vous du chemin pour responses.json
$userResponses = array_filter($responsesDb->getAll(), function($response) use ($quiz) {
    return $response['quiz_id'] === $quiz['id'] &&
           $response['user_id'] === $_SESSION['user']['id'];
});

if (!empty($userResponses)) {
    die("Vous avez déjà répondu à ce questionnaire");
}

// Traitement de la soumission des réponses
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = [
        'id' => uniqid(),
        'quiz_id' => $quiz['id'],
        'user_id' => $_SESSION['user']['id'],
        'user_name' => $_SESSION['user']['nom'] . ' ' . $_SESSION['user']['prenom'],
        'reponses' => $_POST['reponses'],
        'date' => date('Y-m-d H:i:s')
    ];

    // Calcul du score pour les quiz d'école (si applicable)
    if ($quiz['user_role'] === 'ecole') {
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

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($quiz['titre']); ?> - Quizzeo</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="quiz-container">
        <h1><?php echo htmlspecialchars($quiz['titre']); ?></h1>
        
        <?php if (!empty($quiz['description'])): ?>
            <p class="quiz-description"><?php echo nl2br(htmlspecialchars($quiz['description'])); ?></p>
        <?php endif; ?>

        <form method="POST" action="" class="quiz-form">
            <?php foreach ($quiz['questions'] as $index => $question): ?>
                <div class="question-block">
                    <h3><?php echo htmlspecialchars($question['texte']); ?></h3>
                    
                    <?php switch ($question['type']):
                        case 'text': ?>
                            <div class="form-group">
                                <textarea name="reponses[<?php echo $index; ?>]" 
                                          rows="3" 
                                          placeholder="<?php echo htmlspecialchars($question['placeholder'] ?? 'Votre réponse...'); ?>"
                                          required></textarea>
                            </div>
                        <?php break; ?>

                        <?php case 'qcm': ?>
                            <div class="options-group">
                                <?php foreach ($question['options'] as $optionIndex => $option): ?>
                                    <div class="option">
                                        <input type="radio" 
                                               name="reponses[<?php echo $index; ?>]" 
                                               id="q<?php echo $index; ?>o<?php echo $optionIndex; ?>"
                                               value="<?php echo $optionIndex; ?>"
                                               required>
                                        <label for="q<?php echo $index; ?>o<?php echo $optionIndex; ?>">
                                            <?php echo htmlspecialchars($option); ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php break; ?>

                        <?php case 'rating': ?>
                            <div class="rating-group">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <div class="rating-option">
                                        <input type="radio" 
                                               name="reponses[<?php echo $index; ?>]" 
                                               id="q<?php echo $index; ?>r<?php echo $i; ?>"
                                               value="<?php echo $i; ?>"
                                               required>
                                        <label for="q<?php echo $index; ?>r<?php echo $i; ?>">
                                            <?php echo $i; ?>
                                        </label>
                                    </div>
                                <?php endfor; ?>
                            </div>
                        <?php break; ?>
                    <?php endswitch; ?>
                </div>
            <?php endforeach; ?>

            <button type="submit" class="btn-submit">Envoyer mes réponses</button>
        </form>
    </div>
</body>
</html>
