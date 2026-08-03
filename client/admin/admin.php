<?php
session_start();
require_once __DIR__ . '/../../config/connect.php';
require_once __DIR__ . '/../../server/utils/sanitize.php';
require_once __DIR__ . '/../../server/utils/admin_guard.php';
require_once __DIR__ . '/../../server/queries/admin_queries.php';

$sort  = sanitize_text($_GET['sort'] ?? 'name');
$users = get_all_users($conn, $sort);
$stats = get_admin_stats($conn);

$current = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arca Admin — Users</title>
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/img/logo.png">
    <?php include __DIR__ . '/admin_style.php'; ?>
</head>
<body>
<?php include __DIR__ . '/admin_nav.php'; ?>

<main>
    <div class="page-header">
        <h1>Users</h1>
        <p class="page-sub">Manage all registered accounts</p>
    </div>

    <div class="stats-row">
        <div class="stat-card">
            <p class="stat-label">Total Users</p>
            <p class="stat-value"><?= $stats['total_users'] ?></p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Platform Sales</p>
            <p class="stat-value">₱<?= number_format($stats['total_sales'], 2) ?></p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Total Items</p>
            <p class="stat-value"><?= $stats['total_items'] ?></p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Net Profit</p>
            <p class="stat-value">₱<?= number_format($stats['total_sales'] - $stats['total_cost'], 2) ?></p>
        </div>
    </div>

    <div class="sort-buttons">
        <a href="admin.php?sort=name"  class="<?= $sort==='name'  ? 'active' : '' ?>">Sort by Name</a>
        <a href="admin.php?sort=date"  class="<?= $sort==='date'  ? 'active' : '' ?>">Sort by Date</a>
        <a href="admin.php?sort=sales" class="<?= $sort==='sales' ? 'active' : '' ?>">Sort by Sales</a>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>User Name</th>
                    <th>Created Date</th>
                    <th>Total Sales</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($users): ?>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['user_name'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($user['user_date'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td>₱<?= number_format($user['total_sales'], 2) ?></td>
                        <td>
                            <form class="delete-form" action="../../server/admin/control.php" method="POST" style="display:inline-block;">
                                <input type="hidden" name="user_id" value="<?= (int)$user['user_id'] ?>">
                                <button class="btn-danger" type="button">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="empty">No users found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<div class="delete-confirm" id="deleteConfirm">
    <p>Do you want to delete this user?</p>
    <button class="confirm-yes" id="confirmYes">Yes, Delete</button>
    <button class="confirm-no"  id="confirmNo">Cancel</button>
</div>
<div class="overlay" id="overlay"></div>

<script>
let selectedForm = null;
document.querySelectorAll('.delete-form .btn-danger').forEach(btn => {
    btn.addEventListener('click', function() {
        selectedForm = this.closest('.delete-form');
        document.getElementById('deleteConfirm').style.display = 'block';
        document.getElementById('overlay').style.display = 'block';
    });
});
document.getElementById('confirmYes').addEventListener('click', () => { if (selectedForm) selectedForm.submit(); });
document.getElementById('confirmNo').addEventListener('click', () => {
    document.getElementById('deleteConfirm').style.display = 'none';
    document.getElementById('overlay').style.display = 'none';
    selectedForm = null;
});
</script>
</body>
</html>