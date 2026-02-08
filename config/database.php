<?php
/**
 * Конфигурация базы данных
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'figura_db');
define('DB_USER', 'root');
define('DB_PASS', ''); // Укажите пароль для продакшена

// Настройки сайта
define('SITE_NAME', 'Figura - 3D Фигурки');
define('SITE_URL', 'http://localhost/figura-site'); // Изменить для продакшена

// Пути для загрузки файлов
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('UPLOAD_IMAGES', UPLOAD_PATH . 'images/');
define('UPLOAD_FILES', UPLOAD_PATH . 'files/');

// Максимальные размеры файлов (в байтах)
define('MAX_IMAGE_SIZE', 5 * 1024 * 1024);  // 5 MB
define('MAX_FILE_SIZE', 100 * 1024 * 1024); // 100 MB

// Разрешённые типы файлов
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
define('ALLOWED_FILE_EXTENSIONS', ['stl', 'obj', 'fbx', '3ds', 'blend', 'zip', 'rar', '7z']);

// Пагинация
define('ITEMS_PER_PAGE', 12);
define('NEWS_PER_PAGE', 10);

/**
 * Подключение к базе данных
 */
function getDB(): PDO {
    static $pdo = null;

    if ($pdo === null) {
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die('Ошибка подключения к базе данных: ' . $e->getMessage());
        }
    }

    return $pdo;
}
