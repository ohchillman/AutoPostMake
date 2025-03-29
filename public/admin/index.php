<?php
// Входная точка для главной страницы админ-панели
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/models/Database.php';
require_once __DIR__ . '/../../app/models/User.php';
require_once __DIR__ . '/../../app/models/NewsSource.php';
require_once __DIR__ . '/../../app/models/ApiToken.php';
require_once __DIR__ . '/../../app/controllers/Controller.php';

// Запускаем сессию
session_start();

// Проверяем авторизацию
$user = new User();
if (!$user->isLoggedIn()) {
    header('Location: /login.php');
    exit;
}

// Получаем данные для дашборда
$newsSource = new NewsSource();
$apiToken = new ApiToken();

$sourcesCount = count($newsSource->getAllSources());
$activeSourcesCount = $newsSource->getActiveSourcesCount();

// Получаем статистику по новостям и рерайтам
$db = Database::getInstance();
$newsCount = $db->selectOne("SELECT COUNT(*) as count FROM news")['count'] ?? 0;
$rewritesCount = $db->selectOne("SELECT COUNT(*) as count FROM news_rewrites")['count'] ?? 0;

// Получаем последние логи парсинга
$logs = $db->select("
    SELECT l.*, s.name as source_name 
    FROM parsing_logs l 
    JOIN news_sources s ON l.source_id = s.id 
    ORDER BY l.created_at DESC 
    LIMIT 5
");

// Проверяем наличие токена make.com
$makeComToken = $apiToken->getTokenByService('make.com');

// Отображаем шаблон
$pageTitle = 'Панель управления';
$currentPage = 'dashboard';

// Начинаем буферизацию вывода для контента
ob_start();
include __DIR__ . '/../../app/views/admin/dashboard.php';
$content = ob_get_clean();

// Отображаем макет с контентом
include __DIR__ . '/../../app/views/admin/layout.php';
?>
