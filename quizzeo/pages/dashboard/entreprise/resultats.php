<?php
// pages/dashboard/entreprise/resultats.php
session_start();
require_once '../../../includes/auth.php';

// Vérification du rôle
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'entreprise') {
    header('Location: ../../login.php');
    exit;
}

// Récupération des quiz et réponses
$quizDb = new JsonDatabase('quizzes.json');
$responsesDb = new JsonDatabase('responses.json');

// Initialisation des variables
$quizId = isset($_GET['id']) ? $_GET['id'] : null;
$quiz = null;
$quizResponses = [];
$analyseReponses = [];

if ($quizId) {
    // Récupérer le quiz spécifique
    $quiz = $quizDb->findById($quizId);
    
    // Vérifier que le quiz existe et appartient à l'entreprise
    if (!$quiz || !isset($quiz['user_id']) || $quiz['user_id'] !== $_SESSION['user']['id']) {
        $_SESSION['error'] = "Accès non autorisé ou quiz inexistant.";
        header('Location: resultats.php');
        exit;
    }

    // Récupérer toutes les réponses
    $allResponses = $responsesDb->getAll() ?: [];
    
    // Filtrer les réponses pour ce quiz
    $quizResponses = array_filter($allResponses, function($response) use ($quizId) {
        return isset($response['quiz_id']) && $response['quiz_id'] === $quizId;
    });

    // Initialiser l'analyse des réponses
    $analyseReponses = [
        'total_reponses' => count($quizResponses),
        'scores' => [],
        'score_moyen' => 0,
        'score_max' => 0
    ];

    // Calculer les scores
    foreach ($quizResponses as $response) {
        $score = $response['score'] ?? 0;
        $analyseReponses['scores'][] = $score;
    }

    // Calculs statistiques
    if (!empty($analyseReponses['scores'])) {
        $analyseReponses['score_moyen'] = array_sum($analyseReponses['scores']) / count($analyseReponses['scores']);
        $analyseReponses['score_max'] = max($analyseReponses['scores']);
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Résultats - Entreprise</title>
    <link rel="stylesheet" href="../../../assets/css/resultats-entreprise.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <nav class="sidebar">

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
            <?php 
            // Afficher les messages d'erreur
            if (isset($_SESSION['error'])) {
                echo '<div class="alert alert-danger">' . 
                     htmlspecialchars($_SESSION['error']) . 
                     '</div>';
                unset($_SESSION['error']);
            }
            ?>

            <?php if (!$quizId): ?>
                <!-- Liste de tous les quiz avec résultats -->
                <div class="dashboard-header">
                    <h1>Résultats des Quiz</h1>
                </div>

                <div class="quiz-grid">
                    <?php 
                    $allQuizzes = $quizDb->getAll() ?: [];
                    $companyQuizzes = array_filter($allQuizzes, function($quiz) {
                        return isset($quiz['user_id']) && 
                               $quiz['user_id'] === $_SESSION['user']['id'] && 
                               $quiz['status'] !== 'en cours d\'écriture';
                    });

                    foreach ($companyQuizzes as $quiz): 
                        // Compter les réponses pour ce quiz
                        $responses = array_filter($responsesDb->getAll() ?: [], function($response) use ($quiz) {
                            return $response['quiz_id'] === $quiz['id'];
                        });
                    ?>
                    <div class="quiz-card">
                        <div class="quiz-header">
                            <h3><?php echo htmlspecialchars($quiz['titre']); ?></h3>
                            <span class="status-badge <?php echo $quiz['status']; ?>">
                                <?php 
                                switch($quiz['status']) {
                                    case 'lancé': echo 'Actif'; break;
                                    case 'terminé': echo 'Terminé'; break;
                                    default: echo htmlspecialchars($quiz['status']);
                                }
                                ?>
                            </span>
                        </div>
                        <div class="quiz-body">
                            <div class="quiz-stats">
                                <div class="stat">
                                    <span class="stat-label">Réponses :</span>
                                    <span class="stat-value"><?php echo count($responses); ?></span>
                                </div>
                                <div class="stat">
                                    <span class="stat-label">Questions :</span>
                                    <span class="stat-value"><?php echo count($quiz['questions'] ?? []); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="quiz-footer">
                            <a href="resultats.php?id=<?php echo $quiz['id']; ?>" class="btn-primary">
                                Voir les détails
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

            <?php else: ?>
                <!-- Détails des résultats pour un quiz spécifique -->
                <div class="dashboard-header">
                    <div>
                        <h1><?php echo htmlspecialchars($quiz['titre']); ?></h1>
                        <p class="subtitle">Résultats détaillés</p>
                    </div>
                    <a href="resultats.php" class="btn-secondary">Retour aux quiz</a>
                </div>

                <!-- Statistiques générales -->
                <section class="resultats-overview">
                    <div class="stat-card">
                        <h3>Nombre de réponses</h3>
                        <p class="stat-number"><?php echo $analyseReponses['total_reponses']; ?></p>
                    </div>
                    <div class="stat-card">
                        <h3>Score moyen</h3>
                        <p class="stat-number"><?php 
                            echo number_format($analyseReponses['score_moyen'], 2); 
                        ?> / <?php echo $quiz['points_total'] ?? 0; ?></p>
                    </div>
                    <div class="stat-card">
                        <h3>Score maximum</h3>
                        <p class="stat-number"><?php 
                            echo $analyseReponses['score_max']; 
                        ?> / <?php echo $quiz['points_total'] ?? 0; ?></p>
                    </div>
                </section>

                <!-- Liste des réponses -->
                <section class="reponses-liste">
                    <h2>Détail des Réponses</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Participant</th>
                                <th>Score</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($quizResponses as $response): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($response['user_name'] ?? 'Anonyme'); ?></td>
                                <td><?php echo $response['score'] ?? 'N/A'; ?> / <?php echo $quiz['points_total'] ?? 0; ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($response['date'] ?? 'now')); ?></td>
                                <td>
                                    <button onclick="voirDetails('<?php echo $response['id'] ?? ''; ?>')" class="btn-secondary">
                                        Détails
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </section>
            <?php endif; ?>
        </main>
    </div>

    <script>
    function voirDetails(responseId) {
        // TODO: Implémenter l'affichage des détails de réponse
        alert('Fonctionnalité de détails de réponse à venir. ID de réponse : ' + responseId);
    }
    </script>
</body>
</html>