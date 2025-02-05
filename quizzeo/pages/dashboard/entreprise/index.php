<?php
session_start();
require_once '../../../includes/auth.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'entreprise') {
    header('Location: ../../login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - Quizzeo</title>
    <link rel="stylesheet" href="../../../assets/css/base.css">
    <link rel="stylesheet" href="../../../assets/css/dashboard-entreprise.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="logo">
                <img src="../../../assets/images/logo.png" alt="Quizzeo">
            </div>
            <div class="user-info">
                <h3><?php echo htmlspecialchars($_SESSION['user']['nom_entreprise']); ?></h3>
                <p>Entreprise</p>
            </div>
            <ul class="menu">
                <li><a href="index.php" class="active">Tableau de bord</a></li>
                <li><a href="create-quiz.php">Créer un questionnaire</a></li>
                <li><a href="mes-questionnaires.php">Mes questionnaires</a></li>
                <li><a href="analyse.php">Analyses</a></li>
                <li><a href="../../../logout.php">Déconnexion</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="dashboard-header">
                <h1>Tableau de bord</h1>
                <a href="create-quiz.php" class="btn-primary">Nouveau questionnaire</a>
            </div>

            <!-- Statistiques -->
            <div class="stats-container">
                <div class="stat-card">
                    <h3>Total Questionnaires</h3>
                    <div class="stat-value">0</div>
                </div>
                <div class="stat-card">
                    <h3>Questionnaires Actifs</h3>
                    <div class="stat-value">0</div>
                </div>
                <div class="stat-card">
                    <h3>Total Réponses</h3>
                    <div class="stat-value">0</div>
                </div>
            </div>

            <!-- Questionnaires Récents -->
            <div class="recent-questionnaires">
                <h2>Questionnaires Récents</h2>
                <p>Aucun questionnaire récent pour le moment.</p>
            </div>
        </div>
    </div>
</body>
</html>
