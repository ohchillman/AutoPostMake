<?php
// Входная точка для управления парсингом и рерайтингом
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/models/Database.php';
require_once __DIR__ . '/../../app/models/User.php';
require_once __DIR__ . '/../../app/models/NewsSource.php';
require_once __DIR__ . '/../../app/models/ApiToken.php';
require_once __DIR__ . '/../../app/controllers/Controller.php';
require_once __DIR__ . '/../../app/controllers/ParserController.php';

// Запускаем сессию
session_start();

// Проверяем авторизацию
$user = new User();
if (!$user->isLoggedIn()) {
    header('Location: /login.php');
    exit;
}

// Создаем экземпляр контроллера
$controller = new ParserController();

// Определяем действие
$action = $_GET['action'] ?? 'index';
$id = $_GET['id'] ?? null;
$status = $_GET['status'] ?? null;

// Выполняем соответствующее действие
switch ($action) {
    case 'run':
        $controller->runParser($id);
        break;
        
    case 'rewrite':
        $controller->runRewriter();
        break;
        
    case 'setup_cron':
        $pageTitle = 'Настройка периодического парсинга';
        $currentPage = 'parser';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $content = $controller->setupCron();
        } else {
            // Начинаем буферизацию вывода для контента
            ob_start();
            include __DIR__ . '/../../app/views/admin/parser/cron.php';
            $content = ob_get_clean();
        }
        break;
        
    default:
        $pageTitle = 'Управление парсингом';
        $currentPage = 'parser';
        
        // Получаем активные источники
        $sources = (new NewsSource())->getActiveSources();
        
        // Получаем токен make.com
        $makeComToken = (new ApiToken())->hasMakeComToken();
        
        // Получаем последние логи парсинга
        $db = Database::getInstance();
        $logs = $db->select("
            SELECT l.*, s.name as source_name 
            FROM parsing_logs l 
            JOIN news_sources s ON l.source_id = s.id 
            ORDER BY l.created_at DESC 
            LIMIT 10
        ");
        
        // Проверяем статус
        $success = null;
        if ($status === 'started') {
            $success = 'Парсинг запущен. Результаты будут доступны в журнале парсинга.';
        } elseif ($status === 'rewrite_started') {
            $success = 'Рерайтинг запущен. Результаты будут сохранены в базе данных.';
        }
        
        // Начинаем буферизацию вывода для контента
        ob_start();
        include __DIR__ . '/../../app/views/admin/parser/index.php';
        $content = ob_get_clean();
        break;
}

// Отображаем макет с контентом
include __DIR__ . '/../../app/views/admin/layout.php';
?>
