<?php
/**
 * Выход пользователя
 */

session_start();
require_once __DIR__ . '/includes/functions.php';

logoutUser();
setFlash('success', 'Вы вышли из аккаунта');
redirect(SITE_URL . '/');
