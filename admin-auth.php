<?php
session_start();

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $admin_id = $_POST['admin_id'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Check credentials
    if ($admin_id === 'admin' && $password === 'Rudrapur@123') {
        // Set session
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_id'] = $admin_id;
        $_SESSION['login_time'] = time();
        
        // Redirect to dashboard
        header('Location: admin-dashboard.php');
        exit();
    } else {
        // Invalid credentials
        header('Location: admin.php?error=1');
        exit();
    }
} else {
    // Invalid request method
    header('Location: admin.php');
    exit();
}
?>