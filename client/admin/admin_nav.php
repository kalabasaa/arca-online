<?php
$current = basename($_SERVER['PHP_SELF']);
?>
<header>
    <div class="logo"><img src="../assets/img/arca.png" alt="Arca Logo"></div>
    <nav>
        <a href="admin.php"     class="<?= $current==='admin.php'     ? 'active' : '' ?>">Users</a>
        <a href="sales.php"     class="<?= $current==='sales.php'     ? 'active' : '' ?>">Sales</a>
        <a href="inventory.php" class="<?= $current==='inventory.php' ? 'active' : '' ?>">Inventory</a>
        <a href="logs.php"      class="<?= $current==='logs.php'      ? 'active' : '' ?>">Logs</a>
    </nav>
    <div class="logout">
        <form action="../../server/auth/logout.php" method="post">
            <button type="submit">Logout</button>
        </form>
    </div>
</header>