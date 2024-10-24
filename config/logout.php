<?php
session_start();
session_unset();
session_destroy();
if (isset($_COOKIE['token'])) {
    unset($_COOKIE['token']);
    setcookie('token', '', time() - 3600, '/'); // Expira la cookie
}
header("Location: ../index.php");
exit();
?>