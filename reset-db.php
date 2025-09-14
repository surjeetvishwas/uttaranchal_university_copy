<?php
require_once 'includes/cloud-storage.php';

// Security check
if (!isset($_GET['reset']) || $_GET['reset'] !== 'database') {
    die('Access denied. Use ?reset=database to reset the database.');
}

header('Content-Type: text/plain');

echo "=== DATABASE RESET TOOL ===\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n\n";

try {
    echo "1. Creating fresh database connection...\n";
    $storage = new CloudStorageHelper();
    
    // Get a fresh database path
    $dbPath = '/tmp/fresh_mapping_' . time() . '.db';
    $db = new PDO('sqlite:' . $dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "2. Creating fresh tables with correct schema...\n";
    
    // Create students table with both name columns for compatibility
    $db->exec("
        CREATE TABLE students (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            roll_number VARCHAR(50) UNIQUE NOT NULL,
            student_name VARCHAR(100) NOT NULL,
            name VARCHAR(100) NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    // Create results table
    $db->exec("
        CREATE TABLE results (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            roll_number VARCHAR(50) NOT NULL,
            semester INTEGER NOT NULL,
            file_path VARCHAR(255) NOT NULL,
            uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (roll_number) REFERENCES students(roll_number),
            UNIQUE(roll_number, semester)
        )
    ");
    
    echo "3. Tables created successfully.\n";
    
    echo "4. Adding test data...\n";
    $stmt = $db->prepare("INSERT INTO students (roll_number, student_name, name) VALUES (?, ?, ?)");
    $stmt->execute(['1234567890', 'avinash', 'avinash']);
    
    $stmt = $db->prepare("INSERT INTO results (roll_number, semester, file_path) VALUES (?, ?, ?)");
    $stmt->execute(['1234567890', 1, 'UUD/1234567890/sem1.pdf']);
    $stmt->execute(['1234567890', 2, 'UUD/1234567890/sem2.pdf']);
    $stmt->execute(['1234567890', 3, 'UUD/1234567890/sem3.pdf']);
    
    echo "5. Test data added.\n";
    
    echo "6. Uploading database to cloud storage...\n";
    $dbContent = file_get_contents($dbPath);
    $uploadResult = $storage->uploadDatabase($dbContent);
    
    if ($uploadResult) {
        echo "   ✅ Database uploaded to gs://resultexyx/UUD/mapping.db successfully!\n";
    } else {
        echo "   ❌ Failed to upload database to cloud storage.\n";
    }
    
    echo "\n7. Verification:\n";
    $students = $db->query("SELECT COUNT(*) FROM students")->fetchColumn();
    $results = $db->query("SELECT COUNT(*) FROM results")->fetchColumn();
    
    echo "   Students: $students\n";
    echo "   Results: $results\n";
    
    // Clean up
    unlink($dbPath);
    
    echo "\n=== RESET COMPLETE ===\n";
    echo "The database has been reset and uploaded to cloud storage.\n";
    echo "You can now try adding students through the admin panel.\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>