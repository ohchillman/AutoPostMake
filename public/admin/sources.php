<?php
// Входная точка для управления источниками новостей
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/models/Database.php';
require_once __DIR__ . '/../../app/models/User.php';
require_once __DIR__ . '/../../app/models/NewsSource.php';
require_once __DIR__ . '/../../app/controllers/Controller.php';
require_once __DIR__ . '/../../app/controllers/NewsSourceController.php';

// Запускаем сессию
session_start();

// Проверяем авторизацию
$user = new User();
if (!$user->isLoggedIn()) {
    header('Location: /login.php');
    exit;
}

// Создаем экземпляр контроллера
$controller = new NewsSourceController();

// Определяем действие
$action = $_GET['action'] ?? 'index';
$id = $_GET['id'] ?? null;

// Выполняем соответствующее действие
switch ($action) {
    case 'create':
        $pageTitle = 'Добавление источника новостей';
        $currentPage = 'sources';
        
        // Начинаем буферизацию вывода для контента
        ob_start();
        include __DIR__ . '/../../app/views/admin/sources/create.php';
        $content = ob_get_clean();
        break;
        
    case 'store':
        $controller->store();
        break;
        
    case 'edit':
        if (!$id) {
            header('Location: /admin/sources.php');
            exit;
        }
        
        $pageTitle = 'Редактирование источника новостей';
        $currentPage = 'sources';
        
        // Начинаем буферизацию вывода для контента
        ob_start();
        echo $controller->edit($id);
        $content = ob_get_clean();
        break;
        
    case 'update':
        if (!$id) {
            header('Location: /admin/sources.php');
            exit;
        }
        
        $controller->update($id);
        break;
        
    case 'delete':
        if (!$id) {
            header('Location: /admin/sources.php');
            exit;
        }
        
        $controller->delete($id);
        break;
        
    case 'toggle':
        if (!$id) {
            header('Location: /admin/sources.php');
            exit;
        }
        
        $controller->toggleStatus($id);
        break;
        
    default:
        $pageTitle = 'Источники новостей';
        $currentPage = 'sources';
        
        // Получаем список источников
        $sources = (new NewsSource())->getAllSources();
        
        // Начинаем буферизацию вывода для контента
        ob_start();
        include __DIR__ . '/../../app/views/admin/sources/index.php';
        $content = ob_get_clean();
        break;
}

// Отображаем макет с контентом
include __DIR__ . '/../../app/views/admin/layout.php';
?>
