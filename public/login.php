<?php
// Входная точка для страницы авторизации
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/models/Database.php';
require_once __DIR__ . '/../app/models/User.php';
require_once __DIR__ . '/../app/controllers/Controller.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';

// Запускаем сессию
session_start();

// Создаем экземпляр контроллера авторизации
$authController = new AuthController();

// Проверяем, авторизован ли пользователь
$user = new User();
if ($user->isLoggedIn()) {
    // Если пользователь уже авторизован, перенаправляем на главную страницу админ-панели
    header('Location: /admin/index.php');
    exit;
}

// Создаем первого пользователя, если в системе еще нет пользователей
$authController->createInitialAdmin();

// Обрабатываем форму входа
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo $authController->login();
} else {
    // Отображаем страницу входа
    echo $authController->showLoginPage();
}
?>
