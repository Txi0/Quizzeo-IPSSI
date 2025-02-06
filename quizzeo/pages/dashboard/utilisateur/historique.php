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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary-color: #8B5CF6;
            --secondary-color: #7C3AED;
            --background-color: #F3F4F6;
            --text-color: #1F2937;
            --white: #ffffff;
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
            line-height: 1.6;
        }

        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 250px;
            background: var(--white);
            padding: 20px;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
            border-right: 1px solid var(--border-color);
        }

        .user-info {
            text-align: center;
            padding: 20px;
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 20px;
        }

        .user-info h3 {
            color: var(--primary-color);
            font-size: 18px;
        }

        .menu {
            list-style-type: none;
        }

        .menu li a {
            display: block;
            padding: 10px 15px;
            color: var(--secondary-color);
            text-decoration: none;
            transition: all 0.3s ease;
            border-radius: 4px;
        }

        .menu li a:hover,
        .menu li a.active {
            background-color: var(--primary-color);
            color: var(--white);
        }

        /* Main Content */
        .main-content {
            flex: 1;
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

        /* Empty State */
        .empty-state {
            background: var(--white);
            border-radius: 8px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .empty-state p {
            color: var(--secondary-color);
            margin-bottom: 20px;
        }

        .btn-primary {
            background: var(--primary-color);
            color: var(--white);
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }

        .btn-primary:hover {
            background: var(--secondary-color);
        }

        /* Response Table */
        .historique-list {
            background: var(--white);
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .response-table {
            width: 100%;
            border-collapse: collapse;
        }

        .response-table thead {
            background-color: var(--background-color);
        }

        .response-table th, 
        .response-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        .response-table th {
            color: var(--secondary-color);
            font-weight: bold;
        }

        .quiz-info {
            display: flex;
            flex-direction: column;
        }

        .quiz-description {
            color: var(--secondary-color);
            font-size: 0.8em;
            margin-top: 5px;
        }

        .score {
            font-weight: bold;
            color: var(--primary-color);
        }

        .badge {
            background-color: #28a745;
            color: var(--white);
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.8em;
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
            background-color: var(--primary-color);
        }

        .btn-small {
            font-size: 0.8em;
            padding: 5px 10px;
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

            .response-table {
                font-size: 0.9em;
            }

            .response-table th, 
            .response-table td {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <nav class="sidebar">
            <div class="user-info">
                <h3>👤 <?php echo htmlspecialchars($_SESSION['user']['nom'] . ' ' . $_SESSION['user']['prenom']); ?></h3>
            </div>
            <ul class="menu">
                <li><a href="index.php">📊 Tableau de bord</a></li>
                <li class="active"><a href="historique.php">📜 Historique</a></li>
                <li><a href="../../../logout.php">🚪 Déconnexion</a></li>
            </ul>
        </nav>

        <!-- Main Content -->
        <main class="main-content">
            <header class="dashboard-header">
                <h1>📝 Historique des questionnaires</h1>
            </header>

            <?php if (empty($historique)): ?>
                <div class="empty-state">
                    <p>🌱 Vous n'avez pas encore répondu à des questionnaires.</p>
                    <a href="index.php" class="btn-primary">🔍 Répondre à un questionnaire</a>
                </div>
            <?php else: ?>
                <div class="historique-list">
                    <table class="response-table">
                        <thead>
                            <tr>
                                <th>📋 Questionnaire</th>
                                <th>🏫 Type</th>
                                <th>📅 Date de réponse</th>
                                <th>🏆 Résultat</th>
                                <th>🔍 Actions</th>
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
                                            echo '🏫 Quiz École';
                                        } else {
                                            echo '🏢 Questionnaire Entreprise';
                                        }
                                    ?>
                                </td>
                                <td>📅 <?php echo date('d/m/Y H:i', strtotime($item['response']['date'])); ?></td>
                                <td>
                                    <?php if (isset($item['response']['score'])): ?>
                                        <span class="score">
                                            🏆 <?php echo $item['response']['score']; ?> / <?php echo $item['quiz']['points_total']; ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge">✅ Complété</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="voir-reponse.php?id=<?php echo $item['response']['id']; ?>" 
                                       class="btn-secondary btn-small">
                                        👀 Voir mes réponses
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