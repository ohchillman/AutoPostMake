<?php
// Выход из системы
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/models/Database.php';
require_once __DIR__ . '/../app/models/User.php';
require_once __DIR__ . '/../app/controllers/Controller.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';

// Запускаем сессию
session_start();

// Создаем экземпляр контроллера авторизации
$authController = new AuthController();

// Выполняем выход
$authController->logout();
?>
