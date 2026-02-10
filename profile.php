<?php
/**
 * Профиль пользователя
 */

session_start();
require_once __DIR__ . '/includes/functions.php';

if (!isUserLoggedIn()) {
    redirect(SITE_URL . '/login.php');
}

$user = getCurrentUser();
$comments = getUserComments($user['id']);
$requests = getUserPurchaseRequests($user['id']);

$tab = $_GET['tab'] ?? 'comments';

$pageTitle = 'Мой профиль';
require_once __DIR__ . '/includes/header.php';
?>

<h1 class="mb-4"><i class="bi bi-person-circle"></i> <?= e($user['username']) ?></h1>

<ul class="nav nav-tabs mb-4">
    <li class="nav-item">
        <a class="nav-link <?= $tab === 'comments' ? 'active' : '' ?>"
           href="<?= SITE_URL ?>/profile.php?tab=comments">
            <i class="bi bi-chat-dots"></i> Мои комментарии (<?= count($comments) ?>)
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $tab === 'orders' ? 'active' : '' ?>"
           href="<?= SITE_URL ?>/profile.php?tab=orders">
            <i class="bi bi-cart"></i> Мои заявки (<?= count($requests) ?>)
        </a>
    </li>
</ul>

<?php if ($tab === 'comments'): ?>
    <?php if (empty($comments)): ?>
        <div class="empty-state">
            <i class="bi bi-chat"></i>
            <h5>У вас пока нет комментариев</h5>
            <p>Перейдите в <a href="<?= SITE_URL ?>/catalog.php">каталог</a> и оставьте комментарий к понравившейся фигурке.</p>
        </div>
    <?php else: ?>
        <?php foreach ($comments as $comment): ?>
            <div class="card comment-card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <a href="<?= SITE_URL ?>/figure.php?id=<?= $comment['figure_id'] ?>" class="fw-bold text-decoration-none">
                            <i class="bi bi-box"></i> <?= e($comment['figure_name']) ?>
                        </a>
                        <small class="text-muted"><?= formatDate($comment['created_at']) ?></small>
                    </div>
                    <p class="mb-0"><?= nl2br(e($comment['content'])) ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
<?php elseif ($tab === 'orders'): ?>
    <?php if (empty($requests)): ?>
        <div class="empty-state">
            <i class="bi bi-cart"></i>
            <h5>У вас пока нет заявок</h5>
            <p>Перейдите в <a href="<?= SITE_URL ?>/catalog.php">каталог</a> и закажите печать понравившейся фигурки.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Фигурка</th>
                        <th>Сообщение</th>
                        <th>Статус</th>
                        <th>Дата</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($requests as $req): ?>
                        <tr>
                            <td>
                                <a href="<?= SITE_URL ?>/figure.php?id=<?= $req['figure_id'] ?>" class="text-decoration-none">
                                    <?= e($req['figure_name']) ?>
                                </a>
                            </td>
                            <td><?= $req['message'] ? e(mb_substr($req['message'], 0, 100)) : '<span class="text-muted">—</span>' ?></td>
                            <td>
                                <span class="badge <?= getPurchaseStatusBadge($req['status']) ?>">
                                    <?= e(getPurchaseStatusLabel($req['status'])) ?>
                                </span>
                            </td>
                            <td><?= formatDate($req['created_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
