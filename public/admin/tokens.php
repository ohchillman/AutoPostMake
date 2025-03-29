<?php
// Входная точка для управления API токенами
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/models/Database.php';
require_once __DIR__ . '/../../app/models/User.php';
require_once __DIR__ . '/../../app/models/ApiToken.php';
require_once __DIR__ . '/../../app/controllers/Controller.php';
require_once __DIR__ . '/../../app/controllers/ApiTokenController.php';

// Запускаем сессию
session_start();

// Проверяем авторизацию
$user = new User();
if (!$user->isLoggedIn()) {
    header('Location: /login.php');
    exit;
}

// Создаем экземпляр контроллера
$controller = new ApiTokenController();

// Определяем действие
$action = $_GET['action'] ?? 'index';
$id = $_GET['id'] ?? null;

// Выполняем соответствующее действие
switch ($action) {
    case 'save_make_com':
        $result = $controller->saveMakeComToken();
        if ($result) {
            $content = $result;
        } else {
            header('Location: /admin/tokens.php');
            exit;
        }
        break;
        
    case 'delete':
        if (!$id) {
            header('Location: /admin/tokens.php');
            exit;
        }
        
        $controller->deleteToken($id);
        break;
        
    default:
        $pageTitle = 'API Токены';
        $currentPage = 'tokens';
        
        // Получаем токен make.com
        $makeComToken = (new ApiToken())->getTokenByService('make.com');
        
        // Начинаем буферизацию вывода для контента
        ob_start();
        include __DIR__ . '/../../app/views/admin/tokens/index.php';
        $content = ob_get_clean();
        break;
}

// Отображаем макет с контентом
include __DIR__ . '/../../app/views/admin/layout.php';
?>
