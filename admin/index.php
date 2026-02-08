<?php
/**
 * Дашборд админ-панели
 */

$pageTitle = 'Дашборд';
require_once __DIR__ . '/includes/admin-header.php';

// Получение статистики
$pdo = getDB();
$newsCount = (int) $pdo->query('SELECT COUNT(*) FROM news')->fetchColumn();
$figuresCount = (int) $pdo->query('SELECT COUNT(*) FROM figures')->fetchColumn();
$totalDownloads = (int) $pdo->query('SELECT SUM(downloads_count) FROM figures')->fetchColumn();

// Последние новости
$recentNews = $pdo->query('SELECT * FROM news ORDER BY created_at DESC LIMIT 5')->fetchAll();

// Последние фигурки
$recentFigures = $pdo->query('SELECT * FROM figures ORDER BY created_at DESC LIMIT 5')->fetchAll();

// Топ скачиваемых
$topFigures = $pdo->query('SELECT * FROM figures WHERE downloads_count > 0 ORDER BY downloads_count DESC LIMIT 5')->fetchAll();
?>

<h1 class="mb-4"><i class="bi bi-speedometer2"></i> Дашборд</h1>

<!-- Статистика -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Новостей</h6>
                        <h2 class="mb-0"><?= $newsCount ?></h2>
                    </div>
                    <i class="bi bi-newspaper" style="font-size: 3rem; opacity: 0.5;"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Фигурок</h6>
                        <h2 class="mb-0"><?= $figuresCount ?></h2>
                    </div>
                    <i class="bi bi-box" style="font-size: 3rem; opacity: 0.5;"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Скачиваний</h6>
                        <h2 class="mb-0"><?= $totalDownloads ?></h2>
                    </div>
                    <i class="bi bi-download" style="font-size: 3rem; opacity: 0.5;"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Последние новости -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-newspaper"></i> Последние новости</h5>
                <a href="<?= SITE_URL ?>/admin/news.php" class="btn btn-sm btn-outline-primary">Все</a>
            </div>
            <div class="card-body">
                <?php if (empty($recentNews)): ?>
                    <p class="text-muted mb-0">Новостей пока нет</p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recentNews as $news): ?>
                            <a href="<?= SITE_URL ?>/admin/news-edit.php?id=<?= $news['id'] ?>"
                               class="list-group-item list-group-item-action d-flex justify-content-between">
                                <span><?= e($news['title']) ?></span>
                                <small class="text-muted"><?= formatDate($news['created_at'], 'd.m.Y') ?></small>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Последние фигурки -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-box"></i> Последние фигурки</h5>
                <a href="<?= SITE_URL ?>/admin/figures.php" class="btn btn-sm btn-outline-primary">Все</a>
            </div>
            <div class="card-body">
                <?php if (empty($recentFigures)): ?>
                    <p class="text-muted mb-0">Фигурок пока нет</p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recentFigures as $figure): ?>
                            <a href="<?= SITE_URL ?>/admin/figures-edit.php?id=<?= $figure['id'] ?>"
                               class="list-group-item list-group-item-action d-flex justify-content-between">
                                <span><?= e($figure['name']) ?></span>
                                <small class="text-muted">
                                    <i class="bi bi-download"></i> <?= $figure['downloads_count'] ?>
                                </small>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Топ скачиваемых -->
<?php if (!empty($topFigures)): ?>
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-trophy"></i> Топ скачиваемых фигурок</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Название</th>
                        <th>Скачиваний</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($topFigures as $index => $figure): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td>
                                <a href="<?= SITE_URL ?>/admin/figures-edit.php?id=<?= $figure['id'] ?>">
                                    <?= e($figure['name']) ?>
                                </a>
                            </td>
                            <td><span class="badge bg-primary"><?= $figure['downloads_count'] ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
