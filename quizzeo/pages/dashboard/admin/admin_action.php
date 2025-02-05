<?php
session_start();

// Vérification de l'action et de l'ID
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $id = $_GET['id'];

    // Vérifier si l'action est 'toggle_user_status'
    if ($action == 'toggle_user_status') {
        // Charger le fichier des utilisateurs
        $users = json_decode(file_get_contents(__DIR__ . '/../../../data/users.json'), true);

        foreach ($users as &$user) {
            if ($user['id'] == $id) {
                // Inverser le statut 'active' de l'utilisateur
                $user['active'] = !$user['active'];  // Si 'active' est true, il devient false et vice versa

                // Sauvegarder les modifications dans le fichier users.json
                file_put_contents(__DIR__ . '/../../../data/users.json', json_encode($users, JSON_PRETTY_PRINT));

                // Rediriger vers la page admin après la mise à jour
                header('Location: admin.php');
                exit;
            }
        }

        // Si l'utilisateur n'est pas trouvé
        echo "Utilisateur introuvable.";
        exit;
    }

    // Vérifier si l'action est 'toggle_quiz_status'
    if ($action == 'toggle_quiz_status') {
        // Charger le fichier des quiz
        $quizzes = json_decode(file_get_contents(__DIR__ . '/../../../data/quizzes.json'), true);

        foreach ($quizzes as &$quiz) {
            if ($quiz['id'] == $id) {
                // Inverser le statut 'status' du quiz
                $quiz['status'] = ($quiz['status'] == 'actif') ? 'désactivé' : 'actif';  // Changer l'état du statut

                // Sauvegarder les modifications dans le fichier quizzes.json
                file_put_contents(__DIR__ . '/../../../data/quizzes.json', json_encode($quizzes, JSON_PRETTY_PRINT));

                // Rediriger vers la page admin après la mise à jour
                header('Location: admin.php');
                exit;
            }
        }

        // Si le quiz n'est pas trouvé
        echo "Quiz introuvable.";
        exit;
    }
}
?>
