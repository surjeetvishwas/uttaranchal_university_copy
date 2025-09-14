<?php
require_once 'includes/cloud-storage.php';

echo "<h2>Database Debugging - Result Search Issue</h2>";

// Test the exact scenario from the screenshot
$testRollNumber = "1234567890";
$testSemester = 1;
$testStudentName = "avinash";

echo "<h3>Testing with your exact input:</h3>";
echo "<p>Roll Number: <strong>$testRollNumber</strong></p>";
echo "<p>Semester: <strong>$testSemester</strong></p>";
echo "<p>Student Name: <strong>$testStudentName</strong></p>";

try {
    $studentManager = new StudentManager();
    
    echo "<h3>Step 1: Check if student exists</h3>";
    $student = $studentManager->getStudent($testRollNumber);
    if ($student) {
        echo "<p>✅ Student found!</p>";
        echo "<pre>";
        print_r($student);
        echo "</pre>";
        
        // Check name match
        if (strtolower($student['name']) === strtolower($testStudentName)) {
            echo "<p>✅ Student name matches!</p>";
        } else {
            echo "<p>❌ Student name does NOT match!</p>";
            echo "<p>Database name: '" . $student['name'] . "'</p>";
            echo "<p>Provided name: '$testStudentName'</p>";
            echo "<p>Case-insensitive comparison: " . (strtolower($student['name']) === strtolower($testStudentName) ? 'MATCH' : 'NO MATCH') . "</p>";
        }
    } else {
        echo "<p>❌ Student NOT found!</p>";
    }
    
    echo "<h3>Step 2: Check if result exists</h3>";
    $result = $studentManager->getStudentResult($testRollNumber, $testSemester);
    if ($result) {
        echo "<p>✅ Result found!</p>";
        echo "<pre>";
        print_r($result);
        echo "</pre>";
    } else {
        echo "<p>❌ Result NOT found!</p>";
    }
    
    echo "<h3>Step 3: Show all students in database</h3>";
    $allStudents = $studentManager->getAllStudents();
    if ($allStudents) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Roll Number</th><th>Name</th></tr>";
        foreach ($allStudents as $s) {
            $highlight = ($s['roll_number'] == $testRollNumber) ? "background: yellow;" : "";
            echo "<tr style='$highlight'>";
            echo "<td>" . htmlspecialchars($s['id']) . "</td>";
            echo "<td>" . htmlspecialchars($s['roll_number']) . "</td>";
            echo "<td>" . htmlspecialchars($s['name']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>❌ No students found!</p>";
    }
    
    echo "<h3>Step 4: Show all results in database</h3>";
    // Let's create a simple query to get all results
    $db = $studentManager->db;
    $stmt = $db->prepare("SELECT * FROM results");
    $stmt->execute();
    $allResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($allResults) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Roll Number</th><th>Semester</th><th>File Path</th></tr>";
        foreach ($allResults as $r) {
            $highlight = ($r['roll_number'] == $testRollNumber && $r['semester'] == $testSemester) ? "background: yellow;" : "";
            echo "<tr style='$highlight'>";
            echo "<td>" . htmlspecialchars($r['id']) . "</td>";
            echo "<td>" . htmlspecialchars($r['roll_number']) . "</td>";
            echo "<td>" . htmlspecialchars($r['semester']) . "</td>";
            echo "<td>" . htmlspecialchars($r['file_path']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>❌ No results found!</p>";
    }
    
    echo "<h3>Step 5: Test the exact SQL query</h3>";
    $stmt = $db->prepare("
        SELECT r.*, s.name
        FROM results r 
        LEFT JOIN students s ON r.roll_number = s.roll_number 
        WHERE r.roll_number = ? AND r.semester = ?
    ");
    $stmt->execute([$testRollNumber, $testSemester]);
    $directResult = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($directResult) {
        echo "<p>✅ Direct SQL query found result!</p>";
        echo "<pre>";
        print_r($directResult);
        echo "</pre>";
    } else {
        echo "<p>❌ Direct SQL query found NO result!</p>";
        
        // Try without semester filter
        echo "<h4>Trying without semester filter:</h4>";
        $stmt2 = $db->prepare("
            SELECT r.*, s.name
            FROM results r 
            LEFT JOIN students s ON r.roll_number = s.roll_number 
            WHERE r.roll_number = ?
        ");
        $stmt2->execute([$testRollNumber]);
        $allResultsForStudent = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        
        if ($allResultsForStudent) {
            echo "<p>Found " . count($allResultsForStudent) . " results for this roll number:</p>";
            echo "<pre>";
            print_r($allResultsForStudent);
            echo "</pre>";
        } else {
            echo "<p>No results at all for this roll number!</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<h3>Quick Fix Actions:</h3>";
echo "<p><a href='add-test-data.php'>Add Test Data</a> | <a href='admin-dashboard.php'>Admin Dashboard</a></p>";
?>