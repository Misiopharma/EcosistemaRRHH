<?php
class UserModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getUserByUsername($username) {
        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE usuario = :username");
        $stmt->execute(['username' => $username]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateUserSessionToken($userId, $sessionToken) {
        $stmt = $this->pdo->prepare("UPDATE usuarios SET session_token = :session_token WHERE id = :id");
        $stmt->execute(['session_token' => $sessionToken, 'id' => $userId]);
    }

    public function clearUserSessionToken($userId) {
        $stmt = $this->pdo->prepare("UPDATE usuarios SET session_token = NULL WHERE id = :id");
        $stmt->execute(['id' => $userId]);
    }
}
?>