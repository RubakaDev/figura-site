<?php
/**
 * Создание/редактирование фигурки
 */

require_once __DIR__ . '/includes/auth.php';
requireAuth();

$id = (int) ($_GET['id'] ?? 0);
$isEdit = $id > 0;
$pageTitle = $isEdit ? 'Редактирование фигурки' : 'Новая фигурка';

$figure = null;
$images = [];
if ($isEdit) {
    $figure = getFigureById($id);
    if (!$figure) {
        setFlash('error', 'Фигурка не найдена');
        redirect(SITE_URL . '/admin/figures.php');
    }
    $images = getFigureImages($id);
}

$errors = [];

// Удаление дополнительного изображения
if (isset($_GET['delete_image']) && isset($_GET['token'])) {
    if (verifyCsrfToken($_GET['token'])) {
        $imageId = (int) $_GET['delete_image'];
        $pdo = getDB();
        $stmt = $pdo->prepare('SELECT image_path FROM figure_images WHERE id = ? AND figure_id = ?');
        $stmt->execute([$imageId, $id]);
        $img = $stmt->fetch();

        if ($img) {
            deleteFile($img['image_path']);
            $stmt = $pdo->prepare('DELETE FROM figure_images WHERE id = ?');
            $stmt->execute([$imageId]);
            setFlash('success', 'Изображение удалено');
        }
    }
    redirect(SITE_URL . '/admin/figures-edit.php?id=' . $id);
}

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Ошибка безопасности. Попробуйте ещё раз.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if (empty($name)) {
            $errors[] = 'Введите название';
        }

        // Загрузка главного изображения
        $imagePath = $figure['image_path'] ?? null;
        if (!empty($_FILES['image']['name'])) {
            $newImagePath = uploadImage($_FILES['image']);
            if ($newImagePath) {
                // Удаляем старое изображение
                if ($imagePath) {
                    deleteFile($imagePath);
                }
                $imagePath = $newImagePath;
            } else {
                $errors[] = 'Ошибка загрузки изображения. Проверьте формат и размер файла.';
            }
        }

        // Загрузка 3D-файла
        $filePath = $figure['file_path'] ?? null;
        $fileSize = $figure['file_size'] ?? 0;
        if (!empty($_FILES['file']['name'])) {
            $fileData = uploadFile($_FILES['file']);
            if ($fileData) {
                // Удаляем старый файл
                if ($filePath) {
                    deleteFile($filePath);
                }
                $filePath = $fileData['path'];
                $fileSize = $fileData['size'];
            } else {
                $errors[] = 'Ошибка загрузки файла. Проверьте формат и размер.';
            }
        }

        if (empty($errors)) {
            $pdo = getDB();

            if ($isEdit) {
                $stmt = $pdo->prepare('UPDATE figures SET name = ?, description = ?, image_path = ?, file_path = ?, file_size = ? WHERE id = ?');
                $stmt->execute([$name, $description, $imagePath, $filePath, $fileSize, $id]);
            } else {
                $stmt = $pdo->prepare('INSERT INTO figures (name, description, image_path, file_path, file_size) VALUES (?, ?, ?, ?, ?)');
                $stmt->execute([$name, $description, $imagePath, $filePath, $fileSize]);
                $id = $pdo->lastInsertId();
            }

            // Загрузка дополнительных изображений
            if (!empty($_FILES['gallery']['name'][0])) {
                foreach ($_FILES['gallery']['name'] as $key => $filename) {
                    if ($_FILES['gallery']['error'][$key] === UPLOAD_ERR_OK) {
                        $galleryFile = [
                            'name' => $_FILES['gallery']['name'][$key],
                            'type' => $_FILES['gallery']['type'][$key],
                            'tmp_name' => $_FILES['gallery']['tmp_name'][$key],
                            'error' => $_FILES['gallery']['error'][$key],
                            'size' => $_FILES['gallery']['size'][$key]
                        ];

                        $galleryPath = uploadImage($galleryFile);
                        if ($galleryPath) {
                            $stmt = $pdo->prepare('INSERT INTO figure_images (figure_id, image_path, sort_order) VALUES (?, ?, ?)');
                            $stmt->execute([$id, $galleryPath, $key]);
                        }
                    }
                }
            }

            setFlash('success', $isEdit ? 'Фигурка обновлена' : 'Фигурка создана');
            redirect(SITE_URL . '/admin/figures.php');
        }
    }
}

