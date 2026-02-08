<?php
/**
 * Управление фигурками
 */

$pageTitle = 'Фигурки';
require_once __DIR__ . '/includes/admin-header.php';

// Удаление фигурки
if (isset($_GET['delete']) && isset($_GET['token'])) {
    if (verifyCsrfToken($_GET['token'])) {
        $id = (int) $_GET['delete'];
        $figure = getFigureById($id);

        if ($figure) {
            // Удаляем файлы
            if ($figure['image_path']) {
                deleteFile($figure['image_path']);
            }
            if ($figure['file_path']) {
                deleteFile($figure['file_path']);
            }

            // Удаляем дополнительные изображения
            $images = getFigureImages($id);
            foreach ($images as $img) {
                deleteFile($img['image_path']);
            }

            // Удаляем из БД
            $pdo = getDB();
            $stmt = $pdo->prepare('DELETE FROM figures WHERE id = ?');
            $stmt->execute([$id]);

            setFlash('success', 'Фигурка удалена');
        }
    }
    redirect(SITE_URL . '/admin/figures.php');
}

$page = max(1, (int) ($_GET['page'] ?? 1));
$search = trim($_GET['search'] ?? '');
$figures = getFigures($page, 20, $search);
$totalFigures = getFiguresCount($search);
$baseUrl = SITE_URL . '/admin/figures.php' . ($search ? '?search=' . urlencode($search) : '');
$pagination = getPagination($page, $totalFigures, 20, $baseUrl);
$csrfToken = generateCsrfToken();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-box"></i> Фигурки</h1>
    <a href="<?= SITE_URL ?>/admin/figures-edit.php" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Добавить фигурку
    </a>
</div>

<!-- Поиск -->
<div class="card mb-4">
    <div class="card-body">
        <form class="row g-3" method="GET">
            <div class="col-md-10">
                <input type="search" name="search" class="form-control"
                       placeholder="Поиск по названию..." value="<?= e($search) ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary w-100">
                    <i class="bi bi-search"></i> Найти
                </button>
            </div>
        </form>
    </div>
</div>

<?php if ($search): ?>
    <p class="text-muted mb-3">
        Результаты поиска: найдено <?= $totalFigures ?>
        <a href="<?= SITE_URL ?>/admin/figures.php" class="ms-2">Сбросить</a>
    </p>
<?php endif; ?>

<?php if (empty($figures)): ?>
    <div class="card">
        <div class="card-body empty-state">
            <i class="bi bi-box-seam"></i>
            <h5>Фигурок пока нет</h5>
            <p>Добавьте первую фигурку в каталог</p>
            <a href="<?= SITE_URL ?>/admin/figures-edit.php" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Добавить фигурку
            </a>
        </div>
    </div>
<?php else: ?>
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th width="60">Фото</th>
                        <th>Название</th>
                        <th>Файл</th>
                        <th>Скачиваний</th>
                        <th>Дата</th>
                        <th width="150">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($figures as $figure): ?>
                        <tr>
                            <td>
                                <?php if ($figure['image_path']): ?>
                                    <img src="<?= SITE_URL ?>/<?= e($figure['image_path']) ?>"
                                         alt="" class="rounded" style="width: 50px; height: 50px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center"
                                         style="width: 50px; height: 50px;">
                                        <i class="bi bi-box text-muted"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?= SITE_URL ?>/admin/figures-edit.php?id=<?= $figure['id'] ?>">
                                    <?= e($figure['name']) ?>
                                </a>
                            </td>
                            <td>
                                <?php if ($figure['file_path']): ?>
                                    <span class="badge bg-success">
                                        <i class="bi bi-check"></i> <?= formatFileSize($figure['file_size']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Нет файла</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-primary"><?= $figure['downloads_count'] ?></span>
                            </td>
                            <td><?= formatDate($figure['created_at'], 'd.m.Y') ?></td>
                            <td>
                                <a href="<?= SITE_URL ?>/figure.php?id=<?= $figure['id'] ?>"
                                   class="btn btn-sm btn-outline-secondary" title="Просмотр" target="_blank">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="<?= SITE_URL ?>/admin/figures-edit.php?id=<?= $figure['id'] ?>"
                                   class="btn btn-sm btn-outline-primary" title="Редактировать">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="<?= SITE_URL ?>/admin/figures.php?delete=<?= $figure['id'] ?>&token=<?= $csrfToken ?>"
                                   class="btn btn-sm btn-outline-danger"
                                   data-confirm="Удалить фигурку '<?= e($figure['name']) ?>'? Все файлы также будут удалены."
                                   title="Удалить">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if ($pagination['total'] > 1): ?>
        <nav class="mt-4">
            <ul class="pagination justify-content-center">
                <?php if ($pagination['hasPrev']): ?>
                    <li class="page-item">
                        <a class="page-link" href="<?= $pagination['prevUrl'] ?>">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                    </li>
                <?php endif; ?>

                <?php foreach ($pagination['pages'] as $p): ?>
                    <li class="page-item <?= $p['current'] ? 'active' : '' ?>">
                        <a class="page-link" href="<?= $p['url'] ?>"><?= $p['number'] ?></a>
                    </li>
                <?php endforeach; ?>

                <?php if ($pagination['hasNext']): ?>
                    <li class="page-item">
                        <a class="page-link" href="<?= $pagination['nextUrl'] ?>">
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
