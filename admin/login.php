<?php
/**
 * Страница входа в админ-панель
 */

session_start();
require_once __DIR__ . '/includes/auth.php';

// Если уже авторизован - редирект на дашборд
if (isLoggedIn()) {
    redirect(SITE_URL . '/admin/');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Заполните все поля';
    } elseif (loginAdmin($username, $password)) {
        redirect(SITE_URL . '/admin/');
    } else {
        $error = 'Неверный логин или пароль';
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход | Админ-панель</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            height: 100vh;
            display: flex;
            align-items: center;
            background-color: #f5f5f5;
        }
        .login-form {
            max-width: 400px;
            padding: 2rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-form mx-auto">
            <div class="card shadow">
                <div class="card-body">
                    <h1 class="h4 text-center mb-4">
                        <i class="bi bi-lock"></i> Вход в админ-панель
                    </h1>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= e($error) ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label for="username" class="form-label">Логин</label>
                            <input type="text" name="username" id="username"
                                   class="form-control" required autofocus
                                   value="<?= e($_POST['username'] ?? '') ?>">
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Пароль</label>
                            <input type="password" name="password" id="password"
                                   class="form-control" required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-box-arrow-in-right"></i> Войти
                        </button>
                    </form>

                    <div class="text-center mt-3">
                        <a href="<?= SITE_URL ?>/" class="text-muted">
                            <i class="bi bi-arrow-left"></i> Вернуться на сайт
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
