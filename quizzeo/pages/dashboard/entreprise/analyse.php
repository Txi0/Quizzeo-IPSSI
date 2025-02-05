<?php
// pages/dashboard/entreprise/analyses.php
session_start();
require_once '../../../includes/auth.php';

// Vérification du rôle
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'entreprise') {
    header('Location: ../../login.php');
    exit;
}

// Récupération des questionnaires
$quizDb = new JsonDatabase('quizzes.json');
$responsesDb = new JsonDatabase('responses.json');

$entrepriseQuizzes = array_filter($quizDb->getAll(), function($quiz) {
    return $quiz['user_id'] === $_SESSION['user']['id'];
});

// Si un ID de questionnaire spécifique est fourni
$selectedQuizId = $_GET['id'] ?? null;
$selectedQuiz = null;
$quizResponses = [];

if ($selectedQuizId) {
    foreach ($entrepriseQuizzes as $quiz) {
        if ($quiz['id'] === $selectedQuizId) {
            $selectedQuiz = $quiz;
            break;
        }
    }
    
    if ($selectedQuiz) {
        $allResponses = $responsesDb->getAll();
        $quizResponses = array_filter($allResponses, function($response) use ($selectedQuizId) {
            return $response['quiz_id'] === $selectedQuizId;
        });
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analyses - Quizzeo</title>
    <style>
        /* Styles spécifiques pour la page d'analyses */
        .analysis-container {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .quiz-selector {
            margin-bottom: 20px;
        }

        .quiz-selector select {
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #E2E8F0;
            width: 100%;
            max-width: 300px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: #F7FAFC;
            padding: 15px;
            border-radius: 6px;
            text-align: center;
        }

        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #8B5CF6;
            margin: 10px 0;
        }

        .question-analysis {
            margin-bottom: 30px;
            padding: 15px;
            border: 1px solid #E2E8F0;
            border-radius: 6px;
        }

        .response-chart {
            margin: 15px 0;
            height: 200px;
        }

        .rating-average {
            font-size: 18px;
            margin: 10px 0;
        }

        .text-responses {
            margin-top: 15px;
        }

        .text-response {
            background: #F7FAFC;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 4px;
        }

        /* Style de base hérité du dashboard */
        <?php include_once('../../../assets/css/dashboard-entreprise.css'); ?>
    </style>
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
                <li><a href="mes-questionnaires.php">Mes questionnaires</a></li>
                <li class="active"><a href="analyses.php">Analyses</a></li>
                <li><a href="../../../logout.php">Déconnexion</a></li>
            </ul>
        </nav>

        <!-- Main Content -->
        <main class="main-content">
            <div class="header">
                <h1>Analyses des questionnaires</h1>
            </div>

            <div class="analysis-container">
                <!-- Sélecteur de questionnaire -->
                <div class="quiz-selector">
                    <select onchange="window.location.href='analyses.php?id=' + this.value">
                        <option value="">Sélectionnez un questionnaire</option>
                        <?php foreach ($entrepriseQuizzes as $quiz): ?>
                            <option value="<?php echo $quiz['id']; ?>" 
                                    <?php echo $selectedQuizId === $quiz['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($quiz['titre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <?php if ($selectedQuiz): ?>
                    <!-- Statistiques générales -->
                    <div class="stats-grid">
                        <div class="stat-card">
                            <h3>Total Réponses</h3>
                            <div class="stat-value"><?php echo count($quizResponses); ?></div>
                        </div>
                        <div class="stat-card">
                            <h3>Taux de Complétion</h3>
                            <div class="stat-value">
                                <?php 
                                $completionRate = count($quizResponses) > 0 ? 
                                    count(array_filter($quizResponses, function($r) use ($selectedQuiz) {
                                        return count($r['reponses'] ?? []) === count($selectedQuiz['questions']);
                                    })) / count($quizResponses) * 100 : 0;
                                echo number_format($completionRate, 1) . '%';
                                ?>
                            </div>
                        </div>
                    </div>

                    <!-- Analyse par question -->
                    <?php foreach ($selectedQuiz['questions'] as $qIndex => $question): ?>
                    <div class="question-analysis">
                        <h3>Question <?php echo $qIndex + 1; ?>: <?php echo htmlspecialchars($question['texte']); ?></h3>
                        
                        <?php if ($question['type'] === 'rating'): ?>
                            <!-- Analyse des notes -->
                            <?php
                            $ratings = array_map(function($response) use ($qIndex) {
                                return $response['reponses'][$qIndex] ?? null;
                            }, $quizResponses);
                            $ratings = array_filter($ratings, 'is_numeric');
                            $average = count($ratings) > 0 ? array_sum($ratings) / count($ratings) : 0;
                            ?>
                            <div class="rating-average">
                                Note moyenne: <?php echo number_format($average, 1); ?>/5
                            </div>

                        <?php elseif ($question['type'] === 'qcm'): ?>
                            <!-- Analyse des QCM -->
                            <?php
                            $optionCounts = array_fill(0, count($question['options']), 0);
                            foreach ($quizResponses as $response) {
                                if (isset($response['reponses'][$qIndex])) {
                                    $optionCounts[$response['reponses'][$qIndex]]++;
                                }
                            }
                            ?>
                            <div class="options-summary">
                                <?php foreach ($question['options'] as $oIndex => $option): ?>
                                    <div>
                                        <?php 
                                        $percentage = count($quizResponses) > 0 ? 
                                            ($optionCounts[$oIndex] / count($quizResponses) * 100) : 0;
                                        echo htmlspecialchars($option) . ': ' . 
                                             number_format($percentage, 1) . '%' . 
                                             ' (' . $optionCounts[$oIndex] . ' réponses)';
                                        ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                        <?php else: ?>
                            <!-- Réponses textuelles -->
                            <div class="text-responses">
                                <?php
                                $textResponses = array_map(function($response) use ($qIndex) {
                                    return $response['reponses'][$qIndex] ?? null;
                                }, $quizResponses);
                                $textResponses = array_filter($textResponses);
                                
                                foreach ($textResponses as $response): ?>
                                    <div class="text-response">
                                        <?php echo htmlspecialchars($response); ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>

                <?php else: ?>
                    <p>Sélectionnez un questionnaire pour voir son analyse.</p>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>