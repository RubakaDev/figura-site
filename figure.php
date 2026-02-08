<?php
/**
 * Страница отдельной фигурки
 */

require_once __DIR__ . '/includes/functions.php';

$id = (int) ($_GET['id'] ?? 0);
$figure = getFigureById($id);

if (!$figure) {
    http_response_code(404);
    $pageTitle = 'Фигурка не найдена';
    require_once __DIR__ . '/includes/header.php';
    echo '<div class="alert alert-warning">Фигурка не найдена</div>';
    echo '<a href="' . SITE_URL . '/catalog.php" class="btn btn-primary">Вернуться в каталог</a>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$pageTitle = $figure['name'];
$images = getFigureImages($id);

require_once __DIR__ . '/includes/header.php';
?>

<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= SITE_URL ?>/">Главная</a></li>
        <li class="breadcrumb-item"><a href="<?= SITE_URL ?>/catalog.php">Каталог</a></li>
        <li class="breadcrumb-item active"><?= e($figure['name']) ?></li>
    </ol>
</nav>

<div class="row">
    <!-- Галерея изображений -->
    <div class="col-lg-6 mb-4">
        <div class="card">
            <div class="card-body">
                <?php
                $mainImage = $figure['image_path'] ?: null;
                $allImages = [];
                if ($mainImage) {
                    $allImages[] = $mainImage;
                }
                foreach ($images as $img) {
                    $allImages[] = $img['image_path'];
                }
                ?>

                <?php if (!empty($allImages)): ?>
                    <img src="<?= SITE_URL ?>/<?= e($allImages[0]) ?>"
                         id="gallery-main" class="img-fluid gallery-main w-100 mb-3 rounded"
                         alt="<?= e($figure['name']) ?>">

                    <?php if (count($allImages) > 1): ?>
                        <div class="d-flex gap-2 flex-wrap">
                            <?php foreach ($allImages as $index => $imgPath): ?>
                                <img src="<?= SITE_URL ?>/<?= e($imgPath) ?>"
                                     class="gallery-thumb rounded <?= $index === 0 ? 'border-primary border-2' : '' ?>"
                                     style="width: 80px; height: 80px; object-fit: cover; cursor: pointer;"
                                     data-full="<?= SITE_URL ?>/<?= e($imgPath) ?>"
                                     alt="Изображение <?= $index + 1 ?>">
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="text-center py-5 bg-light rounded">
                        <i class="bi bi-box text-muted" style="font-size: 6rem;"></i>
                        <p class="text-muted mt-3">Изображение отсутствует</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Информация о фигурке -->
    <div class="col-lg-6">
        <h1 class="mb-3"><?= e($figure['name']) ?></h1>

        <div class="d-flex gap-3 mb-4 text-muted">
            <span><i class="bi bi-calendar3"></i> <?= formatDate($figure['created_at'], 'd.m.Y') ?></span>
            <span><i class="bi bi-download"></i> <?= $figure['downloads_count'] ?> скачиваний</span>
            <?php if ($figure['file_size']): ?>
                <span><i class="bi bi-file-earmark"></i> <?= formatFileSize($figure['file_size']) ?></span>
            <?php endif; ?>
        </div>

        <?php if ($figure['description']): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Описание</h5>
                </div>
                <div class="card-body">
                    <?= nl2br(e($figure['description'])) ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($figure['file_path']): ?>
            <a href="<?= SITE_URL ?>/download.php?id=<?= $figure['id'] ?>"
               class="btn btn-download btn-lg">
                <i class="bi bi-download"></i> Скачать файл
                <?php if ($figure['file_size']): ?>
                    (<?= formatFileSize($figure['file_size']) ?>)
                <?php endif; ?>
            </a>
        <?php else: ?>
            <button class="btn btn-secondary btn-lg" disabled>
                <i class="bi bi-x-circle"></i> Файл недоступен
            </button>
        <?php endif; ?>

        <div class="mt-4">
            <a href="<?= SITE_URL ?>/catalog.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Назад в каталог
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
