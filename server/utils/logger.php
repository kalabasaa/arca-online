<?php

if (!isset($_SESSION['user_name']) || $_SESSION['user_name'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}