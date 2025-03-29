<?php
// Входная точка для получения рерайтов новости
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/models/Database.php';
require_once __DIR__ . '/../app/models/NewsSource.php';
require_once __DIR__ . '/../app/controllers/Controller.php';
require_once __DIR__ . '/../app/controllers/HomeController.php';

// Запускаем сессию
session_start();

// Проверяем наличие ID новости
$newsId = $_GET['id'] ?? null;
if (!$newsId) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Не указан ID новости']);
    exit;
}

// Создаем экземпляр контроллера
$controller = new HomeController();

// Получаем рерайты новости
header('Content-Type: application/json');
echo $controller->getRewrites($newsId);
?>
