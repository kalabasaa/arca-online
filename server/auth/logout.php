<?php
session_start();
require_once __DIR__ . '/../../config/connect.php';

// Assuming user ID is stored in session, e.g., $_SESSION['user_id']
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];

    // Get current datetime
    $currentTime = date('Y-m-d');

    // Prepare and execute the update query to set last_login time
    $stmt = $conn->prepare("UPDATE users SET last_login = ? WHERE user_id = ?");
    $stmt->bind_param("si", $currentTime, $userId);
    $stmt->execute();
    $stmt->close();
}

// Destroy the session
session_destroy();
<<<<<<< HEAD


=======
>>>>>>> a5115f5088647e6121812f3bd64b6f7b326b24d3


// Redirect to login page
header("Location: ../../client/auth/login.php");


<<<<<<< HEAD
=======

>>>>>>> a5115f5088647e6121812f3bd64b6f7b326b24d3
exit();
?>