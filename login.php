<?php
/**
 * Страница входа пользователя
 */

session_start();
require_once __DIR__ . '/includes/functions.php';

// Если уже авторизован — редирект на главную
if (isUserLoggedIn()) {
    redirect(SITE_URL . '/');
}

$error = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Ошибка безопасности. Попробуйте ещё раз.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $error = 'Заполните все поля';
        } elseif (loginUser($username, $password)) {
            setFlash('success', 'Вы успешно вошли!');
            redirect(SITE_URL . '/');
        } else {
            $error = 'Неверный логин или пароль';
        }
    }
}

$pageTitle = 'Вход';
require_once __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h1 class="h4 text-center mb-4">
                    <i class="bi bi-box-arrow-in-right"></i> Вход
                </h1>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= e($error) ?></div>
                <?php endif; ?>

                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

                    <div class="mb-3">
                        <label for="username" class="form-label">Имя пользователя</label>
                        <input type="text" name="username" id="username"
                               class="form-control" required autofocus
                               value="<?= e($username) ?>">
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
                    <span class="text-muted">Нет аккаунта?</span>
                    <a href="<?= SITE_URL ?>/register.php">Зарегистрироваться</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
