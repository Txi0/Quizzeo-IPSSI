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
    <title>Analyses - Quizzeo</title>
    <link rel="stylesheet" href="../../../assets/css/base.css">
    <link rel="stylesheet" href="../../../assets/css/analyse.css">
</head>
<body>
    <div class="dashboard-container">
        <?php include '../../../templates/sidebar.php'; ?>

        <div class="main-content">
            <div class="dashboard-header">
                <h1>Analyses des Questionnaires</h1>
            </div>

            <div class="analytics-container">
                <!-- Exemple de graphique ou statistique -->
                <div class="analysis-card">
                    <h3>Taux de réponse</h3>
                    <p>75%</p>
                </div>
                <div class="analysis-card">
                    <h3>Satisfaction Moyenne</h3>
                    <p>4.2 / 5</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
