<?php
// pages/dashboard/ecole/update-quiz-status.php
session_start();
require_once '../../../includes/auth.php';

// Vérification du rôle
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'ecole') {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}

// Récupération des données
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['quiz_id']) || !isset($data['status'])) {
    echo json_encode(['success' => false, 'message' => 'Données manquantes']);
    exit;
}

$quizDb = new JsonDatabase('quizzes.json');
$quiz = $quizDb->findById($data['quiz_id']);

// Vérifier que le quiz appartient à l'école
if (!$quiz || $quiz['user_id'] !== $_SESSION['user']['id']) {
    echo json_encode(['success' => false, 'message' => 'Quiz non trouvé']);
    exit;
}

// Mise à jour du statut
if ($quizDb->update($data['quiz_id'], ['status' => $data['status']])) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour']);
}