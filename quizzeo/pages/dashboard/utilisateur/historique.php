<?php
// pages/dashboard/utilisateur/historique.php
session_start();
require_once '../../../includes/auth.php';

// Vérification du rôle
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'utilisateur') {
    header('Location: ../../login.php');
    exit;
}

// Récupération de l'historique des réponses
$responsesDb = new JsonDatabase('responses.json');
$quizDb = new JsonDatabase('quizzes.json');

$userResponses = array_filter($responsesDb->getAll(), function($response) {
    return $response['user_id'] === $_SESSION['user']['id'];
});

// Récupérer les détails des quiz pour chaque réponse
$historique = [];
foreach ($userResponses as $response) {
    $quiz = $quizDb->findById($response['quiz_id']);
    if ($quiz) {
        $historique[] = [
            'quiz' => $quiz,
            'response' => $response
        ];
    }
}

// Trier par date (plus récent en premier)
usort($historique, function($a, $b) {
    return strtotime($b['response']['date']) - strtotime($a['response']['date']);
});
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historique - Quizzeo</title>
    <link rel="stylesheet" href="../../../assets/css/dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <nav class="sidebar">

            <div class="user-info">
                <h3><?php echo htmlspecialchars($_SESSION['user']['nom'] . ' ' . $_SESSION['user']['prenom']); ?></h3>
            </div>
            <ul class="menu">
                <li><a href="index.php">Tableau de bord</a></li>
                <li class="active"><a href="historique.php">Historique</a></li>
                <li><a href="../../../logout.php">Déconnexion</a></li>
            </ul>
        </nav>

        <!-- Main Content -->
        <main class="main-content">
            <header class="dashboard-header">
                <h1>Historique des questionnaires</h1>
            </header>

            <?php if (empty($historique)): ?>
                <div class="empty-state">
                    <p>Vous n'avez pas encore répondu à des questionnaires.</p>
                    <a href="repondre.php" class="btn-primary">Répondre à un questionnaire</a>
                </div>
            <?php else: ?>
                <div class="historique-list">
                    <table class="response-table">
                        <thead>
                            <tr>
                                <th>Questionnaire</th>
                                <th>Type</th>
                                <th>Date de réponse</th>
                                <th>Résultat</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($historique as $item): ?>
                            <tr>
                                <td>
                                    <div class="quiz-info">
                                        <strong><?php echo htmlspecialchars($item['quiz']['titre']); ?></strong>
                                        <?php if (!empty($item['quiz']['description'])): ?>
                                            <span class="quiz-description">
                                                <?php echo htmlspecialchars($item['quiz']['description']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <?php
                                        if (isset($item['quiz']['user_role']) && $item['quiz']['user_role'] === 'ecole') {
                                            echo 'Quiz École';
                                        } else {
                                            echo 'Questionnaire Entreprise';
                                        }
                                    ?>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($item['response']['date'])); ?></td>
                                <td>
                                    <?php if (isset($item['response']['score'])): ?>
                                        <span class="score">
                                            <?php echo $item['response']['score']; ?> / <?php echo $item['quiz']['points_total']; ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge">Complété</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="voir-reponse.php?id=<?php echo $item['response']['id']; ?>" 
                                       class="btn-secondary btn-small">
                                        Voir mes réponses
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>