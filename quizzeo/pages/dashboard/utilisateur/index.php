<?php
// pages/dashboard/utilisateur/index.php
session_start();
require_once '../../../includes/auth.php';

// Vérification du rôle
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'utilisateur') {
    header('Location: ../../login.php');
    exit;
}

// Récupération des quiz déjà répondus par l'utilisateur
$responsesDb = new JsonDatabase('responses.json');
$allResponses = $responsesDb->getAll();
$userResponses = array_filter($allResponses, function($response) {
    return $response['user_id'] === $_SESSION['user']['id'];
});

// Récupérer les détails des quiz répondus
$quizDb = new JsonDatabase('quizzes.json');
$quizzes = $quizDb->getAll();
$answeredQuizzes = [];
foreach ($userResponses as $response) {
    foreach ($quizzes as $quiz) {
        if ($quiz['id'] === $response['quiz_id']) {
            $answeredQuizzes[] = [
                'quiz' => $quiz,
                'response' => $response,
                'date' => $response['date']
            ];
        }
    }
}

// Trier par date de réponse (plus récent en premier)
usort($answeredQuizzes, function($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - Quizzeo</title>
    <link rel="stylesheet" href="../../../assets/css/dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <nav class="sidebar">
            <div class="logo">
                <img src="../../../assets/images/logo.png" alt="Quizzeo">
            </div>
            <div class="user-info">
                <h3><?php echo htmlspecialchars($_SESSION['user']['nom'] . ' ' . $_SESSION['user']['prenom']); ?></h3>
            </div>
            <ul class="menu">
                <li class="active"><a href="index.php">Tableau de bord</a></li>
                <li><a href="repondre.php">Répondre à un quiz</a></li>
                <li><a href="historique.php">Historique</a></li>
                <li><a href="../../../logout.php">Déconnexion</a></li>
            </ul>
        </nav>

        <!-- Main Content -->
        <main class="main-content">
            <header class="dashboard-header">
                <h1>Tableau de bord</h1>
            </header>

            <!-- Section pour répondre à un nouveau quiz -->
            <section class="answer-section">
                <h2>Répondre à un questionnaire</h2>
                <div class="token-input-container">
                    <form action="repondre.php" method="GET" class="token-form">
                        <input type="text" 
                               name="token" 
                               placeholder="Collez le lien ou le token du questionnaire ici"
                               class="token-input">
                        <button type="submit" class="btn-primary">Accéder au questionnaire</button>
                    </form>
                </div>
            </section>

            <!-- Historique récent -->
            <section class="recent-answers">
                <h2>Questionnaires récemment complétés</h2>
                <div class="quiz-grid">
                    <?php foreach (array_slice($answeredQuizzes, 0, 6) as $item): ?>
                    <div class="quiz-card">
                        <div class="quiz-header">
                            <h3><?php echo htmlspecialchars($item['quiz']['titre']); ?></h3>
                            <?php if ($item['quiz']['user_role'] === 'ecole'): ?>
                                <span class="quiz-type">Quiz École</span>
                            <?php else: ?>
                                <span class="quiz-type">Questionnaire Entreprise</span>
                            <?php endif; ?>
                        </div>
                        <div class="quiz-body">
                            <p>Complété le : <?php echo date('d/m/Y H:i', strtotime($item['date'])); ?></p>
                            <?php if (isset($item['response']['score'])): ?>
                                <p class="quiz-score">Score : <?php echo $item['response']['score']; ?>/<?php echo $item['quiz']['points_total']; ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="quiz-footer">
                            <a href="voir-reponse.php?id=<?php echo $item['response']['id']; ?>" class="btn-secondary btn-small">
                                Voir mes réponses
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>
        </main>
    </div>
</body>
</html>