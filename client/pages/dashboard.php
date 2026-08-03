<?php
session_start();
require_once __DIR__ . '/../../config/connect.php';
require_once __DIR__ . '/../../server/utils/sanitize.php';
require_once __DIR__ . '/../../server/queries/inventory_queries.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.html");
    exit();
}

$user_id = sanitize_int($_SESSION['user_id']);
if ($user_id === false) {
    session_destroy();
    header("Location: ../auth/login.html");
    exit();
}

if (isset($_POST['ajax_search'])) {
    $search = sanitize_text($_POST['ajax_search'] ?? '');
    $items  = search_items($conn, $user_id, $search);

    if ($items) {
        foreach ($items as $item) {
            $name     = htmlspecialchars($item['product_name'],     ENT_QUOTES, 'UTF-8');
            $category = htmlspecialchars($item['product_category'], ENT_QUOTES, 'UTF-8');
            $qty      = (int)$item['product_quantity'];
            $cost     = number_format($item['product_cost'],  2);
            $price    = number_format($item['product_price'], 2);
            $id       = (int)$item['product_id'];

            echo "
            <tr>
                <td>{$name}</td>
                <td>{$category}</td>
                <td>{$qty}</td>
                <td>{$cost}</td>
                <td>{$price}</td>
                <td>
                    <div class='action'>
                        <button type='button' class='editBtn'   data-idupdate='{$id}'>Edit</button>
                        <button type='button' class='deleteBtn' data-id='{$id}'>Delete</button>
                    </div>
                </td>
            </tr>";
        }
    } else {
        echo "<tr><td colspan='6' style='text-align:center;'>No items found</td></tr>";
    }
    exit;
}

