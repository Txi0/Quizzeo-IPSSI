<?php
// pages/dashboard/ecole/resultats.php
session_start();
require_once '../../../includes/auth.php';

// Vérification du rôle
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'ecole') {
    header('Location: ../../login.php');
    exit;
}

// Récupération des quiz
$quizDb = new JsonDatabase('quizzes.json');
$responsesDb = new JsonDatabase('responses.json');

// Si un ID de quiz spécifique est demandé
$quizId = $_GET['id'] ?? null;

if ($quizId) {
    // Récupérer le quiz spécifique et ses réponses
    $quiz = $quizDb->findById($quizId);
    if (!$quiz || $quiz['user_id'] !== $_SESSION['user']['id']) {
        header('Location: resultats.php');
        exit;
    }

    // Récupérer toutes les réponses pour ce quiz
    $allResponses = $responsesDb->getAll();
    $quizResponses = array_filter($allResponses, function($response) use ($quizId) {
        return $response['quiz_id'] === $quizId;
    });
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Résultats - École - Quizzeo</title>
    <link rel="stylesheet" href="../../../assets/css/base.css">
    <link rel="stylesheet" href="../../../assets/css/dashboard-entreprise.css">

</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <nav class="sidebar">
            <div class="logo">
                <img src="../../../assets/images/logo.png" alt="Quizzeo">
            </div>
            <div class="user-info">
                <h3><?php echo htmlspecialchars($_SESSION['user']['nom_etablissement']); ?></h3>
            </div>
            <ul class="menu">
                <li><a href="index.php">Tableau de bord</a></li>
                <li><a href="create-quiz.php">Créer un quiz</a></li>
                <li><a href="mes-quiz.php">Mes quiz</a></li>
                <li class="active"><a href="resultats.php">Résultats</a></li>
                <li><a href="../../../logout.php">Déconnexion</a></li>
            </ul>
        </nav>

        <!-- Main Content -->
        <main class="main-content">
            <?php if ($quizId && isset($quiz)): ?>
                <!-- Affichage des résultats d'un quiz spécifique -->
                <header class="dashboard-header">
                    <div>
                        <h1><?php echo htmlspecialchars($quiz['titre']); ?></h1>
                        <p class="subtitle">Résultats détaillés</p>
                    </div>
                    <a href="resultats.php" class="btn-secondary">Retour aux résultats</a>
                </header>

                <div class="results-container">
                    <!-- Statistiques générales -->
                    <div class="stats-summary">
                        <div class="stat-card">
                            <h3>Nombre de réponses</h3>
                            <p class="stat-number"><?php echo count($quizResponses); ?></p>
                        </div>
                        <div class="stat-card">
                            <h3>Moyenne de la classe</h3>
                            <p class="stat-number">
                                <?php
                                    $totalPoints = 0;
                                    foreach ($quizResponses as $response) {
                                        $totalPoints += $response['score'] ?? 0;
                                    }
                                    echo count($quizResponses) > 0 
                                        ? number_format($totalPoints / count($quizResponses), 2) . ' / ' . $quiz['points_total']
                                        : 'N/A';
                                ?>
                            </p>
                        </div>
                    </div>

                    <!-- Liste des réponses -->
                    <div class="results-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Étudiant</th>
                                    <th>Score</th>
                                    <th>Date de soumission</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($quizResponses as $response): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($response['user_name'] ?? 'Anonyme'); ?></td>
                                    <td>
                                        <?php 
                                            echo isset($response['score']) 
                                                ? $response['score'] . ' / ' . $quiz['points_total']
                                                : 'Non noté';
                                        ?>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($response['date'])); ?></td>
                                    <td>
                                        <button onclick="showDetails('<?php echo $response['id']; ?>')" class="btn-secondary btn-small">
                                            Voir détails
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            <?php else: ?>
                <!-- Liste de tous les quiz avec résultats -->
                <header class="dashboard-header">
                    <h1>Résultats des Quiz</h1>
                </header>

                <div class="quiz-grid">
                    <?php 
                    $allQuizzes = $quizDb->getAll();
                    $schoolQuizzes = array_filter($allQuizzes, function($quiz) {
                        return $quiz['user_id'] === $_SESSION['user']['id'] && $quiz['status'] !== 'en cours d\'écriture';
                    });

                    foreach ($schoolQuizzes as $quiz): 
                        // Compter les réponses pour ce quiz
                        $responses = array_filter($responsesDb->getAll(), function($response) use ($quiz) {
                            return $response['quiz_id'] === $quiz['id'];
                        });
                    ?>
                    <div class="quiz-card">
                        <div class="quiz-header">
                            <h3><?php echo htmlspecialchars($quiz['titre']); ?></h3>
                            <span class="status-badge <?php echo $quiz['status']; ?>">
                                <?php echo ucfirst($quiz['status']); ?>
                            </span>
                        </div>
                        <div class="quiz-body">
                            <div class="quiz-stats">
                                <div class="stat">
                                    <span class="stat-label">Réponses :</span>
                                    <span class="stat-value"><?php echo count($responses); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="quiz-footer">
                            <a href="resultats.php?id=<?php echo $quiz['id']; ?>" class="btn-primary btn-small">
                                Voir les résultats
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <script>
    function showDetails(responseId) {
        // Implémentation de l'affichage des détails (peut être fait dans une modal ou une nouvelle page)
        alert('Fonctionnalité à implémenter : afficher les détails de la réponse ' + responseId);
    }
    </script>
</body>
</html>