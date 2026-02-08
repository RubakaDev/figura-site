<?php
/**
 * Создание/редактирование новости
 */

require_once __DIR__ . '/includes/auth.php';
requireAuth();

$id = (int) ($_GET['id'] ?? 0);
$isEdit = $id > 0;
$pageTitle = $isEdit ? 'Редактирование новости' : 'Новая новость';

$news = null;
if ($isEdit) {
    $news = getNewsById($id);
    if (!$news) {
        setFlash('error', 'Новость не найдена');
        redirect(SITE_URL . '/admin/news.php');
    }
}

$errors = [];

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Ошибка безопасности. Попробуйте ещё раз.';
    } else {
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');

        if (empty($title)) {
            $errors[] = 'Введите заголовок';
        }
        if (empty($content)) {
            $errors[] = 'Введите содержание';
        }

        if (empty($errors)) {
            $pdo = getDB();

            if ($isEdit) {
                $stmt = $pdo->prepare('UPDATE news SET title = ?, content = ? WHERE id = ?');
                $stmt->execute([$title, $content, $id]);
                setFlash('success', 'Новость обновлена');
            } else {
                $stmt = $pdo->prepare('INSERT INTO news (title, content) VALUES (?, ?)');
                $stmt->execute([$title, $content]);
                setFlash('success', 'Новость создана');
            }

            redirect(SITE_URL . '/admin/news.php');
        }
    }
}

require_once __DIR__ . '/includes/admin-header.php';
$csrfToken = generateCsrfToken();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-newspaper"></i> <?= $isEdit ? 'Редактирование новости' : 'Новая новость' ?></h1>
    <a href="<?= SITE_URL ?>/admin/news.php" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Назад
    </a>
</div>

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
                <label for="title" class="form-label">Заголовок <span class="text-danger">*</span></label>
                <input type="text" name="title" id="title" class="form-control" required
                       value="<?= e($_POST['title'] ?? $news['title'] ?? '') ?>">
            </div>

            <div class="mb-3">
                <label for="content" class="form-label">Содержание <span class="text-danger">*</span></label>
                <textarea name="content" id="content" class="form-control" rows="10" required><?= e($_POST['content'] ?? $news['content'] ?? '') ?></textarea>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg"></i> <?= $isEdit ? 'Сохранить' : 'Создать' ?>
                </button>
                <a href="<?= SITE_URL ?>/admin/news.php" class="btn btn-outline-secondary">Отмена</a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
