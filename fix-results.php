<?php
require_once 'includes/cloud-storage.php';

echo "<h2>Fix Database Results</h2>";

try {
    $studentManager = new StudentManager();
    
    // Get all students
    $students = $studentManager->getAllStudents();
    echo "<h3>Available Students:</h3>";
    foreach ($students as $student) {
        echo "<p>ID: {$student['id']}, Roll: {$student['roll_number']}, Name: {$student['name']}</p>";
    }
    
    // Get all results that have NULL student_id
    $results = $studentManager->getAllResults();
    echo "<h3>Results Before Fix:</h3>";
    foreach ($results as $result) {
        echo "<p>Result ID: {$result['id']}, Student ID: " . ($result['student_id'] ?? 'NULL') . ", Roll: {$result['roll_number']}, Semester: {$result['semester']}</p>";
    }
    
    // Fix the results by updating student_id based on roll_number
    $pdo = $studentManager->getConnection();
    
    echo "<h3>Fixing Results:</h3>";
    
    // Update results to set student_id based on roll_number
    $stmt = $pdo->prepare("
        UPDATE results 
        SET student_id = (
            SELECT id FROM students WHERE students.roll_number = results.roll_number
        ) 
        WHERE student_id IS NULL
    ");
    
    if ($stmt->execute()) {
        $affected = $stmt->rowCount();
        echo "<p>✅ Fixed {$affected} result records</p>";
    } else {
        echo "<p>❌ Failed to fix results</p>";
    }
    
    // Show results after fix
    $results = $studentManager->getAllResults();
    echo "<h3>Results After Fix:</h3>";
    foreach ($results as $result) {
        echo "<p>Result ID: {$result['id']}, Student ID: " . ($result['student_id'] ?? 'NULL') . ", Roll: {$result['roll_number']}, Semester: {$result['semester']}</p>";
    }
    
    // Now test the getStudentResult method
    echo "<h3>Testing Result Lookup:</h3>";
    $testResult = $studentManager->getStudentResult('1234567890', 1);
    if ($testResult) {
        echo "<p>✅ Found result for roll 1234567890, semester 1:</p>";
        echo "<pre>" . print_r($testResult, true) . "</pre>";
    } else {
        echo "<p>❌ Still not finding result for roll 1234567890, semester 1</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

echo "<p><a href='result.php'>🔍 Test Result Portal</a></p>";
echo "<p><a href='check-data.php'>📊 Check Database Content</a></p>";
?>