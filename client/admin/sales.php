<?php
session_start();
require_once __DIR__ . '/../../config/connect.php';
require_once __DIR__ . '/../../server/utils/sanitize.php';
require_once __DIR__ . '/../../server/utils/admin_guard.php';
require_once __DIR__ . '/../../server/queries/admin_queries.php';

$sort      = sanitize_text($_GET['sort']      ?? 'date');
$date_from = sanitize_text($_GET['date_from'] ?? date('Y-m-01'));
$date_to   = sanitize_text($_GET['date_to']   ?? date('Y-m-d'));

// Validate dates — fall back to safe defaults if tampered
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_from)) $date_from = date('Y-m-01');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_to))   $date_to   = date('Y-m-d');

$sales       = get_admin_sales($conn, $sort, $date_from, $date_to);
$by_user     = get_sales_by_user($conn, $date_from, $date_to);

$grand_sales = array_sum(array_column($by_user, 'total_sales'));
$grand_cost  = array_sum(array_column($by_user, 'total_cost'));
$grand_profit = $grand_sales - $grand_cost;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arca Admin — Sales</title>
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/img/logo.png">
    <?php include __DIR__ . '/admin_style.php'; ?>
</head>
<body>
<?php include __DIR__ . '/admin_nav.php'; ?>

<main>
    <div class="page-header">
        <h1>Sales Overview</h1>
        <p class="page-sub">Aggregate sales across all users</p>
    </div>

    <div class="stats-row">
        <div class="stat-card">
            <p class="stat-label">Total Sales</p>
            <p class="stat-value">₱<?= number_format($grand_sales, 2) ?></p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Total Cost</p>
            <p class="stat-value" style="color:#e74c3c;">₱<?= number_format($grand_cost, 2) ?></p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Net Profit</p>
            <p class="stat-value" style="color:#27ae60;">₱<?= number_format($grand_profit, 2) ?></p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Transactions</p>
            <p class="stat-value"><?= count($sales) ?></p>
        </div>
    </div>

    <!-- Per-user breakdown -->
    <h2 style="font-size:1rem;color:#aaa;margin-bottom:12px;font-weight:500;">Performance by User</h2>
    <div class="table-wrap" style="margin-bottom:32px;">
        <table>
            <thead>
                <tr>
                    <th>User</th>
                    <th>Transactions</th>
                    <th>Total Sales</th>
                    <th>Total Cost</th>
                    <th>Net Profit</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($by_user): ?>
                    <?php foreach ($by_user as $u): ?>
                    <tr>
                        <td><?= htmlspecialchars($u['user_name'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= (int)$u['transactions'] ?></td>
                        <td>₱<?= number_format($u['total_sales'], 2) ?></td>
                        <td>₱<?= number_format($u['total_cost'],  2) ?></td>
                        <td style="color:#27ae60;">₱<?= number_format($u['total_sales'] - $u['total_cost'], 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="empty">No sales data.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Filters -->
    <form method="GET" action="sales.php">
        <div class="filters">
            <label style="color:#666;font-size:0.82rem;">From</label>
            <input type="date" name="date_from" value="<?= htmlspecialchars($date_from) ?>">
            <label style="color:#666;font-size:0.82rem;">To</label>
            <input type="date" name="date_to" value="<?= htmlspecialchars($date_to) ?>">
            <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">
            <button type="submit" style="padding:7px 16px;background:#00bcd4;border:none;color:#000;border-radius:7px;cursor:pointer;font-weight:bold;font-size:0.85rem;">Filter</button>
        </div>
    </form>

    <div class="sort-buttons">
        <a href="sales.php?sort=date&date_from=<?= $date_from ?>&date_to=<?= $date_to ?>"  class="<?= $sort==='date'  ? 'active' : '' ?>">Sort by Date</a>
        <a href="sales.php?sort=user&date_from=<?= $date_from ?>&date_to=<?= $date_to ?>"  class="<?= $sort==='user'  ? 'active' : '' ?>">Sort by User</a>
        <a href="sales.php?sort=sales&date_from=<?= $date_from ?>&date_to=<?= $date_to ?>" class="<?= $sort==='sales' ? 'active' : '' ?>">Sort by Amount</a>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>User</th>
                    <th>Item</th>
                    <th>Qty</th>
                    <th>Cost</th>
                    <th>Sales</th>
                    <th>Profit</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($sales): ?>
                    <?php foreach ($sales as $s): ?>
                    <tr>
                        <td><?= date('M d, Y', strtotime($s['sale_date'])) ?></td>
                        <td><?= htmlspecialchars($s['user_name'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($s['item_name'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= (int)$s['total_items'] ?></td>
                        <td>₱<?= number_format($s['total_cost'],  2) ?></td>
                        <td>₱<?= number_format($s['total_sales'], 2) ?></td>
                        <td style="color:#27ae60;">₱<?= number_format($s['total_sales'] - $s['total_cost'], 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7" class="empty">No transactions found for this date range.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>
</body>
</html>