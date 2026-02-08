<?php
/**
 * Каталог фигурок
 */

$pageTitle = 'Каталог';

require_once __DIR__ . '/includes/header.php';

$page = max(1, (int) ($_GET['page'] ?? 1));
$search = trim($_GET['search'] ?? '');
$figures = getFigures($page, ITEMS_PER_PAGE, $search);
$totalFigures = getFiguresCount($search);
$baseUrl = SITE_URL . '/catalog.php' . ($search ? '?search=' . urlencode($search) : '');
$pagination = getPagination($page, $totalFigures, ITEMS_PER_PAGE, $baseUrl);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-grid"></i> Каталог фигурок</h1>

    <form class="search-form d-flex" action="" method="GET">
        <input type="search" name="search" class="form-control me-2"
               placeholder="Поиск..." value="<?= e($search) ?>">
        <button type="submit" class="btn btn-outline-primary">
            <i class="bi bi-search"></i>
        </button>
    </form>
</div>

<?php if ($search): ?>
    <p class="text-muted mb-3">
        Результаты поиска по запросу "<?= e($search) ?>": найдено <?= $totalFigures ?>
        <a href="<?= SITE_URL ?>/catalog.php" class="ms-2">Сбросить</a>
    </p>
<?php endif; ?>

<?php if (empty($figures)): ?>
    <div class="empty-state">
        <i class="bi bi-box-seam"></i>
        <h5>Фигурки не найдены</h5>
        <?php if ($search): ?>
            <p>Попробуйте изменить поисковый запрос</p>
        <?php else: ?>
            <p>Каталог пока пуст, но скоро здесь появятся интересные модели!</p>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-4">
        <?php foreach ($figures as $figure): ?>
            <div class="col">
                <div class="card figure-card h-100">
                    <?php if ($figure['image_path']): ?>
                        <img src="<?= SITE_URL ?>/<?= e($figure['image_path']) ?>"
                             class="card-img-top" alt="<?= e($figure['name']) ?>">
                    <?php else: ?>
                        <div class="card-img-top d-flex align-items-center justify-content-center bg-light">
                            <i class="bi bi-box text-muted" style="font-size: 4rem;"></i>
                        </div>
                    <?php endif; ?>

                    <div class="card-body">
                        <h5 class="card-title"><?= e($figure['name']) ?></h5>
                        <?php if ($figure['description']): ?>
                            <p class="card-text">
                                <?= e(mb_substr($figure['description'], 0, 100)) ?>
                                <?= mb_strlen($figure['description']) > 100 ? '...' : '' ?>
                            </p>
                        <?php endif; ?>
                    </div>

                    <div class="card-footer bg-transparent d-flex justify-content-between align-items-center">
                        <span class="stats-badge text-muted">
                            <i class="bi bi-download"></i> <?= $figure['downloads_count'] ?>
                        </span>
                        <a href="<?= SITE_URL ?>/figure.php?id=<?= $figure['id'] ?>" class="btn btn-sm btn-primary">
                            Подробнее
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if ($pagination['total'] > 1): ?>
        <nav aria-label="Навигация по страницам" class="mt-4">
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

<?php require_once __DIR__ . '/includes/footer.php'; ?>
