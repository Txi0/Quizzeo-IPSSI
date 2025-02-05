<?php
// includes/dashboard.php
class Dashboard {
    private $db;

    public function __construct() {
        $this->db = new JsonDatabase('quizzes.json');
    }

    public function getAdminStats() {
        $users = (new JsonDatabase('users.json'))->getAll();
        $quizzes = $this->db->getAll();

        return [
            'total_users' => count($users),
            'connected_users' => array_filter($users, fn($u) => isset($u['last_activity']) && (time() - strtotime($u['last_activity']) < 300)),
            'total_quizzes' => count($quizzes),
            'quizzes_by_status' => array_reduce($quizzes, function($acc, $quiz) {
                $acc[$quiz['status']] = ($acc[$quiz['status']] ?? 0) + 1;
                return $acc;
            }, [])
        ];
    }

    public function getSchoolQuizzes($userId) {
        $quizzes = $this->db->getAll();
        return array_filter($quizzes, fn($q) => $q['creator_id'] === $userId && $q['type'] === 'school');
    }

    public function getBusinessQuizzes($userId) {
        $quizzes = $this->db->getAll();
        return array_filter($quizzes, fn($q) => $q['creator_id'] === $userId && $q['type'] === 'business');
    }

    public function getUserQuizzes($userId) {
        $responses = (new JsonDatabase('responses.json'))->getAll();
        return array_filter($responses, fn($r) => $r['user_id'] === $userId);
    }
}