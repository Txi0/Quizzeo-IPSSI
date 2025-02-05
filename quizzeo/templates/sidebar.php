<!-- templates/sidebar.php -->
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
