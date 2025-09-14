<?php
require_once 'includes/cloud-storage.php';

// Simple verification script
header('Content-Type: text/plain');

echo "=== DATABASE VERIFICATION ===\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n";
echo "Environment: " . (isset($_SERVER['K_SERVICE']) ? 'Cloud Run' : 'Local') . "\n\n";

try {
    $studentManager = new StudentManager();
    
    // Check if we can connect to database
    echo "✅ Database connection successful\n";
    
    // Get all students
    $students = $studentManager->getAllStudents();
    echo "📊 Total students: " . count($students) . "\n";
    
    foreach ($students as $student) {
        echo "👤 Student: " . $student['roll_number'] . " - " . $student['student_name'] . "\n";
        
        $results = $studentManager->getStudentResults($student['roll_number']);
        echo "   📋 Results: " . count($results) . " semesters\n";
        
        foreach ($results as $result) {
            echo "   📄 Semester " . $result['semester'] . ": " . $result['file_path'] . "\n";
        }
    }
    
    echo "\n=== CLOUD STORAGE STATUS ===\n";
    $cloudStorage = new CloudStorageHelper();
    echo "🔗 Target: gs://resultexyx/UUD/mapping.db\n";
    echo "🔑 Bucket: resultexyx\n";
    echo "📁 Base folder: UUD\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "📍 Line: " . $e->getLine() . "\n";
    echo "📂 File: " . $e->getFile() . "\n";
}

echo "\n=== END VERIFICATION ===\n";
?>