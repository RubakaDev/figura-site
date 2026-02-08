<?php
/**
 * Управление новостями
 */

$pageTitle = 'Новости';
require_once __DIR__ . '/includes/admin-header.php';

// Удаление новости
if (isset($_GET['delete']) && isset($_GET['token'])) {
    if (verifyCsrfToken($_GET['token'])) {
        $id = (int) $_GET['delete'];
        $pdo = getDB();
        $stmt = $pdo->prepare('DELETE FROM news WHERE id = ?');
        $stmt->execute([$id]);
        setFlash('success', 'Новость удалена');
    }
    redirect(SITE_URL . '/admin/news.php');
}

$page = max(1, (int) ($_GET['page'] ?? 1));
$news = getNews($page, 20);
$totalNews = getNewsCount();
$pagination = getPagination($page, $totalNews, 20, SITE_URL . '/admin/news.php');
$csrfToken = generateCsrfToken();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-newspaper"></i> Новости</h1>
    <a href="<?= SITE_URL ?>/admin/news-edit.php" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Добавить новость
    </a>
</div>

<?php if (empty($news)): ?>
    <div class="card">
        <div class="card-body empty-state">
            <i class="bi bi-inbox"></i>
            <h5>Новостей пока нет</h5>
            <p>Создайте первую новость для вашего сайта</p>
            <a href="<?= SITE_URL ?>/admin/news-edit.php" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Добавить новость
            </a>
        </div>
    </div>
<?php else: ?>
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Заголовок</th>
                        <th>Дата создания</th>
                        <th>Дата изменения</th>
                        <th width="150">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($news as $item): ?>
                        <tr>
                            <td><?= $item['id'] ?></td>
                            <td>
                                <a href="<?= SITE_URL ?>/admin/news-edit.php?id=<?= $item['id'] ?>">
                                    <?= e($item['title']) ?>
                                </a>
                            </td>
                            <td><?= formatDate($item['created_at']) ?></td>
                            <td><?= formatDate($item['updated_at']) ?></td>
                            <td>
                                <a href="<?= SITE_URL ?>/admin/news-edit.php?id=<?= $item['id'] ?>"
                                   class="btn btn-sm btn-outline-primary" title="Редактировать">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="<?= SITE_URL ?>/admin/news.php?delete=<?= $item['id'] ?>&token=<?= $csrfToken ?>"
                                   class="btn btn-sm btn-outline-danger"
                                   data-confirm="Удалить новость '<?= e($item['title']) ?>'?"
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
