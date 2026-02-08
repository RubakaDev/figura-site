<?php
/**
 * Главная страница - Новости
 */

$pageTitle = 'Главная';

require_once __DIR__ . '/includes/header.php';

$page = max(1, (int) ($_GET['page'] ?? 1));
$news = getNews($page);
$totalNews = getNewsCount();
$pagination = getPagination($page, $totalNews, NEWS_PER_PAGE, SITE_URL . '/');
?>

<div class="row">
    <div class="col-lg-8">
        <h1 class="mb-4"><i class="bi bi-newspaper"></i> Новости</h1>

        <?php if (empty($news)): ?>
            <div class="empty-state">
                <i class="bi bi-inbox"></i>
                <h5>Новостей пока нет</h5>
                <p>Скоро здесь появятся интересные новости!</p>
            </div>
        <?php else: ?>
            <?php foreach ($news as $item): ?>
                <article class="card news-card mb-4">
                    <div class="card-body">
                        <h2 class="card-title h5"><?= e($item['title']) ?></h2>
                        <p class="news-date mb-3">
                            <i class="bi bi-calendar3"></i>
                            <?= formatDate($item['created_at']) ?>
                        </p>
                        <div class="card-text">
                            <?= nl2br(e($item['content'])) ?>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>

            <?php if ($pagination['total'] > 1): ?>
                <nav aria-label="Навигация по страницам">
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
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-grid"></i> Каталог фигурок</h5>
            </div>
            <div class="card-body">
                <p>Посмотрите нашу коллекцию 3D-фигурок для печати и скачайте понравившиеся модели.</p>
                <a href="<?= SITE_URL ?>/catalog.php" class="btn btn-primary">
                    <i class="bi bi-arrow-right"></i> Перейти в каталог
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
