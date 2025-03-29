<?php
// Входная точка для публичной части сайта
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/models/Database.php';
require_once __DIR__ . '/../app/models/NewsSource.php';
require_once __DIR__ . '/../app/controllers/Controller.php';
require_once __DIR__ . '/../app/controllers/HomeController.php';

// Запускаем сессию
session_start();

// Создаем экземпляр контроллера
$controller = new HomeController();

// Отображаем главную страницу
echo $controller->index();
?>
