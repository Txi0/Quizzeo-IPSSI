<?php
// repondre.php
session_start();
require_once 'includes/auth.php';

// Vérification qu'un token est fourni
$token = $_GET['token'] ?? '';
if (empty($token)) {
    die("Lien invalide ou expiré");
}

// Récupération du quiz
$quizDb = new JsonDatabase('quizzes.json');
$allQuizzes = $quizDb->getAll();
$quiz = null;

// Chercher le quiz correspondant au token
foreach ($allQuizzes as $q) {
    if (isset($q['share_token']) && $q['share_token'] === $token && $q['status'] === 'lancé') {
        $quiz = $q;
        break;
    }
}

// Vérifier si le quiz existe
if (!$quiz) {
    die("Ce quiz n'est pas disponible actuellement");
}

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
    // Sauvegarder l'URL pour rediriger après la connexion
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: pages/login.php');
    exit;
}

// Traitement de la soumission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reponses'])) {
    $responsesDb = new JsonDatabase('responses.json');
    
    // Créer la réponse
    $response = [
        'id' => uniqid(),
        'quiz_id' => $quiz['id'],
        'user_id' => $_SESSION['user']['id'],
        'user_name' => $_SESSION['user']['nom'] . ' ' . $_SESSION['user']['prenom'],
        'reponses' => $_POST['reponses'],
        'date' => date('Y-m-d H:i:s')
    ];

    // Sauvegarder la réponse
    if ($responsesDb->insert($response)) {
        // Mettre à jour le nombre de réponses du quiz
        $quiz['nb_reponses'] = ($quiz['nb_reponses'] ?? 0) + 1;
        $quizDb->update($quiz['id'], ['nb_reponses' => $quiz['nb_reponses']]);
        
        header('Location: http://localhost/Quizzeo-IPSSI/quizzeo/pages/dashboard/utilisateur/index.php');
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
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f3f4f6;
        }

        .quiz-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        h1 {
            color: #1f2937;
            margin-bottom: 20px;
        }

        .question {
            background: #f9fafb;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 6px;
            border: 1px solid #e5e7eb;
        }

        .question-text {
            font-weight: bold;
            margin-bottom: 15px;
        }

        .options {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .option {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        input[type="radio"] {
            margin: 0;
        }

        label {
            margin: 0;
            cursor: pointer;
        }

        .submit-button {
            background-color: #8b5cf6;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            margin-top: 20px;
        }

        .submit-button:hover {
            background-color: #7c3aed;
        }
    </style>
</head>
<body>
    <div class="quiz-container">
        <h1><?php echo htmlspecialchars($quiz['titre']); ?></h1>
        
        <form method="POST" action="">
            <?php foreach ($quiz['questions'] as $index => $question): ?>
                <div class="question">
                    <div class="question-text">
                        <?php echo htmlspecialchars($question['texte']); ?>
                    </div>
                    
                    <div class="options">
                        <?php if (isset($question['options'])): ?>
                            <?php foreach ($question['options'] as $optionIndex => $option): ?>
                                <div class="option">
                                    <input type="radio" 
                                           id="q<?php echo $index; ?>_<?php echo $optionIndex; ?>"
                                           name="reponses[<?php echo $index; ?>]"
                                           value="<?php echo $optionIndex; ?>"
                                           required>
                                    <label for="q<?php echo $index; ?>_<?php echo $optionIndex; ?>">
                                        <?php echo htmlspecialchars($option); ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <button type="submit" class="submit-button">Soumettre mes réponses</button>
        </form>
    </div>
</body>
</html>