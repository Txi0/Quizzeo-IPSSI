<?php
session_start();

// Vérifier si l'utilisateur est connecté et s'il est administrateur
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Fonction pour lire et décoder le JSON en toute sécurité
function readJsonFile($path) {
    if (file_exists($path)) {
        $content = file_get_contents($path);
        if ($content === false) {
            return [];
        }
        $data = json_decode($content, true);
        return $data ?: [];
    }
    return [];
}

// Récupérer les utilisateurs et les quiz
$users = readJsonFile(__DIR__ . '/../../../data/users.json');
$quizzes = readJsonFile(__DIR__ . '/../../../data/quizzes.json');
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration - Quizzeo</title>
    <style>
        :root {
            --primary-color: #8B5CF6;
            --secondary-color: #FFB340;
            --background-color: #F3F4F6;
            --danger-color: #EF4444;
            --success-color: #10B981;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: var(--background-color);
        }

        h1, h2 {
            color: var(--primary-color);
            margin-bottom: 20px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .stat-card h3 {
            margin: 0;
            color: var(--secondary-color);
        }

        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: var(--primary-color);
            margin-top: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #E5E7EB;
        }

        th {
            background-color: var(--primary-color);
            color: white;
        }

        tr:hover {
            background-color: #F9FAFB;
        }

        .action-link {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 4px;
            text-decoration: none;
            background-color: var(--primary-color);
            color: white;
            transition: opacity 0.2s;
        }

        .action-link:hover {
            opacity: 0.9;
        }

        .delete-btn {
            background-color: var(--danger-color);
        }

        .delete-btn:hover {
            background-color: #DC2626;
        }

        .status {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 14px;
        }

        .status-active {
            background-color: var(--success-color);
            color: white;
        }

        .status-inactive {
            background-color: var(--danger-color);
            color: white;
        }

        .btn-logout {
            display: inline-block;
            padding: 8px 16px;
            background-color: var(--danger-color);
            color: white;
            text-decoration: none;
            border-radius: 4px;
            float: right;
        }

        .alert {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 4px;
        }

        .alert-success {
            background-color: var(--success-color);
            color: white;
        }

        .alert-error {
            background-color: var(--danger-color);
            color: white;
        }

        .actions-container {
            display: flex;
            gap: 10px;
        }
    </style>
</head>
<body>
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?php 
            echo $_SESSION['success'];
            unset($_SESSION['success']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error">
            <?php 
            echo $_SESSION['error'];
            unset($_SESSION['error']);
            ?>
        </div>
    <?php endif; ?>

    <a href="../../../logout.php" class="btn-logout">Déconnexion</a>
    <h1>Administration Quizzeo</h1>

    <div class="stats-grid">
        <div class="stat-card">
            <h3>Utilisateurs Total</h3>
            <div class="stat-value"><?php echo count($users); ?></div>
        </div>
        <div class="stat-card">
            <h3>Quiz Total</h3>
            <div class="stat-value"><?php echo count($quizzes); ?></div>
        </div>
        <div class="stat-card">
            <h3>Utilisateurs Actifs</h3>
            <div class="stat-value">
                <?php 
                $activeUsers = array_filter($users, function($user) {
                    return isset($user['active']) && $user['active'];
                });
                echo count($activeUsers);
                ?>
            </div>
        </div>
    </div>

    <h2>Liste des utilisateurs</h2>
    <table>
        <thead>
            <tr>
                <th>Nom</th>
                <th>Email</th>
                <th>Rôle</th>
                <th>Statut</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
            <tr>
                <td><?php echo htmlspecialchars($user['nom'] ?? 'N/A'); ?></td>
                <td><?php echo htmlspecialchars($user['email'] ?? 'N/A'); ?></td>
                <td><?php echo htmlspecialchars($user['role'] ?? 'N/A'); ?></td>
                <td>
                    <span class="status <?php echo isset($user['active']) && $user['active'] ? 'status-active' : 'status-inactive'; ?>">
                        <?php echo isset($user['active']) && $user['active'] ? 'Actif' : 'Inactif'; ?>
                    </span>
                </td>
                <td>
                    <a href="admin_action.php?action=toggle_user_status&id=<?php echo $user['id'] ?? ''; ?>" 
                       class="action-link">
                        <?php echo isset($user['active']) && $user['active'] ? 'Désactiver' : 'Activer'; ?>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Liste des quiz</h2>
    <table>
        <thead>
            <tr>
                <th>Titre</th>
                <th>Statut</th>
                <th>Réponses</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($quizzes as $quiz): ?>
            <tr>
                <td><?php echo htmlspecialchars($quiz['titre'] ?? 'N/A'); ?></td>
                <td>
                    <span class="status <?php echo ($quiz['status'] ?? '') === 'actif' ? 'status-active' : 'status-inactive'; ?>">
                        <?php echo ucfirst($quiz['status'] ?? 'Inactif'); ?>
                    </span>
                </td>
                <td><?php echo $quiz['nb_reponses'] ?? 0; ?></td>
                <td>
                    <div class="actions-container">
                        <a href="admin_action.php?action=toggle_quiz_status&id=<?php echo $quiz['id'] ?? ''; ?>" 
                           class="action-link">
                            <?php echo ($quiz['status'] ?? '') === 'actif' ? 'Désactiver' : 'Activer'; ?>
                        </a>
                        <a href="admin_action.php?action=delete_quiz&id=<?php echo $quiz['id'] ?? ''; ?>" 
                           class="action-link delete-btn"
                           onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce quiz ?');">
                            Supprimer
                        </a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>