<?php
/**
 * Скачивание файла фигурки
 */

require_once __DIR__ . '/includes/functions.php';

$id = (int) ($_GET['id'] ?? 0);
$figure = getFigureById($id);

if (!$figure || !$figure['file_path']) {
    http_response_code(404);
    die('Файл не найден');
}

$filePath = __DIR__ . '/' . $figure['file_path'];

if (!file_exists($filePath)) {
    http_response_code(404);
    die('Файл не найден на сервере');
}

// Увеличиваем счётчик скачиваний
incrementDownloads($id);

// Определяем имя файла для скачивания
$extension = pathinfo($figure['file_path'], PATHINFO_EXTENSION);
$downloadName = preg_replace('/[^a-zA-Z0-9а-яА-ЯёЁ\-_\.]/', '_', $figure['name']) . '.' . $extension;

// Отправляем файл
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $downloadName . '"');
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($filePath));

ob_clean();
flush();
readfile($filePath);
exit;