require_once __DIR__ . '/includes/admin-header.php';
$csrfToken = generateCsrfToken();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-box"></i> <?= $isEdit ? 'Редактирование фигурки' : 'Новая фигурка' ?></h1>
    <a href="<?= SITE_URL ?>/admin/figures.php" class="btn btn-outline-secondary">
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

<form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Основная информация</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Название <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control" required
                               value="<?= e($_POST['name'] ?? $figure['name'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Описание</label>
                        <textarea name="description" id="description" class="form-control" rows="6"><?= e($_POST['description'] ?? $figure['description'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Дополнительные изображения -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Галерея изображений</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($images)): ?>
                        <div class="row g-3 mb-3">
                            <?php foreach ($images as $img): ?>
                                <div class="col-md-3">
                                    <div class="position-relative">
                                        <img src="<?= SITE_URL ?>/<?= e($img['image_path']) ?>"
                                             class="img-fluid rounded" alt="">
                                        <a href="<?= SITE_URL ?>/admin/figures-edit.php?id=<?= $id ?>&delete_image=<?= $img['id'] ?>&token=<?= $csrfToken ?>"
                                           class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1"
                                           data-confirm="Удалить это изображение?">
                                            <i class="bi bi-x"></i>
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <div class="mb-0">
                        <label for="gallery" class="form-label">Добавить изображения</label>
                        <input type="file" name="gallery[]" id="gallery" class="form-control"
                               accept="image/*" multiple>
                        <div class="form-text">Можно выбрать несколько файлов</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Главное изображение -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Главное изображение</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($figure['image_path'])): ?>
                        <img src="<?= SITE_URL ?>/<?= e($figure['image_path']) ?>"
                             id="image-preview" class="img-fluid rounded mb-3" alt="">
                    <?php else: ?>
                        <img src="" id="image-preview" class="img-fluid rounded mb-3"
                             style="display: none;" alt="">
                    <?php endif; ?>

                    <input type="file" name="image" id="image" class="form-control"
                           accept="image/*" data-preview="image-preview">
                    <div class="form-text">JPG, PNG, GIF, WebP. Макс. 5 МБ</div>
                </div>
            </div>

            <!-- 3D-файл -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">3D-файл для скачивания</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($figure['file_path'])): ?>
                        <div class="alert alert-success mb-3">
                            <i class="bi bi-check-circle"></i>
                            Файл загружен (<?= formatFileSize($figure['file_size']) ?>)
                        </div>
                    <?php endif; ?>

                    <input type="file" name="file" id="file" class="form-control"
                           accept=".stl,.obj,.fbx,.3ds,.blend,.zip,.rar,.7z">
                    <div class="form-text">STL, OBJ, FBX, 3DS, Blend, ZIP, RAR, 7Z. Макс. 100 МБ</div>
                </div>
            </div>

            <!-- Статистика -->
            <?php if ($isEdit): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Статистика</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-2">
                            <strong>Скачиваний:</strong> <?= $figure['downloads_count'] ?>
                        </p>
                        <p class="mb-2">
                            <strong>Создано:</strong> <?= formatDate($figure['created_at']) ?>
                        </p>
                        <p class="mb-0">
                            <strong>Изменено:</strong> <?= formatDate($figure['updated_at']) ?>
                        </p>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Кнопки -->
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bi bi-check-lg"></i> <?= $isEdit ? 'Сохранить' : 'Создать' ?>
                </button>
                <a href="<?= SITE_URL ?>/admin/figures.php" class="btn btn-outline-secondary">Отмена</a>
            </div>
        </div>
    </div>
</form>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
