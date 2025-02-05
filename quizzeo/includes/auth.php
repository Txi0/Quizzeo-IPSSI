<?php
// includes/auth.php
require_once __DIR__ . '/database.php';

class Auth {
    private $db;
    
    public function __construct() {
        $this->db = new JsonDatabase('users.json');
    }
    
    public function register($data) {
        // Vérifier si l'email existe déjà
        $users = $this->db->getAll();
        foreach ($users as $user) {
            if ($user['email'] === $data['email']) {
                return ['success' => false, 'message' => 'Cet email est déjà utilisé'];
            }
        }
        
        // Préparer les données de l'utilisateur
        $userData = [
            'id' => uniqid(),
            'email' => $data['email'],
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'role' => $data['role'],
            'nom' => $data['nom'],
            'prenom' => $data['prenom'],
            'active' => true, // Compte activé par défaut
            'created_at' => date('Y-m-d H:i:s')
        ];

        // Ajouter des champs spécifiques selon le rôle
        if ($data['role'] === 'ecole' && isset($data['nom_etablissement'])) {
            $userData['nom_etablissement'] = $data['nom_etablissement'];
        } elseif ($data['role'] === 'entreprise' && isset($data['nom_entreprise'])) {
            $userData['nom_entreprise'] = $data['nom_entreprise'];
        }
        
        // Sauvegarder l'utilisateur
        if ($this->db->insert($userData)) {
            return ['success' => true, 'message' => 'Inscription réussie'];
        }
        
        return ['success' => false, 'message' => 'Erreur lors de l\'enregistrement'];
    }
    
    public function login($email, $password) {
        $users = $this->db->getAll();
        foreach ($users as $user) {
            if ($user['email'] === $email && password_verify($password, $user['password'])) {
                // Vérifier si le compte est actif
                if (!$user['active']) {
                    return ['success' => false, 'message' => 'Ce compte est désactivé'];
                }
                
                // Créer la session pour l'utilisateur
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'email' => $user['email'],
                    'nom' => $user['nom'],
                    'prenom' => $user['prenom'],
                    'role' => $user['role']
                ];
                
                // Ajouter des champs spécifiques en fonction du rôle
                if ($user['role'] === 'ecole' && isset($user['nom_etablissement'])) {
                    $_SESSION['user']['nom_etablissement'] = $user['nom_etablissement'];
                } elseif ($user['role'] === 'entreprise' && isset($user['nom_entreprise'])) {
                    $_SESSION['user']['nom_entreprise'] = $user['nom_entreprise'];
                }
                
                return ['success' => true];
            }
        }
        
        return ['success' => false, 'message' => 'Email ou mot de passe incorrect'];
    }

    public function logout() {
        session_destroy();
    }

    public function isLoggedIn() {
        return isset($_SESSION['user']);
    }

    public function getCurrentUser() {
        return $_SESSION['user'] ?? null;
    }
}
?>
