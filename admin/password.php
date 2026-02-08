<?php
/**
 * Смена пароля администратора
 */

$pageTitle = 'Смена пароля';
require_once __DIR__ . '/includes/admin-header.php';

$errors = [];
$success = false;

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Ошибка безопасности. Попробуйте ещё раз.';
    } else {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Получаем текущий хеш пароля
        $pdo = getDB();
        $stmt = $pdo->prepare('SELECT password_hash FROM admins WHERE id = ?');
        $stmt->execute([$_SESSION['admin_id']]);
        $admin = $stmt->fetch();

        // Валидация
        if (empty($currentPassword)) {
            $errors[] = 'Введите текущий пароль';
        } elseif (!password_verify($currentPassword, $admin['password_hash'])) {
            $errors[] = 'Неверный текущий пароль';
        }

        if (empty($newPassword)) {
            $errors[] = 'Введите новый пароль';
        } elseif (strlen($newPassword) < 6) {
            $errors[] = 'Новый пароль должен быть не менее 6 символов';
        }

        if ($newPassword !== $confirmPassword) {
            $errors[] = 'Пароли не совпадают';
        }

        if ($currentPassword === $newPassword) {
            $errors[] = 'Новый пароль должен отличаться от текущего';
        }

        // Сохранение нового пароля
        if (empty($errors)) {
            $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('UPDATE admins SET password_hash = ? WHERE id = ?');
            $stmt->execute([$newHash, $_SESSION['admin_id']]);

            $success = true;
        }
    }
}

$csrfToken = generateCsrfToken();
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <h1 class="mb-4"><i class="bi bi-key"></i> Смена пароля</h1>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="bi bi-check-circle"></i> Пароль успешно изменён!
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?= e($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

                    <div class="mb-3">
                        <label for="current_password" class="form-label">
                            Текущий пароль <span class="text-danger">*</span>
                        </label>
                        <input type="password" name="current_password" id="current_password"
                               class="form-control" required autocomplete="current-password">
                    </div>

                    <hr class="my-4">

                    <div class="mb-3">
                        <label for="new_password" class="form-label">
                            Новый пароль <span class="text-danger">*</span>
                        </label>
                        <input type="password" name="new_password" id="new_password"
                               class="form-control" required minlength="6" autocomplete="new-password">
                        <div class="form-text">Минимум 6 символов</div>
                    </div>

                    <div class="mb-4">
                        <label for="confirm_password" class="form-label">
                            Подтвердите новый пароль <span class="text-danger">*</span>
                        </label>
                        <input type="password" name="confirm_password" id="confirm_password"
                               class="form-control" required minlength="6" autocomplete="new-password">
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg"></i> Сменить пароль
                        </button>
                        <a href="<?= SITE_URL ?>/admin/" class="btn btn-outline-secondary">Отмена</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
