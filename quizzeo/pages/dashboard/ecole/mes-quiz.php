<?php
// pages/dashboard/ecole/mes-quiz.php
session_start();
require_once '../../../includes/auth.php';

// Vérification du rôle
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'ecole') {
    header('Location: ../../login.php');
    exit;
}

// Récupération des quiz
$quizDb = new JsonDatabase('quizzes.json');
$schoolQuizzes = array_filter($quizDb->getAll(), function($quiz) {
    return isset($quiz['user_id']) && $quiz['user_id'] === $_SESSION['user']['id'];
});

// Fonction pour générer un lien de partage
function generateShareToken() {
    return bin2hex(random_bytes(16));
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Quiz - École</title>
    <style>
        /* Styles pour le dashboard École */
        :root {
            --primary-color: #8B5CF6;  /* Couleur violette principale */
            --secondary-color: #6B48F3; /* Nuance de violet légèrement différente */
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

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .header h1 {
            color: var(--primary-color);
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: var(--white);
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 6px;
            transition: background-color 0.3s ease;
        }

        .btn-primary:hover {
            background-color: var(--secondary-color);
        }

        /* Quiz Cards */
        .quiz-list {
            display: grid;
            gap: 20px;
        }

        .quiz-card {
            background-color: var(--white);
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .quiz-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border-color);
        }

        .quiz-header h3 {
            color: var(--primary-color);
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.8em;
            font-weight: bold;
        }

        .status-badge.en\ cours\ d\'écriture {
            background-color: #FFA500;
            color: var(--white);
        }

        .status-badge.lancé {
            background-color: #28a745;
            color: var(--white);
        }

        .status-badge.terminé {
            background-color: #6c757d;
            color: var(--white);
        }

        .quiz-info {
            margin-bottom: 15px;
        }

        .quiz-info p {
            color: #6B7280;
            margin: 5px 0;
        }

        .quiz-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .btn-secondary, .btn-warning, .btn-danger {
            padding: 8px 15px;
            border-radius: 6px;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        .btn-secondary {
            background-color: var(--secondary-color);
            color: var(--white);
        }

        .btn-warning {
            background-color: #FFA500;
            color: var(--white);
        }

        .btn-danger {
            background-color: #dc3545;
            color: var(--white);
        }

        .share-link {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .share-link input {
            flex-grow: 1;
            padding: 8px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
        }

        .btn-copy {
            background-color: var(--primary-color);
            color: var(--white);
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
        }

        .empty-state {
            background-color: var(--white);
            border-radius: 8px;
            padding: 40px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .alert-success {
            background-color: #DEF7EC;
            color: #03543F;
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

            .quiz-actions {
                flex-direction: column;
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
                <li class="active"><a href="mes-quiz.php">Mes quiz</a></li>
                <li><a href="resultats.php">Résultats</a></li>
                <li><a href="../../../logout.php">Déconnexion</a></li>
            </ul>
        </nav>

        <main class="main-content">
            <div class="header">
                <h1>Mes Quiz</h1>
                <a href="create-quiz.php" class="btn-primary">Nouveau Quiz</a>
            </div>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    Le quiz a été enregistré avec succès !
                </div>
            <?php endif; ?>

            <div class="quiz-list">
                <?php if (empty($schoolQuizzes)): ?>
                    <div class="empty-state">
                        <p>Vous n'avez pas encore créé de quiz.</p>
                        <a href="create-quiz.php" class="btn-primary">Créer mon premier quiz</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($schoolQuizzes as $quiz): ?>
                        <div class="quiz-card">
                            <div class="quiz-header">
                                <h3><?php echo htmlspecialchars($quiz['titre']); ?></h3>
                                <span class="status-badge <?php echo $quiz['status']; ?>">
                                    <?php echo ucfirst($quiz['status']); ?>
                                </span>
                            </div>

                            <div class="quiz-info">
                                <p>Questions: <?php echo count($quiz['questions']); ?></p>
                                <p>Points total: <?php echo $quiz['points_total']; ?></p>
                                <p>Réponses: <?php echo $quiz['nb_reponses'] ?? 0; ?></p>
                            </div>

                            <div class="quiz-actions">
                                <?php if ($quiz['status'] === 'en cours d\'écriture'): ?>
                                    <a href="edit-quiz.php?id=<?php echo $quiz['id']; ?>" 
                                       class="btn-secondary">Modifier</a>
                                    <button onclick="lancerQuiz('<?php echo $quiz['id']; ?>')" 
                                            class="btn-primary">Lancer</button>
                                <?php elseif ($quiz['status'] === 'lancé'): ?>
                                    <div class="share-link">
                                        <?php
                                        if (!isset($quiz['share_token'])) {
                                            $quiz['share_token'] = generateShareToken();
                                            $quizDb->update($quiz['id'], ['share_token' => $quiz['share_token']]);
                                        }
                                        $shareLink = "http://" . $_SERVER['HTTP_HOST'] . "/repondre.php?token=" . $quiz['share_token'];
                                        ?>
                                        <input type="text" value="<?php echo $shareLink; ?>" 
                                               id="shareLink_<?php echo $quiz['id']; ?>" readonly>
                                        <button onclick="copyLink('<?php echo $quiz['id']; ?>')" 
                                                class="btn-copy">Copier le lien</button>
                                    </div>
                                    <button onclick="terminerQuiz('<?php echo $quiz['id']; ?>')" 
                                            class="btn-warning">Terminer</button>
                                <?php endif; ?>
                                
                                <a href="resultats.php?id=<?php echo $quiz['id']; ?>" 
                                   class="btn-secondary">Voir les résultats</a>
                                
                                <?php if ($quiz['status'] === 'en cours d\'écriture'): ?>
                                    <button onclick="supprimerQuiz('<?php echo $quiz['id']; ?>')" 
                                            class="btn-danger">Supprimer</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
    function copyLink(quizId) {
        const input = document.getElementById('shareLink_' + quizId);
        input.select();
        document.execCommand('copy');
        alert('Lien copié !');
    }

    function lancerQuiz(quizId) {
        if (confirm('Voulez-vous vraiment lancer ce quiz ?')) {
            window.location.href = 'update-quiz-status.php?id=' + quizId + '&status=lancé';
        }
    }

    function terminerQuiz(quizId) {
        if (confirm('Voulez-vous vraiment terminer ce quiz ? Les étudiants ne pourront plus y répondre.')) {
            window.location.href = 'update-quiz-status.php?id=' + quizId + '&status=terminé';
        }
    }

    function supprimerQuiz(quizId) {
        if (confirm('Voulez-vous vraiment supprimer ce quiz ? Cette action est irréversible.')) {
            window.location.href = 'delete-quiz.php?id=' + quizId;
        }
    }
    </script>
</body>
</html>