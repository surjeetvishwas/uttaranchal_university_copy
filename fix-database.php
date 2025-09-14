<?php
require_once 'includes/cloud-storage.php';

echo "<h2>Fix Database Results</h2>";

try {
    $studentManager = new StudentManager();
    
    echo "<h3>Available Students:</h3>";
    $students = $studentManager->getAllStudents();
    foreach ($students as $student) {
        echo "<p>ID: {$student['id']}, Roll: {$student['roll_number']}, Name: {$student['name']}</p>";
    }
    
    echo "<h3>Results Before Fix:</h3>";
    $results = $studentManager->getAllResults();
    foreach ($results as $result) {
        echo "<p>Result ID: {$result['id']}, Roll: {$result['roll_number']}, Semester: {$result['semester']}</p>";
    }
    
    echo "<h3>Fixing Results:</h3>";

    try {
        // First, let's check the actual schema
        echo "<h4>Students Table Schema:</h4>";
        $stmt = $studentManager->getConnection()->prepare("PRAGMA table_info(students)");
        $stmt->execute();
        $studentColumns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<ul>";
        foreach ($studentColumns as $col) {
            echo "<li>{$col['name']} ({$col['type']})</li>";
        }
        echo "</ul>";
        
        echo "<h4>Results Table Schema:</h4>";
        $stmt = $studentManager->getConnection()->prepare("PRAGMA table_info(results)");
        $stmt->execute();
        $resultColumns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<ul>";
        foreach ($resultColumns as $col) {
            echo "<li>{$col['name']} ({$col['type']})</li>";
        }
        echo "</ul>";
        
        // Now let's use the correct column name
        $stmt = $studentManager->getConnection()->prepare("
            SELECT r.*, s.student_name as student_name, s.id as student_db_id
            FROM results r 
            LEFT JOIN students s ON r.roll_number = s.roll_number
        ");
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($results)) {
            echo "<p>❌ No results found in database</p>";
        } else {
            echo "<p>✅ Found " . count($results) . " results in database</p>";
            
            foreach ($results as $result) {
                if (!$result['student_name']) {
                    echo "<p>⚠️ Result ID {$result['id']} has roll number {$result['roll_number']} but no matching student</p>";
                } else {
                    echo "<p>✅ Result ID {$result['id']} - Roll: {$result['roll_number']} - Student: {$result['student_name']} - Semester: {$result['semester']}</p>";
                }
            }
        }
        
        echo "<h3>Testing getStudentResult Method:</h3>";
        
        // Test the getStudentResult method
        $testResult = $studentManager->getStudentResult('1234567890', 1);
        if ($testResult) {
            echo "<p>✅ getStudentResult('1234567890', 1) works!</p>";
            echo "<pre>";
            print_r($testResult);
            echo "</pre>";
        } else {
            echo "<p>❌ getStudentResult('1234567890', 1) failed</p>";
        }
        
    } catch (Exception $e) {
        echo "<p>❌ Error: " . $e->getMessage() . "</p>";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}

echo "<h3>Actions:</h3>";
echo "<p><a href='result.php'>🔍 Test Result Portal</a></p>";
echo "<p><a href='check-data.php'>📊 Check Database Content</a></p>";
?>