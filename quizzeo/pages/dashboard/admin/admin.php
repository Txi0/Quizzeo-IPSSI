<?php
session_start();

// Vérifier si l'utilisateur est connecté et s'il est administrateur
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Récupérer les utilisateurs et les quiz
$users = json_decode(file_get_contents(__DIR__ . '/../../../data/users.json'), true);
$quizzes = json_decode(file_get_contents(__DIR__ . '/../../../data/quizzes.json'), true);

echo "<h2>Liste des utilisateurs</h2>";
echo "<table><tr><th>Nom</th><th>Email</th><th>Rôle</th><th>Statut</th><th>Action</th></tr>";

foreach ($users as $user) {
    // Afficher le statut en fonction de 'active'
    $status = $user['active'] ? 'Actif' : 'Désactivé';
    echo "<tr>
            <td>{$user['name']}</td>
            <td>{$user['email']}</td>
            <td>{$user['role']}</td>
            <td>{$status}</td>
            <td><a href='admin_action.php?action=toggle_user_status&id={$user['id']}'>Désactiver/Activer</a></td>
          </tr>";
}

echo "</table>";

echo "<h2>Liste des quiz</h2>";
echo "<table><tr><th>Titre</th><th>Statut</th><th>Réponses</th><th>Action</th></tr>";

foreach ($quizzes as $quiz) {
    // Afficher le statut des quiz
    $quizStatus = ($quiz['status'] == 'actif') ? 'Actif' : 'Désactivé';
    echo "<tr>
            <td>{$quiz['title']}</td>
            <td>{$quizStatus}</td>
            <td>{$quiz['responses_count']}</td>
            <td><a href='admin_action.php?action=toggle_quiz_status&id={$quiz['id']}'>Désactiver/Activer</a></td>
          </tr>";
}

echo "</table>";
?>
