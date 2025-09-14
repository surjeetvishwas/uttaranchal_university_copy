<?php
require_once 'includes/cloud-storage.php';

try {
    echo "=== DIAGNOSTIC SCRIPT ===\n";
    
    $studentManager = new StudentManager();
    
    // Test 1: Check database connection
    echo "1. Testing database connection...\n";
    $students = $studentManager->getAllStudents();
    echo "   ✅ Database connected successfully\n";
    echo "   Found " . count($students) . " students\n";
    
    // Test 2: Show students
    echo "\n2. Students in database:\n";
    foreach ($students as $student) {
        echo "   Roll: " . $student['roll_number'] . ", Name: " . $student['student_name'] . "\n";
    }
    
    // Test 3: Test specific student
    echo "\n3. Testing specific student (1234567890):\n";
    $student = $studentManager->getStudent('1234567890');
    if ($student) {
        echo "   ✅ Student found: " . $student['student_name'] . "\n";
    } else {
        echo "   ❌ Student not found\n";
    }
    
    // Test 4: Test results query
    echo "\n4. Testing results query:\n";
    $results = $studentManager->getStudentResults('1234567890');
    echo "   Found " . count($results) . " results\n";
    foreach ($results as $result) {
        echo "   Semester: " . $result['semester'] . ", File: " . $result['file_path'] . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>