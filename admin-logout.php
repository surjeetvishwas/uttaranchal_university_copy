<?php
session_start();

// Destroy the session
session_destroy();

// Redirect to admin login
header('Location: admin.php');
exit();
?>