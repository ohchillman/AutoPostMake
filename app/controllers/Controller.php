<?php
// Базовый класс контроллера
class Controller {
    protected $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
        
        // Запускаем сессию, если она еще не запущена
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    // Метод для рендеринга представлений
    protected function render($view, $data = []) {
        // Извлекаем переменные из массива данных
        extract($data);
        
        // Путь к файлу представления
        $viewPath = APP_ROOT . '/app/views/' . $view . '.php';
        
        // Проверяем существование файла представления
        if (file_exists($viewPath)) {
            // Начинаем буферизацию вывода
            ob_start();
            // Подключаем файл представления
            include $viewPath;
            // Получаем содержимое буфера и очищаем его
            $content = ob_get_clean();
            
            // Возвращаем содержимое
            return $content;
        } else {
            // Если файл представления не найден, выбрасываем исключение
            throw new Exception("View {$view} not found");
        }
    }
    
    // Метод для проверки авторизации пользователя
    protected function requireAuth() {
        $user = new User();
        if (!$user->isLoggedIn()) {
            header('Location: /login.php');
            exit;
        }
    }
    
    // Метод для перенаправления
    protected function redirect($url) {
        header("Location: {$url}");
        exit;
    }
    
    // Метод для отправки JSON-ответа
    protected function jsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
?>
