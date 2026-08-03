<?php
session_start();
require_once __DIR__ . '/../../config/connect.php';
require_once __DIR__ . '/../../server/utils/sanitize.php';
require_once __DIR__ . '/../../server/utils/admin_guard.php';
require_once __DIR__ . '/../../server/queries/admin_queries.php';

$sort            = sanitize_text($_GET['sort']     ?? 'name');
$filter_user     = sanitize_text($_GET['user']     ?? '');
$filter_category = sanitize_text($_GET['category'] ?? '');

$items = get_admin_inventory($conn, $filter_user, $filter_category, $sort);
$users = get_inventory_users($conn);

$total_items = count($items);
$low_stock   = count(array_filter($items, fn($i) => (int)$i['product_quantity'] <= 5));
$out_stock   = count(array_filter($items, fn($i) => (int)$i['product_quantity'] === 0));

$allowed_categories = [
    'rice_grains','canned_goods','noodles_pasta','snacks','bread_bakery',
    'condiments','cooking_oil','soft_drinks','water','coffee_tea',
    'powdered_drinks','cleaning','laundry','dishwashing','hygiene',
    'hair_care','skin_care','school','medicine','load_eload','others',
    // legacy values
    'food','drinks','canned','noodles',
];

$category_labels = [
    'rice_grains'=>'Rice & Grains','canned_goods'=>'Canned Goods','noodles_pasta'=>'Noodles & Pasta',
    'snacks'=>'Snacks & Chips','bread_bakery'=>'Bread & Bakery','condiments'=>'Condiments & Sauces',
    'cooking_oil'=>'Cooking Oil','soft_drinks'=>'Soft Drinks','water'=>'Water & Juice',
    'coffee_tea'=>'Coffee & Tea','powdered_drinks'=>'Powdered Drinks','cleaning'=>'Cleaning',
    'laundry'=>'Laundry','dishwashing'=>'Dishwashing','hygiene'=>'Hygiene',
    'hair_care'=>'Hair Care','skin_care'=>'Skin Care','school'=>'School Supplies',
    'medicine'=>'Medicine','load_eload'=>'Load & E-load','others'=>'Others',
    'food'=>'Food','drinks'=>'Drinks','canned'=>'Canned','noodles'=>'Noodles',
];

if ($filter_category !== '' && !in_array($filter_category, $allowed_categories, true)) {
    $filter_category = '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arca Admin — Inventory</title>
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/img/logo.png">
    <?php include __DIR__ . '/admin_style.php'; ?>
</head>
<body>
<?php include __DIR__ . '/admin_nav.php'; ?>

<main>
    <div class="page-header">
        <h1>Inventory</h1>
        <p class="page-sub">Read-only view across all users</p>
    </div>

    <div class="stats-row">
        <div class="stat-card">
            <p class="stat-label">Total Items</p>
            <p class="stat-value"><?= $total_items ?></p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Low Stock (≤5)</p>
            <p class="stat-value" style="color:#f1c40f;"><?= $low_stock ?></p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Out of Stock</p>
            <p class="stat-value" style="color:#e74c3c;"><?= $out_stock ?></p>
        </div>
        <div class="stat-card">
            <p class="stat-label">Users with Items</p>
            <p class="stat-value"><?= count($users) ?></p>
        </div>
    </div>

    <!-- Filters -->
    <form method="GET" action="inventory.php">
        <div class="filters">
            <select name="user">
                <option value="">All Users</option>
                <?php foreach ($users as $u): ?>
                <option value="<?= htmlspecialchars($u, ENT_QUOTES, 'UTF-8') ?>" <?= $filter_user===$u ? 'selected' : '' ?>>
                    <?= htmlspecialchars($u, ENT_QUOTES, 'UTF-8') ?>
                </option>
                <?php endforeach; ?>
            </select>

            <select name="category">
                <option value="">All Categories</option>
                <?php foreach ($allowed_categories as $cat): ?>
                <option value="<?= $cat ?>" <?= $filter_category===$cat ? 'selected' : '' ?>>
                    <?= $category_labels[$cat] ?? ucfirst($cat) ?>
                </option>
                <?php endforeach; ?>
            </select>

            <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">
            <button type="submit" style="padding:7px 16px;background:#00bcd4;border:none;color:#000;border-radius:7px;cursor:pointer;font-weight:bold;font-size:0.85rem;">Filter</button>
            <a href="inventory.php" style="padding:7px 14px;background:#1f1f2e;color:#aaa;border-radius:7px;font-size:0.85rem;text-decoration:none;border:1px solid rgba(255,255,255,0.07);">Reset</a>
        </div>
    </form>

    <div class="sort-buttons">
        <a href="inventory.php?sort=name&user=<?= urlencode($filter_user) ?>&category=<?= urlencode($filter_category) ?>"     class="<?= $sort==='name'     ? 'active' : '' ?>">Name</a>
        <a href="inventory.php?sort=user&user=<?= urlencode($filter_user) ?>&category=<?= urlencode($filter_category) ?>"     class="<?= $sort==='user'     ? 'active' : '' ?>">User</a>
        <a href="inventory.php?sort=category&user=<?= urlencode($filter_user) ?>&category=<?= urlencode($filter_category) ?>" class="<?= $sort==='category' ? 'active' : '' ?>">Category</a>
        <a href="inventory.php?sort=qty&user=<?= urlencode($filter_user) ?>&category=<?= urlencode($filter_category) ?>"      class="<?= $sort==='qty'      ? 'active' : '' ?>">Qty ↑</a>
        <a href="inventory.php?sort=price&user=<?= urlencode($filter_user) ?>&category=<?= urlencode($filter_category) ?>"    class="<?= $sort==='price'    ? 'active' : '' ?>">Price ↓</a>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th>Category</th>
                    <th>User</th>
                    <th>Quantity</th>
                    <th>Cost</th>
                    <th>Price</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($items): ?>
                    <?php foreach ($items as $item): ?>
                    <?php
                        $qty = (int)$item['product_quantity'];
                        if ($qty === 0)     { $status = '<span class="badge badge-red">Out of Stock</span>'; }
                        elseif ($qty <= 5)  { $status = '<span class="badge badge-yellow">Low Stock</span>'; }
                        else                { $status = '<span class="badge badge-green">In Stock</span>'; }
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($item['product_name'],     ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= $category_labels[$item['product_category']] ?? htmlspecialchars($item['product_category'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($item['user_name'],         ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= $qty ?></td>
                        <td>₱<?= number_format($item['product_cost'],  2) ?></td>
                        <td>₱<?= number_format($item['product_price'], 2) ?></td>
                        <td><?= $status ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7" class="empty">No items found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>
</body>
</html>