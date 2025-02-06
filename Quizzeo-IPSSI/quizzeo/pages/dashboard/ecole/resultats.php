<?php
// pages/dashboard/ecole/resultats.php
session_start();
require_once '../../../includes/auth.php';

// Vérification du rôle
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'ecole') {
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
    
    // Vérifier que le quiz existe et appartient à l'école
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
    <title>Résultats - École</title>
    <style>
        /* Variables */
:root {
    --primary-color: #8B5CF6;
    --secondary-color: #6B48F3;
    --background-color: #f4f4f9;
    --white: #ffffff;
    --text-color: #333;
    --border-color: #e0e0e0;
}

* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

body {
    font-family: 'Arial', sans-serif;
    background-color: var(--background-color);
    color: var(--text-color);
}

.dashboard-container {
    display: flex;
    min-height: 100vh;
}

/* Sidebar */
.sidebar {
    width: 250px;
    background-color: var(--white);
    border-right: 1px solid var(--border-color);
    padding: 20px 0;
}

.sidebar .menu {
    list-style-type: none;
}

.sidebar .menu li {
    margin-bottom: 5px;
}

.sidebar .menu li a {
    display: block;
    padding: 10px 20px;
    color: #6B48F3;
    text-decoration: none;
    transition: background-color 0.3s ease;
}

.sidebar .menu li a:hover,
.sidebar .menu li a.active {
    background-color: rgba(139, 92, 246, 0.1);
    color: var(--primary-color);
}

/* Main Content */
.main-content {
    flex-grow: 1;
    padding: 30px;
    background-color: var(--background-color);
}

.dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    border-bottom: 1px solid var(--border-color);
    padding-bottom: 15px;
}

.dashboard-header h1 {
    color: var(--primary-color);
    font-size: 24px;
}

.subtitle {
    color: var(--secondary-color);
    font-size: 16px;
}

.btn-secondary {
    background-color: var(--secondary-color);
    color: var(--white);
    padding: 10px 15px;
    text-decoration: none;
    border-radius: 4px;
    transition: background-color 0.3s ease;
}

.btn-secondary:hover {
    background-color: darken(#6B48F3, 10%);
}

/* Statistiques */
.resultats-overview {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background-color: var(--white);
    border-radius: 8px;
    padding: 20px;
    text-align: center;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.stat-card h3 {
    color: var(--secondary-color);
    margin-bottom: 10px;
    font-size: 16px;
}

.stat-number {
    font-size: 24px;
    font-weight: bold;
    color: var(--primary-color);
}

/* Quiz Grid */
.quiz-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
}

.quiz-card {
    background-color: var(--white);
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.quiz-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    background-color: var(--background-color);
    border-bottom: 1px solid var(--border-color);
}

.quiz-header h3 {
    color: var(--primary-color);
    font-size: 16px;
}

.status-badge {
    padding: 5px 10px;
    border-radius: 4px;
    font-size: 0.8em;
    font-weight: bold;
}

.status-badge.lancé {
    background-color: #28a745;
    color: var(--white);
}

.status-badge.terminé {
    background-color: #6c757d;
    color: var(--white);
}

.quiz-body {
    padding: 15px;
}

.quiz-stats {
    display: flex;
    justify-content: space-between;
}

.quiz-stats .stat-label {
    color: var(--secondary-color);
    font-size: 14px;
}

.quiz-stats .stat-value {
    font-weight: bold;
    color: var(--primary-color);
}

.quiz-footer {
    padding: 15px;
    background-color: var(--background-color);
    border-top: 1px solid var(--border-color);
    text-align: center;
}

.btn-primary {
    background-color: var(--primary-color);
    color: var(--white);
    padding: 10px 15px;
    text-decoration: none;
    border-radius: 4px;
    transition: background-color 0.3s ease;
}

.btn-primary:hover {
    background-color: darken(#8B5CF6, 10%);
}

/* Liste des réponses */
.reponses-liste {
    background-color: var(--white);
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.reponses-liste h2 {
    color: var(--primary-color);
    margin-bottom: 20px;
    font-size: 20px;
    border-bottom: 1px solid var(--border-color);
    padding-bottom: 10px;
}

table {
    width: 100%;
    border-collapse: collapse;
}

table th, table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid var(--border-color);
}

table th {
    background-color: var(--background-color);
    color: var(--secondary-color);
}

.btn-secondary {
    background-color: var(--secondary-color);
    color: var(--white);
    padding: 8px 12px;
    text-decoration: none;
    border-radius: 4px;
    transition: background-color 0.3s ease;
}

.btn-secondary:hover {
    background-color: darken(#6B48F3, 10%);
}

/* Alertes */
.alert-danger {
    background-color: #FEE2E2;
    color: #991B1B;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
}

/* Responsive Design */
@media (max-width: 768px) {
    .dashboard-container {
        flex-direction: column;
    }

    .sidebar {
        width: 100%;
        border-right: none;
        border-bottom: 1px solid var(--border-color);
    }

    .sidebar .menu {
        display: flex;
        justify-content: space-around;
    }

    .resultats-overview,
    .quiz-grid {
        grid-template-columns: 1fr;
    }

    .dashboard-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .dashboard-header h1 {
        margin-bottom: 15px;
    }
}
    </style>
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
                    $schoolQuizzes = array_filter($allQuizzes, function($quiz) {
                        return isset($quiz['user_id']) && 
                               $quiz['user_id'] === $_SESSION['user']['id'] && 
                               $quiz['status'] !== 'en cours d\'écriture';
                    });

                    foreach ($schoolQuizzes as $quiz): 
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