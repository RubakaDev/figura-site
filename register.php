<?php
/**
 * Страница регистрации пользователя
 */

session_start();
require_once __DIR__ . '/includes/functions.php';

// Если уже авторизован — редирект на главную
if (isUserLoggedIn()) {
    redirect(SITE_URL . '/');
}

$error = '';
$username = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Ошибка безопасности. Попробуйте ещё раз.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';

        if (empty($username) || empty($email) || empty($password)) {
            $error = 'Заполните все поля';
        } elseif (mb_strlen($username) < 3 || mb_strlen($username) > 50) {
            $error = 'Имя пользователя должно быть от 3 до 50 символов';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Введите корректный email';
        } elseif (mb_strlen($password) < 6) {
            $error = 'Пароль должен быть не менее 6 символов';
        } elseif ($password !== $passwordConfirm) {
            $error = 'Пароли не совпадают';
        } else {
            $result = registerUser($username, $email, $password);

            if ($result['success']) {
                // Автоматический вход после регистрации
                $_SESSION['user_id'] = $result['user_id'];
                $_SESSION['user_username'] = $username;
                setFlash('success', 'Регистрация прошла успешно! Добро пожаловать!');
                redirect(SITE_URL . '/');
            } else {
                $error = $result['error'];
            }
        }
    }
}

$pageTitle = 'Регистрация';
require_once __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h1 class="h4 text-center mb-4">
                    <i class="bi bi-person-plus"></i> Регистрация
                </h1>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= e($error) ?></div>
                <?php endif; ?>

                <form method="POST" id="register-form">
                    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

                    <div class="mb-3">
                        <label for="username" class="form-label">Имя пользователя</label>
                        <input type="text" name="username" id="username"
                               class="form-control" required autofocus
                               minlength="3" maxlength="50"
                               value="<?= e($username) ?>">
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" name="email" id="email"
                               class="form-control" required
                               value="<?= e($email) ?>">
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Пароль</label>
                        <input type="password" name="password" id="password"
                               class="form-control" required minlength="6">
                        <div class="form-text">Минимум 6 символов</div>
                    </div>

                    <div class="mb-3">
                        <label for="password_confirm" class="form-label">Подтвердите пароль</label>
                        <input type="password" name="password_confirm" id="password_confirm"
                               class="form-control" required minlength="6">
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-person-plus"></i> Зарегистрироваться
                    </button>
                </form>

                <div class="text-center mt-3">
                    <span class="text-muted">Уже есть аккаунт?</span>
                    <a href="<?= SITE_URL ?>/login.php">Войти</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
