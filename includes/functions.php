<?php
/**
 * Вспомогательные функции
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Экранирование вывода HTML
 */
function e(string $string): string {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Генерация CSRF-токена
 */
function generateCsrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Проверка CSRF-токена
 */
function verifyCsrfToken(string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Форматирование даты
 */
function formatDate(string $date, string $format = 'd.m.Y H:i'): string {
    return date($format, strtotime($date));
}

/**
 * Форматирование размера файла
 */
function formatFileSize(int $bytes): string {
    $units = ['Б', 'КБ', 'МБ', 'ГБ'];
    $i = 0;
    while ($bytes >= 1024 && $i < count($units) - 1) {
        $bytes /= 1024;
        $i++;
    }
    return round($bytes, 2) . ' ' . $units[$i];
}

/**
 * Получение новостей с пагинацией
 */
function getNews(int $page = 1, int $perPage = NEWS_PER_PAGE): array {
    $pdo = getDB();
    $offset = ($page - 1) * $perPage;

    $stmt = $pdo->prepare('SELECT * FROM news ORDER BY created_at DESC LIMIT :limit OFFSET :offset');
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}

/**
 * Получение общего количества новостей
 */
function getNewsCount(): int {
    $pdo = getDB();
    return (int) $pdo->query('SELECT COUNT(*) FROM news')->fetchColumn();
}

/**
 * Получение одной новости по ID
 */
function getNewsById(int $id): ?array {
    $pdo = getDB();
    $stmt = $pdo->prepare('SELECT * FROM news WHERE id = ?');
    $stmt->execute([$id]);
    $news = $stmt->fetch();
    return $news ?: null;
}

/**
 * Получение фигурок с пагинацией
 */
function getFigures(int $page = 1, int $perPage = ITEMS_PER_PAGE, string $search = ''): array {
    $pdo = getDB();
    $offset = ($page - 1) * $perPage;

    $sql = 'SELECT * FROM figures';
    $params = [];

    if ($search) {
        $sql .= ' WHERE name LIKE :search1 OR description LIKE :search2';
        $params[':search1'] = '%' . $search . '%';
        $params[':search2'] = '%' . $search . '%';
    }

    $sql .= ' ORDER BY created_at DESC LIMIT :limit OFFSET :offset';

    $stmt = $pdo->prepare($sql);

    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Получение общего количества фигурок
 */
function getFiguresCount(string $search = ''): int {
    $pdo = getDB();

    if ($search) {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM figures WHERE name LIKE ? OR description LIKE ?');
        $searchParam = '%' . $search . '%';
        $stmt->execute([$searchParam, $searchParam]);
    } else {
        $stmt = $pdo->query('SELECT COUNT(*) FROM figures');
    }

    return (int) $stmt->fetchColumn();
}

/**
 * Получение одной фигурки по ID
 */
function getFigureById(int $id): ?array {
    $pdo = getDB();
    $stmt = $pdo->prepare('SELECT * FROM figures WHERE id = ?');
    $stmt->execute([$id]);
    $figure = $stmt->fetch();
    return $figure ?: null;
}

/**
 * Получение дополнительных изображений фигурки
 */
function getFigureImages(int $figureId): array {
    $pdo = getDB();
    $stmt = $pdo->prepare('SELECT * FROM figure_images WHERE figure_id = ? ORDER BY sort_order');
    $stmt->execute([$figureId]);
    return $stmt->fetchAll();
}

/**
 * Увеличение счётчика скачиваний
 */
function incrementDownloads(int $figureId): void {
    $pdo = getDB();
    $stmt = $pdo->prepare('UPDATE figures SET downloads_count = downloads_count + 1 WHERE id = ?');
    $stmt->execute([$figureId]);
}

/**
 * Загрузка изображения
 */
function uploadImage(array $file): ?string {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    if (!in_array($file['type'], ALLOWED_IMAGE_TYPES)) {
        return null;
    }

    if ($file['size'] > MAX_IMAGE_SIZE) {
        return null;
    }

    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('img_') . '.' . strtolower($extension);
    $destination = UPLOAD_IMAGES . $filename;

    if (!is_dir(UPLOAD_IMAGES)) {
        mkdir(UPLOAD_IMAGES, 0755, true);
    }

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return 'uploads/images/' . $filename;
    }

    return null;
}

/**
 * Загрузка 3D-файла
 */
function uploadFile(array $file): ?array {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($extension, ALLOWED_FILE_EXTENSIONS)) {
        return null;
    }

    if ($file['size'] > MAX_FILE_SIZE) {
        return null;
    }

    $filename = uniqid('file_') . '.' . $extension;
    $destination = UPLOAD_FILES . $filename;

    if (!is_dir(UPLOAD_FILES)) {
        mkdir(UPLOAD_FILES, 0755, true);
    }

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return [
            'path' => 'uploads/files/' . $filename,
            'size' => $file['size']
        ];
    }

    return null;
}

/**
 * Удаление файла
 */
function deleteFile(string $path): bool {
    $fullPath = __DIR__ . '/../' . $path;
    if (file_exists($fullPath)) {
        return unlink($fullPath);
    }
    return false;
}

/**
 * Генерация пагинации
 */
function getPagination(int $currentPage, int $totalItems, int $perPage, string $baseUrl): array {
    $totalPages = ceil($totalItems / $perPage);
    $pages = [];

    for ($i = 1; $i <= $totalPages; $i++) {
        $pages[] = [
            'number' => $i,
            'url' => $baseUrl . (strpos($baseUrl, '?') !== false ? '&' : '?') . 'page=' . $i,
            'current' => $i === $currentPage
        ];
    }

    return [
        'pages' => $pages,
        'total' => $totalPages,
        'current' => $currentPage,
        'hasPrev' => $currentPage > 1,
        'hasNext' => $currentPage < $totalPages,
        'prevUrl' => $currentPage > 1 ? $baseUrl . (strpos($baseUrl, '?') !== false ? '&' : '?') . 'page=' . ($currentPage - 1) : null,
        'nextUrl' => $currentPage < $totalPages ? $baseUrl . (strpos($baseUrl, '?') !== false ? '&' : '?') . 'page=' . ($currentPage + 1) : null
    ];
}

/**
 * Редирект
 */
function redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}

/**
 * Установка flash-сообщения
 */
function setFlash(string $type, string $message): void {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Получение и удаление flash-сообщения
 */
function getFlash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
