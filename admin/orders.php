<?php
/**
 * Управление заявками на покупку (админ)
 */

$pageTitle = 'Заявки на покупку';

require_once __DIR__ . '/includes/admin-header.php';

// Обработка смены статуса
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Ошибка безопасности.');
    } else {
        $requestId = (int) ($_POST['request_id'] ?? 0);
        $newStatus = $_POST['status'] ?? '';
        $validStatuses = ['new', 'processing', 'completed', 'rejected'];

        if ($requestId > 0 && in_array($newStatus, $validStatuses)) {
            updatePurchaseRequestStatus($requestId, $newStatus);
            setFlash('success', 'Статус заявки обновлён.');
        } else {
            setFlash('error', 'Некорректные данные.');
        }
    }
    redirect(SITE_URL . '/admin/orders.php' . (isset($_GET['status']) ? '?status=' . urlencode($_GET['status']) : ''));
}

$filterStatus = $_GET['status'] ?? '';
$requests = getAllPurchaseRequests($filterStatus);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3"><i class="bi bi-cart"></i> Заявки на покупку</h1>
    <span class="badge bg-secondary fs-6"><?= count($requests) ?></span>
</div>

<!-- Фильтр по статусу -->
<div class="mb-4">
    <div class="btn-group" role="group">
        <a href="<?= SITE_URL ?>/admin/orders.php"
           class="btn btn-outline-secondary <?= $filterStatus === '' ? 'active' : '' ?>">
            Все
        </a>
        <a href="<?= SITE_URL ?>/admin/orders.php?status=new"
           class="btn btn-outline-primary <?= $filterStatus === 'new' ? 'active' : '' ?>">
            Новые
        </a>
        <a href="<?= SITE_URL ?>/admin/orders.php?status=processing"
           class="btn btn-outline-warning <?= $filterStatus === 'processing' ? 'active' : '' ?>">
            В обработке
        </a>
        <a href="<?= SITE_URL ?>/admin/orders.php?status=completed"
           class="btn btn-outline-success <?= $filterStatus === 'completed' ? 'active' : '' ?>">
            Выполнены
        </a>
        <a href="<?= SITE_URL ?>/admin/orders.php?status=rejected"
           class="btn btn-outline-danger <?= $filterStatus === 'rejected' ? 'active' : '' ?>">
            Отклонены
        </a>
    </div>
</div>

<?php if (empty($requests)): ?>
    <div class="empty-state">
        <i class="bi bi-cart-x"></i>
        <h5>Заявок нет</h5>
        <p>Нет заявок на покупку<?= $filterStatus ? ' с выбранным статусом' : '' ?>.</p>
    </div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Фигурка</th>
                    <th>Пользователь</th>
                    <th>Email</th>
                    <th>Сообщение</th>
                    <th>Статус</th>
                    <th>Дата</th>
                    <th>Действие</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($requests as $req): ?>
                    <tr>
                        <td><?= $req['id'] ?></td>
                        <td>
                            <a href="<?= SITE_URL ?>/figure.php?id=<?= $req['figure_id'] ?>" target="_blank">
                                <?= e($req['figure_name']) ?>
                            </a>
                        </td>
                        <td><?= e($req['username']) ?></td>
                        <td><?= e($req['email']) ?></td>
                        <td>
                            <?php if ($req['message']): ?>
                                <span title="<?= e($req['message']) ?>">
                                    <?= e(mb_substr($req['message'], 0, 50)) ?><?= mb_strlen($req['message']) > 50 ? '...' : '' ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge <?= getPurchaseStatusBadge($req['status']) ?>">
                                <?= e(getPurchaseStatusLabel($req['status'])) ?>
                            </span>
                        </td>
                        <td><?= formatDate($req['created_at']) ?></td>
                        <td>
                            <form method="POST" class="d-flex gap-1" style="min-width: 200px;">
                                <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                                <input type="hidden" name="request_id" value="<?= $req['id'] ?>">
                                <select name="status" class="form-select form-select-sm">
                                    <option value="new" <?= $req['status'] === 'new' ? 'selected' : '' ?>>Новая</option>
                                    <option value="processing" <?= $req['status'] === 'processing' ? 'selected' : '' ?>>В обработке</option>
                                    <option value="completed" <?= $req['status'] === 'completed' ? 'selected' : '' ?>>Выполнена</option>
                                    <option value="rejected" <?= $req['status'] === 'rejected' ? 'selected' : '' ?>>Отклонена</option>
                                </select>
                                <button type="submit" class="btn btn-sm btn-primary">
                                    <i class="bi bi-check-lg"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
