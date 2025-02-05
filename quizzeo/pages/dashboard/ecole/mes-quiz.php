<?php
// pages/dashboard/ecole/mes-quiz.php
session_start();
require_once '../../../includes/auth.php';

// Vérification du rôle
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'ecole') {
    header('Location: ../../login.php');
    exit;
}

// Récupération des quiz via la classe JsonDatabase
$quizDb = new JsonDatabase('quizzes.json'); // Adaptez le chemin si nécessaire (ex: 'data/quizzes.json')
$schoolQuizzes = array_filter($quizDb->getAll(), function($quiz) {
    return $quiz['user_id'] === $_SESSION['user']['id'];
});

// Trier par date (le plus récent en premier)
usort($schoolQuizzes, function($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});

// Message de succès si création/modification
$success = isset($_GET['success']) ? true : false;

// Fonction pour générer un token de partage si nécessaire (non utilisé ici, mais conservé pour référence)
function generateShareToken($quiz) {
    if (!isset($quiz['share_token'])) {
        return bin2hex(random_bytes(16));
    }
    return $quiz['share_token'];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Quiz - École - Quizzeo</title>
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
                <h3><?php echo htmlspecialchars($_SESSION['user']['nom_etablissement']); ?></h3>
            </div>
            <ul class="menu">
                <li><a href="index.php">Tableau de bord</a></li>
                <li><a href="create-quiz.php">Créer un quiz</a></li>
                <li class="active"><a href="mes-quiz.php">Mes quiz</a></li>
                <li><a href="resultats.php">Résultats</a></li>
                <li><a href="../../../logout.php">Déconnexion</a></li>
            </ul>
        </nav>

        <!-- Main Content -->
        <main class="main-content">
            <header class="dashboard-header">
                <h1>Mes Quiz</h1>
                <a href="create-quiz.php" class="btn-primary">Nouveau Quiz</a>
            </header>

            <?php if ($success): ?>
            <div class="alert alert-success">
                Le quiz a été créé avec succès !
            </div>
            <?php endif; ?>

            <!-- Filtres -->
            <div class="filters">
                <select id="statusFilter" onchange="filterQuizzes()">
                    <option value="">Tous les statuts</option>
                    <option value="en cours d'écriture">En cours d'écriture</option>
                    <option value="lancé">Lancé</option>
                    <option value="terminé">Terminé</option>
                </select>
            </div>

            <!-- Liste des quiz -->
            <div class="quiz-grid">
                <?php foreach ($schoolQuizzes as $quiz): ?>
                <div class="quiz-card" data-status="<?php echo $quiz['status']; ?>">
                    <div class="quiz-header">
                        <h3><?php echo htmlspecialchars($quiz['titre']); ?></h3>
                        <span class="status-badge <?php echo $quiz['status']; ?>">
                            <?php echo ucfirst($quiz['status']); ?>
                        </span>
                    </div>
                    
                    <div class="quiz-body">
                        <?php if (!empty($quiz['description'])): ?>
                        <p class="quiz-description"><?php echo htmlspecialchars($quiz['description']); ?></p>
                        <?php endif; ?>
                        
                        <div class="quiz-stats">
                            <div class="stat">
                                <span class="stat-label">Questions :</span>
                                <span class="stat-value"><?php echo count($quiz['questions']); ?></span>
                            </div>
                            <div class="stat">
                                <span class="stat-label">Points total :</span>
                                <span class="stat-value"><?php echo $quiz['points_total']; ?></span>
                            </div>
                            <div class="stat">
                                <span class="stat-label">Réponses :</span>
                                <span class="stat-value"><?php echo $quiz['nb_reponses']; ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="quiz-footer">
                        <!-- Actions selon le statut -->
                        <?php if ($quiz['status'] === 'en cours d\'écriture'): ?>
                            <button onclick="modifierQuiz('<?php echo $quiz['id']; ?>')" class="btn-secondary btn-small">
                                Modifier
                            </button>
                            <button onclick="lancerQuiz('<?php echo $quiz['id']; ?>')" class="btn-success btn-small">
                                Lancer
                            </button>
                        <?php elseif ($quiz['status'] === 'lancé'): ?>
                            <?php
                                // Génération du lien de partage en utilisant l'ID du quiz
                                $shareLink = "http://" . $_SERVER['HTTP_HOST'] . "/repondre.php?id=" . $quiz['id'];
                                echo '<div class="share-section">
                                        <input type="text" 
                                               value="' . htmlspecialchars($shareLink) . '" 
                                               id="shareLink_' . $quiz['id'] . '" 
                                               readonly
                                               class="share-link">
                                        <button onclick="copyShareLink(\'' . $quiz['id'] . '\')" class="btn-info btn-small">
                                            Copier le lien
                                        </button>
                                      </div>';
                            ?>
                            <button onclick="terminerQuiz('<?php echo $quiz['id']; ?>')" class="btn-warning btn-small">
                                Terminer
                            </button>
                        <?php endif; ?>
                        
                        <?php if ($quiz['status'] !== 'en cours d\'écriture'): ?>
                            <a href="resultats.php?id=<?php echo $quiz['id']; ?>" class="btn-primary btn-small">
                                Voir les résultats
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>

    <!-- Script JavaScript -->
    <script>
    function filterQuizzes() {
        const statusFilter = document.getElementById('statusFilter').value;
        const cards = document.querySelectorAll('.quiz-card');
        
        cards.forEach(card => {
            if (!statusFilter || card.dataset.status === statusFilter) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }

    function copyShareLink(quizId) {
        const linkInput = document.getElementById('shareLink_' + quizId);
        if (linkInput) {
            linkInput.select();
            document.execCommand('copy');
            alert('Lien copié dans le presse-papier !');
        } else {
            console.error("L'élément avec l'ID shareLink_" + quizId + " n'a pas été trouvé.");
        }
    }

    function lancerQuiz(quizId) {
        if (confirm('Êtes-vous sûr de vouloir lancer ce quiz ? Les étudiants pourront y répondre.')) {
            updateQuizStatus(quizId, 'lancé');
        }
    }

    function terminerQuiz(quizId) {
        if (confirm('Êtes-vous sûr de vouloir terminer ce quiz ? Les étudiants ne pourront plus y répondre.')) {
            updateQuizStatus(quizId, 'terminé');
        }
    }

    function modifierQuiz(quizId) {
        window.location.href = `create-quiz.php?id=${quizId}`;
    }

    function updateQuizStatus(quizId, status) {
        fetch('update-quiz-status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                quiz_id: quizId,
                status: status
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert('Erreur lors de la mise à jour du statut');
            }
        });
    }
    </script>
</body>
</html>
