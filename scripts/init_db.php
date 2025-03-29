<?php
// Файл для инициализации базы данных
require_once __DIR__ . '/../config/config.php';

// Подключение к MySQL
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST,
        DB_USER,
        DB_PASS,
        array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
    );
    
    // Создаем базу данных, если она не существует
    $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    
    // Выбираем базу данных
    $pdo->exec("USE " . DB_NAME);
    
    // Читаем SQL-скрипт для создания таблиц
    $sql = file_get_contents(__DIR__ . '/../database/schema.sql');
    
    // Выполняем SQL-скрипт
    $pdo->exec($sql);
    
    echo "База данных успешно инициализирована.\n";
    
} catch (PDOException $e) {
    die("Ошибка при инициализации базы данных: " . $e->getMessage() . "\n");
}
?>
