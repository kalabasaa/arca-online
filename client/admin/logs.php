<?php
session_start();
require_once __DIR__ . '/../../config/connect.php';
require_once __DIR__ . '/../../server/utils/sanitize.php';
require_once __DIR__ . '/../../server/utils/admin_guard.php';
require_once __DIR__ . '/../../server/queries/admin_queries.php';

$per_page      = 30;
$page          = max(1, sanitize_int($_GET['page'] ?? 1) ?: 1);
$filter_action = sanitize_text($_GET['action'] ?? '');
$offset        = ($page - 1) * $per_page;

$logs       = get_logs($conn, $filter_action, $per_page, $offset);
$total      = get_logs_count($conn, $filter_action);
$total_pages = (int)ceil($total / $per_page);

$action_labels = [
    'login_success' => ['Login',      'badge-green'],
    'login_fail'    => ['Failed Login','badge-red'],
    'logout'        => ['Logout',     'badge-gray'],
    'signup'        => ['Signup',     'badge-blue'],
    'push'          => ['Sale Push',  'badge-green'],
    'item_add'      => ['Item Added', 'badge-blue'],
    'item_delete'   => ['Item Deleted','badge-yellow'],
    'user_delete'   => ['User Deleted','badge-red'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arca Admin — Logs</title>
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/img/logo.png">
    <?php include __DIR__ . '/admin_style.php'; ?>
</head>
<body>
<?php include __DIR__ . '/admin_nav.php'; ?>

<main>
    <div class="page-header">
        <h1>Activity Logs</h1>
        <p class="page-sub"><?= number_format($total) ?> total entries</p>
    </div>

    <!-- Filter by action -->
    <form method="GET" action="logs.php">
        <div class="filters">
            <select name="action" onchange="this.form.submit()">
                <option value="" <?= $filter_action==='' ? 'selected' : '' ?>>All Actions</option>
                <?php foreach ($action_labels as $val => [$label, $_]): ?>
                <option value="<?= $val ?>" <?= $filter_action===$val ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </form>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Time</th>
                    <th>User</th>
                    <th>Action</th>
                    <th>Description</th>
                    <th>IP Address</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($logs): ?>
                    <?php foreach ($logs as $log): ?>
                    <?php
                        $action_key = $log['action'];
                        [$label, $badge_class] = $action_labels[$action_key] ?? [$action_key, 'badge-gray'];
                    ?>
                    <tr>
                        <td style="color:#555;"><?= (int)$log['log_id'] ?></td>
                        <td style="white-space:nowrap;"><?= date('M d, Y H:i:s', strtotime($log['created_at'])) ?></td>
                        <td><?= htmlspecialchars($log['user_name'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                        <td><span class="badge <?= $badge_class ?>"><?= $label ?></span></td>
                        <td style="color:#888;"><?= htmlspecialchars($log['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                        <td style="color:#555;font-size:0.8rem;"><?= htmlspecialchars($log['ip_address'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="empty">No logs found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?page=<?= $page-1 ?>&action=<?= urlencode($filter_action) ?>">← Prev</a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <?php if ($i === $page): ?>
                <span class="current"><?= $i ?></span>
            <?php elseif ($i <= 2 || $i >= $total_pages - 1 || abs($i - $page) <= 1): ?>
                <a href="?page=<?= $i ?>&action=<?= urlencode($filter_action) ?>"><?= $i ?></a>
            <?php elseif (abs($i - $page) === 2): ?>
                <span class="dots">…</span>
            <?php endif; ?>
        <?php endfor; ?>

        <?php if ($page < $total_pages): ?>
            <a href="?page=<?= $page+1 ?>&action=<?= urlencode($filter_action) ?>">Next →</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</main>
</body>
</html>