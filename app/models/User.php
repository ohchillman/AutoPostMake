<?php
// Класс для работы с пользователями
class User {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function login($username, $password) {
        $user = $this->db->selectOne("SELECT * FROM users WHERE username = :username", [
            'username' => $username
        ]);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            return true;
        }
        return false;
    }

    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    public function logout() {
        unset($_SESSION['user_id']);
        unset($_SESSION['username']);
        session_destroy();
    }

    public function createUser($username, $password) {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => HASH_COST]);
        return $this->db->insert('users', [
            'username' => $username,
            'password' => $hashedPassword
        ]);
    }

    public function getUserById($id) {
        return $this->db->selectOne("SELECT * FROM users WHERE id = :id", ['id' => $id]);
    }
}
?>
