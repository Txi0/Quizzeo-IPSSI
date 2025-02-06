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
                $user['active'] = !$user['active'];  

                // Sauvegarder les modifications
                file_put_contents(__DIR__ . '/../../../data/users.json', json_encode($users, JSON_PRETTY_PRINT));

                header('Location: admin.php');
                exit;
            }
        }

        echo "Utilisateur introuvable.";
        exit;
    }

    // Vérifier si l'action est 'toggle_quiz_status'
    if ($action == 'toggle_quiz_status') {
        // Charger le fichier des quiz
        $quizzes = json_decode(file_get_contents(__DIR__ . '/../../../data/quizzes.json'), true);

        foreach ($quizzes as &$quiz) {
            if ($quiz['id'] == $id) {
                // Inverser le statut du quiz
                $quiz['status'] = ($quiz['status'] == 'actif') ? 'désactivé' : 'actif';

                // Sauvegarder les modifications
                file_put_contents(__DIR__ . '/../../../data/quizzes.json', json_encode($quizzes, JSON_PRETTY_PRINT));

                header('Location: admin.php');
                exit;
            }
        }

        echo "Quiz introuvable.";
        exit;
    }

    // Nouvelle action pour supprimer un quiz
    if ($action == 'delete_quiz') {
        // Charger le fichier des quiz
        $quizzes = json_decode(file_get_contents(__DIR__ . '/../../../data/quizzes.json'), true);

        // Trouver et supprimer le quiz
        foreach ($quizzes as $key => $quiz) {
            if ($quiz['id'] == $id) {
                // Supprimer le quiz du tableau
                unset($quizzes[$key]);

                // Réindexer le tableau
                $quizzes = array_values($quizzes);

                // Sauvegarder les modifications
                if (file_put_contents(__DIR__ . '/../../../data/quizzes.json', json_encode($quizzes, JSON_PRETTY_PRINT))) {
                    $_SESSION['success'] = "Le quiz a été supprimé avec succès.";
                } else {
                    $_SESSION['error'] = "Erreur lors de la suppression du quiz.";
                }

                header('Location: admin.php');
                exit;
            }
        }

        $_SESSION['error'] = "Quiz introuvable.";
        header('Location: admin.php');
        exit;
    }
}

// Si aucune action valide n'est trouvée
header('Location: admin.php');
exit;
?>