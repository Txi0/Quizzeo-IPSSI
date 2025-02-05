<?php
// pages/dashboard/entreprise/analyse.php
session_start();
require_once '../../../includes/auth.php';

// Vérification du rôle
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'entreprise') {
    header('Location: ../../login.php');
    exit;
}

// Initialisation des bases de données
$quizDb = new JsonDatabase('quizzes.json');
$responsesDb = new JsonDatabase('responses.json');

// Récupération du questionnaire spécifique si un ID est fourni
$quizId = $_GET['id'] ?? null;

if ($quizId) {
    $quiz = $quizDb->findById($quizId);
    if (!$quiz || $quiz['user_id'] !== $_SESSION['user']['id']) {
        header('Location: analyse.php');
        exit;
    }

    // Récupérer toutes les réponses pour ce questionnaire
    $allResponses = $responsesDb->getAll();
    $quizResponses = array_filter($allResponses, function($response) use ($quizId) {
        return $response['quiz_id'] === $quizId;
    });

    // Analyser les réponses
    $analysis = [];
    foreach ($quiz['questions'] as $index => $question) {
        $analysis[$index] = [
            'question' => $question['texte'],
            'type' => $question['type'],
            'responses' => []
        ];

        switch ($question['type']) {
            case 'rating':
                // Initialiser les compteurs pour chaque note
                $analysis[$index]['ratings'] = array_fill(1, 5, 0);
                $analysis[$index]['average'] = 0;
                $totalRatings = 0;
                
                foreach ($quizResponses as $response) {
                    if (isset($response['reponses'][$index])) {
                        $rating = intval($response['reponses'][$index]);
                        $analysis[$index]['ratings'][$rating]++;
                        $totalRatings += $rating;
                    }
                }
                
                if (count($quizResponses) > 0) {
                    $analysis[$index]['average'] = $totalRatings / count($quizResponses);
                }
                break;

            case 'qcm':
                // Compter les occurrences de chaque option
                $analysis[$index]['options'] = array_fill(0, count($question['options']), 0);
                foreach ($quizResponses as $response) {
                    if (isset($response['reponses'][$index])) {
                        $optionIndex = intval($response['reponses'][$index]);
                        $analysis[$index]['options'][$optionIndex]++;
                    }
                }
                break;

            case 'text':
                // Collecter toutes les réponses textuelles
                foreach ($quizResponses as $response) {
                    if (!empty($response['reponses'][$index])) {
                        $analysis[$index]['responses'][] = $response['reponses'][$index];
                    }
                }
                break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analyse des questionnaires - Quizzeo</title>
    <link rel="stylesheet" href="../../../assets/css/dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <nav class="sidebar">
            <div class="logo">
                <img src="../../../assets/images/logo.png" alt="Quizzeo">
            </div>
            <div class="user-info">
                <h3><?php echo htmlspecialchars($_SESSION['user']['nom_entreprise']); ?></h3>
            </div>
            <ul class="menu">
                <li><a href="index.php">Tableau de bord</a></li>
                <li><a href="create-quiz.php">Créer un questionnaire</a></li>
                <li><a href="mes-quiz.php">Mes questionnaires</a></li>
                <li class="active"><a href="analyse.php">Analyses</a></li>
                <li><a href="../../../logout.php">Déconnexion</a></li>
            </ul>
        </nav>

        <!-- Main Content -->
        <main class="main-content">
            <?php if ($quizId && isset($quiz)): ?>
                <header class="dashboard-header">
                    <div>
                        <h1><?php echo htmlspecialchars($quiz['titre']); ?></h1>
                        <p class="subtitle">Analyse des réponses</p>
                    </div>
                    <a href="analyse.php" class="btn-secondary">Retour aux analyses</a>
                </header>

                <div class="analysis-container">
                    <!-- Statistiques générales -->
                    <div class="stats-summary">
                        <div class="stat-card">
                            <h3>Nombre de réponses</h3>
                            <p class="stat-number"><?php echo count($quizResponses); ?></p>
                        </div>
                    </div>

                    <!-- Analyse détaillée par question -->
                    <?php foreach ($analysis as $index => $questionAnalysis): ?>
                    <div class="question-analysis">
                        <h3><?php echo htmlspecialchars($questionAnalysis['question']); ?></h3>
                        <?php switch ($questionAnalysis['type']): 
                            case 'rating': ?>
                                <div class="rating-analysis">
                                    <canvas id="ratingChart<?php echo $index; ?>"></canvas>
                                    <p class="average-rating">
                                        Note moyenne : <?php echo number_format($questionAnalysis['average'], 2); ?>/5
                                    </p>
                                </div>
                                <script>
                                new Chart(document.getElementById('ratingChart<?php echo $index; ?>'), {
                                    type: 'bar',
                                    data: {
                                        labels: ['1', '2', '3', '4', '5'],
                                        datasets: [{
                                            label: 'Nombre de réponses',
                                            data: <?php echo json_encode(array_values($questionAnalysis['ratings'])); ?>,
                                            backgroundColor: '#6B46C1'
                                        }]
                                    },
                                    options: {
                                        responsive: true,
                                        scales: {
                                            y: {
                                                beginAtZero: true,
                                                ticks: {
                                                    stepSize: 1
                                                }
                                            }
                                        }
                                    }
                                });
                                </script>
                            <?php break; ?>

                            <?php case 'qcm': ?>
                                <div class="qcm-analysis">
                                    <canvas id="qcmChart<?php echo $index; ?>"></canvas>
                                </div>
                                <script>
                                new Chart(document.getElementById('qcmChart<?php echo $index; ?>'), {
                                    type: 'pie',
                                    data: {
                                        labels: <?php echo json_encode($quiz['questions'][$index]['options']); ?>,
                                        datasets: [{
                                            data: <?php echo json_encode($questionAnalysis['options']); ?>,
                                            backgroundColor: [
                                                '#6B46C1',
                                                '#805AD5',
                                                '#9F7AEA',
                                                '#B794F4',
                                                '#D6BCFA'
                                            ]
                                        }]
                                    },
                                    options: {
                                        responsive: true,
                                        plugins: {
                                            legend: {
                                                position: 'top',
                                            }
                                        }
                                    }
                                });
                                </script>
                            <?php break; ?>

                            <?php case 'text': ?>
                                <div class="text-responses">
                                    <?php if (empty($questionAnalysis['responses'])): ?>
                                        <p class="no-responses">Aucune réponse pour cette question</p>
                                    <?php else: ?>
                                        <div class="responses-list">
                                            <?php foreach ($questionAnalysis['responses'] as $response): ?>
                                                <div class="response-item">
                                                    <?php echo htmlspecialchars($response); ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php break; ?>
                        <?php endswitch; ?>
                    </div>
                    <?php endforeach; ?>
                </div>

            <?php else: ?>
                <!-- Liste de tous les questionnaires -->
                <header class="dashboard-header">
                    <h1>Analyses des questionnaires</h1>
                </header>

                <div class="quiz-grid">
                    <?php 
                    $allQuizzes = $quizDb->getAll();
                    $companyQuizzes = array_filter($allQuizzes, function($quiz) {
                        return $quiz['user_id'] === $_SESSION['user']['id'] && $quiz['status'] !== 'en cours d\'écriture';
                    });

                    foreach ($companyQuizzes as $quiz): 
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
                                <p><?php echo htmlspecialchars($quiz['description'] ?? ''); ?></p>
                                <div class="quiz-stats">
                                    <div class="stat">
                                        <span class="stat-label">Réponses :</span>
                                        <span class="stat-value"><?php echo count($responses); ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="quiz-footer">
                                <a href="analyse.php?id=<?php echo $quiz['id']; ?>" class="btn-primary">
                                    Voir l'analyse
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>