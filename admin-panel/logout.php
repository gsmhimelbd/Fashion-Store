<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
unset($_SESSION['admin_logged_in']);
unset($_SESSION['admin_id']);
unset($_SESSION['admin_name']);
unset($_SESSION['admin_username']);
session_destroy();
header('Location: login.php');
exit;
