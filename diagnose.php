<?php
require_once 'includes/cloud-storage.php';

// Check authentication first
if (!isset($_GET['action']) || $_GET['action'] !== 'diagnose') {
    die('Access denied. Use ?action=diagnose');
}

header('Content-Type: text/plain');

echo "=== DATABASE DIAGNOSTIC TOOL ===\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n\n";

try {
    echo "1. Creating CloudStorageHelper...\n";
    $storage = new CloudStorageHelper();
    
    echo "2. Getting database connection...\n";
    $db = $storage->getDatabaseConnection();
    
    echo "3. Checking if students table exists...\n";
    $tables = $db->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll();
    echo "   Tables found: " . count($tables) . "\n";
    foreach ($tables as $table) {
        echo "   - " . $table['name'] . "\n";
    }
    
    if (in_array('students', array_column($tables, 'name'))) {
        echo "\n4. Students table schema:\n";
        $columns = $db->query("PRAGMA table_info(students)")->fetchAll();
        foreach ($columns as $col) {
            echo sprintf("   %-15s %-12s NotNull:%-1s Default:%-10s PK:%-1s\n", 
                $col['name'], 
                $col['type'], 
                $col['notnull'] ? 'Y' : 'N',
                $col['dflt_value'] ?: 'NULL',
                $col['pk'] ? 'Y' : 'N'
            );
        }
        
        echo "\n5. Current students data:\n";
        $students = $db->query("SELECT * FROM students LIMIT 5")->fetchAll();
        echo "   Count: " . count($students) . "\n";
        foreach ($students as $student) {
            echo "   Roll: " . $student['roll_number'];
            if (isset($student['name'])) echo " | Name: " . $student['name'];
            if (isset($student['student_name'])) echo " | StudentName: " . $student['student_name'];
            echo "\n";
        }
    } else {
        echo "\n4. Students table does not exist. Creating...\n";
        $db->exec("
            CREATE TABLE students (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                roll_number VARCHAR(50) UNIQUE NOT NULL,
                student_name VARCHAR(100) NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
        echo "   Students table created successfully.\n";
    }
    
    echo "\n6. Testing INSERT operation:\n";
    $testRoll = 'TEST' . time();
    $testName = 'Test Student ' . date('H:i:s');
    
    try {
        $stmt = $db->prepare("INSERT INTO students (roll_number, student_name) VALUES (?, ?)");
        $result = $stmt->execute([$testRoll, $testName]);
        
        if ($result) {
            echo "   ✅ INSERT successful with student_name column\n";
            
            // Try to upload to cloud
            $uploadResult = $storage->saveDatabase($db);
            if ($uploadResult) {
                echo "   ✅ Database uploaded to cloud successfully\n";
            } else {
                echo "   ❌ Database upload to cloud failed\n";
            }
        } else {
            echo "   ❌ INSERT failed\n";
        }
    } catch (Exception $e) {
        echo "   ❌ INSERT error: " . $e->getMessage() . "\n";
        
        // Try with 'name' column
        try {
            $stmt = $db->prepare("INSERT INTO students (roll_number, name) VALUES (?, ?)");
            $result = $stmt->execute([$testRoll, $testName]);
            
            if ($result) {
                echo "   ✅ INSERT successful with 'name' column\n";
            } else {
                echo "   ❌ INSERT with 'name' column also failed\n";
            }
        } catch (Exception $e2) {
            echo "   ❌ INSERT with 'name' column error: " . $e2->getMessage() . "\n";
        }
    }
    
    echo "\n=== DIAGNOSIS COMPLETE ===\n";
    
} catch (Exception $e) {
    echo "FATAL ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>