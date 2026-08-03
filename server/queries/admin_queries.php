<?php


function get_allowed_sort(): array {
    return [
        'name'  => 'u.user_name ASC',
        'date'  => 'u.user_date ASC',
        'sales' => 'total_sales DESC',
    ];
}

function get_all_users(mysqli $conn, string $sort): array {
    $allowed  = get_allowed_sort();
    $order_by = $allowed[$sort] ?? 'u.user_id ASC';
    $stmt = $conn->prepare("
        SELECT u.user_id, u.user_name, u.user_date,
               IFNULL(SUM(s.total_sales), 0) AS total_sales
        FROM users u
        LEFT JOIN sales s ON u.user_id = s.user_id_fk
        WHERE u.user_name != 'admin'
        GROUP BY u.user_id
        ORDER BY $order_by
    ");
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

function delete_user(mysqli $conn, int $user_id): bool {
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ? AND user_name != 'admin'");
    $stmt->bind_param("i", $user_id);
    $ok = $stmt->execute() && $stmt->affected_rows > 0;
    $stmt->close();
    return $ok;
}

/* ── ADMIN STATS ── */

function get_admin_stats(mysqli $conn): array {
    $stats = [];
    $r = $conn->query("SELECT COUNT(*) AS c FROM users WHERE user_name != 'admin'");
    $stats['total_users'] = (int)$r->fetch_assoc()['c'];
    $r = $conn->query("SELECT IFNULL(SUM(total_sales),0) AS c FROM sales");
    $stats['total_sales'] = (float)$r->fetch_assoc()['c'];
    $r = $conn->query("SELECT IFNULL(SUM(total_cost),0) AS c FROM sales");
    $stats['total_cost'] = (float)$r->fetch_assoc()['c'];
    $r = $conn->query("SELECT COUNT(*) AS c FROM items");
    $stats['total_items'] = (int)$r->fetch_assoc()['c'];
    return $stats;
}

/* ── ADMIN SALES ── */

function get_admin_sales(mysqli $conn, string $sort, string $date_from, string $date_to): array {
    $allowed_sort = [
        'date'  => 's.sale_date DESC',
        'user'  => 'u.user_name ASC',
        'sales' => 's.total_sales DESC',
    ];
    $order_by = $allowed_sort[$sort] ?? 's.sale_date DESC';
    $stmt = $conn->prepare("
        SELECT s.sale_date, s.item_name, s.total_items, s.total_cost, s.total_sales, u.user_name
        FROM sales s
        JOIN users u ON s.user_id_fk = u.user_id
        WHERE DATE(s.sale_date) BETWEEN ? AND ?
        ORDER BY $order_by
    ");
    $stmt->bind_param("ss", $date_from, $date_to);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

function get_sales_by_user(mysqli $conn, string $date_from, string $date_to): array {
    $stmt = $conn->prepare("
        SELECT u.user_name,
               IFNULL(SUM(s.total_sales), 0) AS total_sales,
               IFNULL(SUM(s.total_cost),  0) AS total_cost,
               COUNT(s.sale_id)               AS transactions
        FROM users u
        LEFT JOIN sales s ON u.user_id = s.user_id_fk
            AND DATE(s.sale_date) BETWEEN ? AND ?
        WHERE u.user_name != 'admin'
        GROUP BY u.user_id
        ORDER BY total_sales DESC
    ");
    $stmt->bind_param("ss", $date_from, $date_to);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

/* ── ADMIN LOGS ── */

function get_logs(mysqli $conn, string $filter_action, int $limit, int $offset): array {
    $allowed_actions = ['', 'login_success', 'login_fail', 'logout', 'signup', 'push', 'item_add', 'item_delete', 'user_delete'];
    if (!in_array($filter_action, $allowed_actions, true)) $filter_action = '';

    if ($filter_action !== '') {
        $stmt = $conn->prepare("
            SELECT log_id, user_id, user_name, action, description, ip_address, created_at
            FROM activity_logs WHERE action = ?
            ORDER BY created_at DESC LIMIT ? OFFSET ?
        ");
        $stmt->bind_param("sii", $filter_action, $limit, $offset);
    } else {
        $stmt = $conn->prepare("
            SELECT log_id, user_id, user_name, action, description, ip_address, created_at
            FROM activity_logs
            ORDER BY created_at DESC LIMIT ? OFFSET ?
        ");
        $stmt->bind_param("ii", $limit, $offset);
    }
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

function get_logs_count(mysqli $conn, string $filter_action): int {
    $allowed_actions = ['', 'login_success', 'login_fail', 'logout', 'signup', 'push', 'item_add', 'item_delete', 'user_delete'];
    if (!in_array($filter_action, $allowed_actions, true)) $filter_action = '';

    if ($filter_action !== '') {
        $stmt = $conn->prepare("SELECT COUNT(*) AS c FROM activity_logs WHERE action = ?");
        $stmt->bind_param("s", $filter_action);
    } else {
        $stmt = $conn->prepare("SELECT COUNT(*) AS c FROM activity_logs");
    }
    $stmt->execute();
    $count = (int)$stmt->get_result()->fetch_assoc()['c'];
    $stmt->close();
    return $count;
}

/* ── ADMIN INVENTORY ── */

function get_admin_inventory(mysqli $conn, string $filter_user, string $filter_category, string $sort): array {
    $allowed_sort = [
        'name'     => 'i.product_name ASC',
        'qty'      => 'i.product_quantity ASC',
        'price'    => 'i.product_price DESC',
        'user'     => 'u.user_name ASC',
        'category' => 'i.product_category ASC',
    ];
    $order_by = $allowed_sort[$sort] ?? 'i.product_name ASC';

    $where  = ["u.user_name != 'admin'"];
    $params = [];
    $types  = '';

    if ($filter_user !== '') {
        $where[]  = 'u.user_name = ?';
        $params[] = $filter_user;
        $types   .= 's';
    }
    if ($filter_category !== '') {
        $where[]  = 'i.product_category = ?';
        $params[] = $filter_category;
        $types   .= 's';
    }

    $where_sql = 'WHERE ' . implode(' AND ', $where);
    $stmt = $conn->prepare("
        SELECT i.product_id, i.product_name, i.product_category,
               i.product_quantity, i.product_cost, i.product_price, u.user_name
        FROM items i
        JOIN users u ON i.user_id_fk = u.user_id
        $where_sql
        ORDER BY $order_by
    ");
    if ($params) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

function get_inventory_users(mysqli $conn): array {
    $stmt = $conn->prepare("
        SELECT DISTINCT u.user_name FROM items i
        JOIN users u ON i.user_id_fk = u.user_id
        WHERE u.user_name != 'admin'
        ORDER BY u.user_name ASC
    ");
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return array_column($rows, 'user_name');
}