<?php
// Test script to verify result retrieval works
require_once 'includes/cloud-storage.php';

echo "<h2>Testing Result Retrieval</h2>";

$manager = new StudentManager();

// Test with our known data
$roll_number = "1234567890";
$semester = "1";

echo "<h3>Testing with Roll: $roll_number, Semester: $semester</h3>";

try {
    $result = $manager->getStudentResult($roll_number, $semester);
    
    if ($result) {
        echo "<p style='color: green;'>✅ SUCCESS: Result found!</p>";
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>Roll Number</th><th>Student Name</th><th>Semester</th><th>File Path</th></tr>";
        echo "<tr>";
        echo "<td>" . htmlspecialchars($result['roll_number']) . "</td>";
        echo "<td>" . htmlspecialchars($result['name']) . "</td>";
        echo "<td>" . htmlspecialchars($result['semester']) . "</td>";
        echo "<td>" . htmlspecialchars($result['file_path']) . "</td>";
        echo "</tr>";
        echo "</table>";
    } else {
        echo "<p style='color: red;'>❌ FAILURE: No result found</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ ERROR: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Test with semester 3 (also exists)
$semester = "3";
echo "<h3>Testing with Roll: $roll_number, Semester: $semester</h3>";

try {
    $result = $manager->getStudentResult($roll_number, $semester);
    
    if ($result) {
        echo "<p style='color: green;'>✅ SUCCESS: Result found!</p>";
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>Roll Number</th><th>Student Name</th><th>Semester</th><th>File Path</th></tr>";
        echo "<tr>";
        echo "<td>" . htmlspecialchars($result['roll_number']) . "</td>";
        echo "<td>" . htmlspecialchars($result['name']) . "</td>";
        echo "<td>" . htmlspecialchars($result['semester']) . "</td>";
        echo "<td>" . htmlspecialchars($result['file_path']) . "</td>";
        echo "</tr>";
        echo "</table>";
    } else {
        echo "<p style='color: red;'>❌ FAILURE: No result found</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ ERROR: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Test with semester 2 (does not exist)
$semester = "2";
echo "<h3>Testing with Roll: $roll_number, Semester: $semester (Should not exist)</h3>";

try {
    $result = $manager->getStudentResult($roll_number, $semester);
    
    if ($result) {
        echo "<p style='color: orange;'>⚠️ UNEXPECTED: Result found when it shouldn't exist!</p>";
    } else {
        echo "<p style='color: green;'>✅ CORRECT: No result found (as expected)</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ ERROR: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<h3>Links:</h3>";
echo "<p><a href='result.php'>📄 Go to Result Page</a></p>";
echo "<p><a href='fix-database.php'>🔧 Database Diagnostics</a></p>";
?>