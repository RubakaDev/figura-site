<?php
/**
 * Проверка авторизации администратора
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

/**
 * Проверка авторизации
 */
function isLoggedIn(): bool {
    return isset($_SESSION['admin_id']);
}

/**
 * Требование авторизации (редирект если не авторизован)
 */
function requireAuth(): void {
    if (!isLoggedIn()) {
        redirect(SITE_URL . '/admin/login.php');
    }
}

/**
 * Авторизация администратора
 */
function loginAdmin(string $username, string $password): bool {
    $pdo = getDB();
    $stmt = $pdo->prepare('SELECT id, password_hash FROM admins WHERE username = ?');
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password_hash'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $username;
        return true;
    }

    return false;
}

/**
 * Выход администратора
 */
function logoutAdmin(): void {
    unset($_SESSION['admin_id'], $_SESSION['admin_username']);
    session_destroy();
}

/**
 * Получение текущего имени администратора
 */
function getAdminUsername(): string {
    return $_SESSION['admin_username'] ?? 'Гость';
}
