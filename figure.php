<?php
/**
 * Страница отдельной фигурки
 */

session_start();
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

// Обработка POST-запросов (комментарии и заявки)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isUserLoggedIn()) {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Ошибка безопасности. Попробуйте ещё раз.');
        redirect(SITE_URL . '/figure.php?id=' . $id);
    }

    $action = $_POST['action'] ?? '';
    $user = getCurrentUser();

    if ($action === 'comment') {
        $content = trim($_POST['content'] ?? '');
        if (!empty($content) && mb_strlen($content) <= 2000) {
            addComment($id, $user['id'], $content);
            setFlash('success', 'Комментарий добавлен!');
        } else {
            setFlash('error', 'Комментарий не может быть пустым (максимум 2000 символов).');
        }
        redirect(SITE_URL . '/figure.php?id=' . $id . '#comments');
    }

    if ($action === 'purchase') {
        $message = trim($_POST['message'] ?? '');
        createPurchaseRequest($id, $user['id'], $message);
        setFlash('success', 'Заявка на покупку отправлена! Мы свяжемся с вами.');
        redirect(SITE_URL . '/figure.php?id=' . $id);
    }
}

$pageTitle = $figure['name'];
$images = getFigureImages($id);
$comments = getCommentsByFigure($id);

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

        <div class="d-flex gap-2 flex-wrap">
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

            <?php if (isUserLoggedIn()): ?>
                <button type="button" class="btn btn-success btn-lg" data-bs-toggle="modal" data-bs-target="#purchaseModal">
                    <i class="bi bi-cart-plus"></i> Заказать печать
                </button>
            <?php else: ?>
                <a href="<?= SITE_URL ?>/login.php" class="btn btn-outline-success btn-lg">
                    <i class="bi bi-cart-plus"></i> Заказать печать
                </a>
            <?php endif; ?>
        </div>

        <div class="mt-4">
            <a href="<?= SITE_URL ?>/catalog.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Назад в каталог
            </a>
        </div>
    </div>
</div>

<!-- Секция комментариев -->
<div class="mt-5" id="comments">
    <h3 class="mb-4"><i class="bi bi-chat-dots"></i> Комментарии (<?= count($comments) ?>)</h3>

    <?php if (isUserLoggedIn()): ?>
        <div class="card mb-4">
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                    <input type="hidden" name="action" value="comment">

                    <div class="mb-3">
                        <label for="comment-content" class="form-label">Ваш комментарий</label>
                        <textarea name="content" id="comment-content" class="form-control"
                                  rows="3" required maxlength="2000"
                                  placeholder="Напишите ваш комментарий..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send"></i> Отправить
                    </button>
                </form>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-info mb-4">
            <a href="<?= SITE_URL ?>/login.php">Войдите</a> или
            <a href="<?= SITE_URL ?>/register.php">зарегистрируйтесь</a>,
            чтобы оставить комментарий.
        </div>
    <?php endif; ?>

    <?php if (empty($comments)): ?>
        <div class="text-center text-muted py-4">
            <i class="bi bi-chat" style="font-size: 3rem;"></i>
            <p class="mt-2">Пока нет комментариев. Будьте первым!</p>
        </div>
    <?php else: ?>
        <?php foreach ($comments as $comment): ?>
            <div class="card comment-card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <strong><i class="bi bi-person"></i> <?= e($comment['username']) ?></strong>
                        <small class="text-muted"><?= formatDate($comment['created_at']) ?></small>
                    </div>
                    <p class="mb-0"><?= nl2br(e($comment['content'])) ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Модальное окно заявки на покупку -->
<?php if (isUserLoggedIn()): ?>
<div class="modal fade" id="purchaseModal" tabindex="-1" aria-labelledby="purchaseModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                <input type="hidden" name="action" value="purchase">

                <div class="modal-header">
                    <h5 class="modal-title" id="purchaseModalLabel">
                        <i class="bi bi-cart-plus"></i> Заказать печать
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
                </div>

                <div class="modal-body">
                    <p>Вы хотите заказать печать фигурки <strong><?= e($figure['name']) ?></strong>.</p>
                    <div class="mb-3">
                        <label for="purchase-message" class="form-label">Сообщение (необязательно)</label>
                        <textarea name="message" id="purchase-message" class="form-control"
                                  rows="3" maxlength="1000"
                                  placeholder="Укажите пожелания: размер, цвет, материал..."></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-lg"></i> Отправить заявку
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
