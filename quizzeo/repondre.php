<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/auth.php';

try {
    // Vérification du token
    $token = $_GET['token'] ?? '';
    if (empty($token)) {
        throw new Exception("Lien invalide ou expiré");
    }

    // Initialisation de la base de données
    if (!class_exists('JsonDatabase')) {
        throw new Exception("Erreur de configuration: JsonDatabase non disponible");
    }

    $quizDb = new JsonDatabase('quizzes.json');
    $quiz = null;

    // Recherche du quiz correspondant au token
    $allQuizzes = $quizDb->getAll();
    foreach ($allQuizzes as $q) {
        if (isset($q['share_token']) && $q['share_token'] === $token && $q['status'] === 'lancé') {
            $quiz = $q;
            break;
        }
    }

    if (!$quiz) {
        throw new Exception("Quiz non trouvé ou non disponible");
    }

    // Traitement du formulaire soumis
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reponses'])) {
        $responsesDb = new JsonDatabase('responses.json');
        
        // Calcul du score
        $score = 0;
        $maxScore = 0;
        
        foreach ($quiz['questions'] as $index => $question) {
            $maxScore += $question['points'];
            $reponseUtilisateur = $_POST['reponses'][$index] ?? null;
            
            if ($reponseUtilisateur !== null && isset($question['reponse_correcte'])) {
                if ((int)$reponseUtilisateur === (int)$question['reponse_correcte']) {
                    $score += $question['points'];
                }
            }
        }
        
        $pourcentage = ($maxScore > 0) ? round(($score / $maxScore) * 100, 1) : 0;
        
        // Enregistrement de la réponse
        $response = [
            'id' => uniqid(),
            'quiz_id' => $quiz['id'],
            'user_id' => $_SESSION['user']['id'],
            'user_name' => $_SESSION['user']['nom'] . ' ' . $_SESSION['user']['prenom'],
            'reponses' => $_POST['reponses'],
            'score' => $score,
            'score_max' => $maxScore,
            'pourcentage' => $pourcentage,
            'date' => date('Y-m-d H:i:s')
        ];

        if (!$responsesDb->insert($response)) {
            throw new Exception("Erreur lors de l'enregistrement des réponses");
        }

        // Mise à jour du compteur de réponses du quiz
        $quiz['nb_reponses'] = ($quiz['nb_reponses'] ?? 0) + 1;
        $quizDb->update($quiz['id'], ['nb_reponses' => $quiz['nb_reponses']]);

        // Stockage du résultat dans la session
        $_SESSION['last_quiz_result'] = [
            'quiz_titre' => $quiz['titre'],
            'score' => $score,
            'score_max' => $maxScore,
            'pourcentage' => $pourcentage
        ];

        // Redirection vers le dashboard avec le résultat
        header('Location: pages/dashboard/utilisateur/index.php?quiz_complete=1');
        exit;
    }

} catch (Exception $e) {
    die("Erreur : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($quiz['titre']); ?></title>
    <style>
        :root {
            --primary: #6C5CE7;
            --secondary: #a29bfe;
            --bg-light: #F8F7FF;
            --text: #2D3436;
            --border: #E2E8F0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', system-ui, sans-serif;
        }

        body {
            background-color: var(--bg-light);
            color: var(--text);
            line-height: 1.6;
            padding: 2rem;
        }

        .quiz-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .quiz-title {
            color: var(--primary);
            text-align: center;
            margin-bottom: 2rem;
            font-size: 1.8rem;
        }

        .question {
            background: var(--bg-light);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border-radius: 8px;
            border: 1px solid var(--border);
        }

        .question-text {
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--text);
        }

        .options {
            display: flex;
            flex-direction: column;
            gap: 0.8rem;
        }

        .option {
            display: flex;
            align-items: center;
            padding: 1rem;
            background: white;
            border: 1px solid var(--border);
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .option:hover {
            border-color: var(--primary);
            background: var(--bg-light);
        }

        .option input[type="radio"] {
            margin-right: 1rem;
        }

        .submit-button {
            width: 100%;
            padding: 1rem;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            cursor: pointer;
            transition: background 0.3s ease;
            margin-top: 2rem;
        }

        .submit-button:hover {
            background: #5849e0;
        }
    </style>
</head>
<body>
    <div class="quiz-container">
        <h1 class="quiz-title"><?php echo htmlspecialchars($quiz['titre']); ?></h1>
        
        <form method="POST" action="" id="quizForm">
            <?php foreach ($quiz['questions'] as $index => $question): ?>
                <div class="question">
                    <div class="question-text">
                        <?php echo htmlspecialchars($question['texte']); ?>
                    </div>
                    <div class="options">
                        <?php foreach ($question['options'] as $optionIndex => $option): ?>
                            <label class="option">
                                <input type="radio" 
                                       name="reponses[<?php echo $index; ?>]" 
                                       value="<?php echo $optionIndex; ?>" 
                                       required>
                                <?php echo htmlspecialchars($option); ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <button type="submit" class="submit-button">Valider mes réponses</button>
        </form>
    </div>

    <script>
    document.getElementById('quizForm').addEventListener('submit', function(e) {
        const questions = document.querySelectorAll('.question');
        let allAnswered = true;

        questions.forEach((question, index) => {
            const radios = question.querySelectorAll('input[type="radio"]:checked');
            if (radios.length === 0) {
                allAnswered = false;
            }
        });

        if (!allAnswered) {
            e.preventDefault();
            alert('Veuillez répondre à toutes les questions.');
        }
    });
    </script>
</body>
</html>