$items = get_all_items($conn, $user_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arca Dashboard</title>
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/img/logo.png">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
<div class="glow-orb glow-orb-1"></div>
<div class="glow-orb glow-orb-2"></div>
<div class="glow-orb glow-orb-3"></div>
<header>
    <div class="logo"><img src="../assets/img/arca.png" alt="logo"></div>

    <nav id="nav-menu">
        <a href="dashboard.php" class="active">Dashboard</a>
        <a href="counter.php">Counter</a>
        <a href="sales.php">Sales Report</a>
        <form action="../../server/auth/logout.php" method="post" class="nav-logout">
            <button type="submit">Logout</button>
        </form>
    </nav>

    <!-- Desktop logout -->
    <div class="logout">
        <form action="../../server/auth/logout.php" method="post">
            <button type="submit">Logout</button>
        </form>
    </div>

    <!-- Hamburger on the far right -->
    <button class="hamburger" id="hamburger" aria-label="Toggle menu">
        <span></span>
        <span></span>
        <span></span>
    </button>
</header>

<div class="container">
    <form action="../../server/inventory/add.php" autocomplete="off" method="post">
        <input type="text"   name="item"     placeholder="Item name"     required>
        <select name="category" required>
    <option value="" disabled selected hidden>Select category</option>

    <!-- Food & Drinks -->
    <option value="rice_grains">Rice & Grains</option>
    <option value="canned_goods">Canned Goods</option>
    <option value="noodles_pasta">Noodles & Pasta</option>
    <option value="snacks">Snacks & Chips</option>
    <option value="bread_bakery">Bread & Bakery</option>
    <option value="condiments">Condiments & Sauces</option>
    <option value="cooking_oil">Cooking Oil & Vinegar</option>

    <!-- Beverages -->
    <option value="soft_drinks">Soft Drinks</option>
    <option value="water">Water & Juice</option>
    <option value="coffee_tea">Coffee & Tea</option>
    <option value="powdered_drinks">Powdered Drinks</option>

    <!-- Household -->
    <option value="cleaning">Cleaning Supplies</option>
    <option value="laundry">Laundry Products</option>
    <option value="dishwashing">Dishwashing</option>

    <!-- Personal Care -->
    <option value="hygiene">Personal Hygiene</option>
    <option value="hair_care">Hair Care</option>
    <option value="skin_care">Skin Care</option>

    <!-- Other -->
    <option value="school">School Supplies</option>
    <option value="medicine">Medicine & First Aid</option>
    <option value="load_eload">Load & E-load</option>
    <option value="others">Others</option>
</select>
        <input type="number" name="quantity" placeholder="Item quantity" min="0" required>
        <input type="number" name="cost"     placeholder="Item cost"     min="0" step="0.01" required>
        <input type="number" name="price"    placeholder="Item price"    min="0" step="0.01" required>
        <button type="submit" name="add">Add</button>
    </form>
</div>

<main class="table-wrapper">
    <div class="search-wrapper">
    <div class="search-container">
        <input type="text" id="itemSearch" placeholder="Search item...">
        <button type="button" id="searchBtn" onclick="searchItem()">Search</button>
    </div>
    </div>
    <table>
        <thead>
            <tr>
                <th>Item Name</th>
                <th>Category</th>
                <th>Quantity</th>
                <th>Cost</th>
                <th>Price</th>
                <th>Action</th>
            </tr>
        </thead>
        <!-- FIX 1: Added id="table-body" so AJAX can inject rows -->
        <tbody id="table-body">
            <?php if ($items): ?>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['product_name'],     ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($item['product_category'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= (int)$item['product_quantity'] ?></td>
                    <td><?= number_format($item['product_cost'],  2) ?></td>
                    <td><?= number_format($item['product_price'], 2) ?></td>
                    <td>
                        <div class="action">
                            <button type="button" class="editBtn"   data-idupdate="<?= (int)$item['product_id'] ?>">Edit</button>
                            <button type="button" class="deleteBtn" data-id="<?= (int)$item['product_id'] ?>">Delete</button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="6" style="text-align:center;">No items found</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div id="overlay"></div>

    <!-- Delete popup -->
    <div class="popup">
        <p>Are you sure you want to delete this item?</p>
        <form action="../../server/inventory/delete.php" method="post">
            <input type="hidden" name="id" id="deleteId">
            <div class="button-container">
                <button type="submit" class="red">Delete</button>
                <button id="cancelBtn" class="blue" type="button">Cancel</button>
            </div>
        </form>
    </div>


</main>
    <!-- Edit popup -->
    <div class="update">
        <form action="../../server/inventory/update.php" id="update" autocomplete="off" method="post">
            <input type="hidden" name="updateId" id="idupdate">
            <input type="text"   name="item"     placeholder="Item name"     required>
            <select name="category" required>
                <option value="" disabled selected hidden>Select category</option>
                <option value="food">Food</option>
                <option value="drinks">Drinks</option>
                <option value="canned">Canned</option>
                <option value="noodles">Noodles</option>
                <option value="snacks">Snacks</option>
                <option value="cleaning">Cleaning</option>
                <option value="others">Others</option>
            </select>
            <input type="number" name="quantity" placeholder="Item quantity" min="0" required>
            <input type="number" name="cost"     placeholder="Item cost"     min="0" step="0.01" required>
            <input type="number" name="price"    placeholder="Item price"    min="0" step="0.01" required>
            <div class="button-container">
                <button type="submit" class="blue" name="edit">Update</button>
                <button type="button" class="red"  id="cancel">Cancel</button>
            </div>
        </form>
    </div>
<script>
    let highlightedRow = null;

    function clearHighlight() {
        if (highlightedRow) {
            highlightedRow.classList.remove('highlighted');
            highlightedRow = null;
        }
    }

    // FIX 2: Removed the duplicate inline listeners above attachRowEvents().
    // FIX 3: Added clearHighlight() inside attachRowEvents() so it works after AJAX search too.
    function attachRowEvents() {
        document.querySelectorAll('.editBtn').forEach(btn => {
            btn.addEventListener('click', function () {
                clearHighlight();
                const row = this.closest('tr');
                row.classList.add('highlighted');
                highlightedRow = row;

                const updatePop = document.querySelector('.update');
                document.getElementById('idupdate').value = this.dataset.idupdate;
                updatePop.querySelector('input[name="item"]').value      = row.children[0].textContent.trim();
                updatePop.querySelector('select[name="category"]').value = row.children[1].textContent.trim();
                updatePop.querySelector('input[name="quantity"]').value  = row.children[2].textContent.trim();
                updatePop.querySelector('input[name="cost"]').value      = row.children[3].textContent.trim();
                updatePop.querySelector('input[name="price"]').value     = row.children[4].textContent.trim();

                updatePop.style.display = 'block';
                document.getElementById('overlay').style.display = 'block';
            });
        });

        document.querySelectorAll('.deleteBtn').forEach(btn => {
            btn.addEventListener('click', function () {
                clearHighlight();
                const row = this.closest('tr');
                row.classList.add('highlighted');
                highlightedRow = row;

                document.getElementById('deleteId').value = this.dataset.id;
                document.querySelector('.popup').style.display = 'block';
                document.getElementById('overlay').style.display = 'block';
            });
        });
    }

    document.getElementById('cancel').addEventListener('click', () => {
        document.querySelector('.update').style.display = 'none';
        document.getElementById('overlay').style.display = 'none';
        clearHighlight();
    });

    document.getElementById('cancelBtn').addEventListener('click', () => {
        document.querySelector('.popup').style.display = 'none';
        document.getElementById('overlay').style.display = 'none';
        clearHighlight();
    });

    function searchItem() {
    const search = document.getElementById("itemSearch").value;
    const xhr = new XMLHttpRequest();
    
    // Show a "Searching..." state on the button (Optional Polish)
    const btn = document.getElementById("searchBtn");
    const originalText = btn.innerText;
    btn.innerText = "Checking...";

    xhr.open("POST", "dashboard.php", true);
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    
    xhr.onload = function () {
        btn.innerText = originalText; // Reset button text
        if (this.status === 200) {
            document.getElementById("table-body").innerHTML = this.responseText;
            attachRowEvents(); // Re-attach edit/delete listeners to new rows
        }
    };
    xhr.send("ajax_search=" + encodeURIComponent(search));
}

// Allow "Enter" key to trigger the search button
document.getElementById("itemSearch").addEventListener("keypress", function(event) {
    if (event.key === "Enter") {
        event.preventDefault();
        searchItem();
    }
});

    // Attach events on initial page load
    attachRowEvents();
    // ─── Hamburger ─────────────────────────────────────────
    const hamburger = document.getElementById('hamburger');
    const navMenu   = document.getElementById('nav-menu');

    hamburger.addEventListener('click', () => {
        hamburger.classList.toggle('open');
        navMenu.classList.toggle('open');
    });
</script>

</body>
</html>