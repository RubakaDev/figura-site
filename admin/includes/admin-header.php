<?php
require_once __DIR__ . '/auth.php';
requireAuth();

$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? e($pageTitle) . ' | ' : '' ?>Админ-панель</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= SITE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= SITE_URL ?>/admin/">
                <i class="bi bi-gear"></i> Админ-панель
            </a>
            <div class="d-flex align-items-center">
                <span class="text-light me-3">
                    <i class="bi bi-person"></i> <?= e(getAdminUsername()) ?>
                </span>
                <a href="<?= SITE_URL ?>/" class="btn btn-outline-light btn-sm me-2">
                    <i class="bi bi-eye"></i> Сайт
                </a>
                <a href="<?= SITE_URL ?>/admin/logout.php" class="btn btn-outline-danger btn-sm">
                    <i class="bi bi-box-arrow-right"></i> Выход
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Боковое меню -->
            <nav class="col-md-3 col-lg-2 d-md-block bg-dark admin-sidebar py-3">
                <ul class="nav flex-column admin-nav">
                    <li class="nav-item">
                        <a class="nav-link <?= $currentPage === 'index.php' ? 'active' : '' ?>"
                           href="<?= SITE_URL ?>/admin/">
                            <i class="bi bi-speedometer2"></i> Дашборд
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= in_array($currentPage, ['news.php', 'news-edit.php']) ? 'active' : '' ?>"
                           href="<?= SITE_URL ?>/admin/news.php">
                            <i class="bi bi-newspaper"></i> Новости
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= in_array($currentPage, ['figures.php', 'figures-edit.php']) ? 'active' : '' ?>"
                           href="<?= SITE_URL ?>/admin/figures.php">
                            <i class="bi bi-box"></i> Фигурки
                        </a>
                    </li>
                    <li class="nav-item mt-3 pt-3 border-top border-secondary">
                        <a class="nav-link <?= $currentPage === 'password.php' ? 'active' : '' ?>"
                           href="<?= SITE_URL ?>/admin/password.php">
                            <i class="bi bi-key"></i> Сменить пароль
                        </a>
                    </li>
                </ul>
            </nav>

            <!-- Основной контент -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 admin-content py-4">
                <?php if ($flash = getFlash()): ?>
                    <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : $flash['type'] ?> alert-dismissible fade show">
                        <?= e($flash['message']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
