<?php
// Класс для работы с API токенами
class ApiToken {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAllTokens() {
        return $this->db->select("SELECT * FROM api_tokens ORDER BY service");
    }

    public function getTokenByService($service) {
        return $this->db->selectOne("SELECT * FROM api_tokens WHERE service = :service", ['service' => $service]);
    }

    public function addToken($service, $token) {
        // Проверяем, существует ли уже токен для этого сервиса
        $existingToken = $this->getTokenByService($service);
        
        if ($existingToken) {
            // Если токен существует, обновляем его
            $this->db->update('api_tokens', ['token' => $token], 'service = :service', ['service' => $service]);
            return $existingToken['id'];
        } else {
            // Если токена нет, добавляем новый
            return $this->db->insert('api_tokens', [
                'service' => $service,
                'token' => $token
            ]);
        }
    }

    public function updateToken($id, $token) {
        $this->db->update('api_tokens', ['token' => $token], 'id = :id', ['id' => $id]);
    }

    public function deleteToken($id) {
        $this->db->delete('api_tokens', 'id = :id', ['id' => $id]);
    }

    public function hasMakeComToken() {
        $token = $this->getTokenByService('make.com');
        return !empty($token);
    }

    public function getMakeComToken() {
        $token = $this->getTokenByService('make.com');
        return $token ? $token['token'] : null;
    }
}
?>
