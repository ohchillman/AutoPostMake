<?php
// Конфигурация базы данных
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'autopostmake');
define('DB_USER', 'root');
define('DB_PASS', '');

// Конфигурация приложения
define('APP_NAME', 'AutoPostMake');
define('APP_URL', 'http://localhost');
define('APP_ROOT', dirname(__DIR__));

// Конфигурация сессий
define('SESSION_LIFETIME', 86400); // 24 часа

// Конфигурация для Python-скриптов
define('PYTHON_PATH', '/usr/bin/python3');
define('SCRIPTS_DIR', APP_ROOT . '/scripts');

// Настройки безопасности
define('HASH_COST', 10); // Стоимость хеширования для bcrypt
?>
