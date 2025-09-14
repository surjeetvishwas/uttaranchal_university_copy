<?php
require_once 'includes/cloud-storage.php';

echo "<h2>Database Content Check</h2>";

try {
    $studentManager = new StudentManager();
    
    echo "<h3>Students in Database:</h3>";
    $students = $studentManager->getAllStudents();
    if (empty($students)) {
        echo "❌ No students found in database<br>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
        echo "<tr style='background: #f0f0f0;'><th>ID</th><th>Roll Number</th><th>Name</th></tr>";
        foreach ($students as $student) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($student['id']) . "</td>";
            echo "<td>" . htmlspecialchars($student['roll_number']) . "</td>";
            echo "<td>" . htmlspecialchars($student['name']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<h3>Results in Database:</h3>";
    $results = $studentManager->getAllResults();
    if (empty($results)) {
        echo "<div style='background: #ffebee; padding: 15px; border-radius: 5px; color: #c62828;'>";
        echo "❌ <strong>No results found in database!</strong><br>";
        echo "This is why you're getting 'Result not found for the selected semester' error.<br>";
        echo "You need to add result data for your students.";
        echo "</div>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
        echo "<tr style='background: #f0f0f0;'><th>ID</th><th>Student ID</th><th>Semester</th><th>File Path</th></tr>";
        foreach ($results as $result) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($result['id']) . "</td>";
            echo "<td>" . htmlspecialchars($result['student_id']) . "</td>";
            echo "<td>" . htmlspecialchars($result['semester']) . "</td>";
            echo "<td>" . htmlspecialchars($result['file_path']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #ffebee; padding: 15px; border-radius: 5px; color: #c62828;'>";
    echo "❌ Error: " . $e->getMessage();
    echo "</div>";
}

echo "<h3>Actions:</h3>";
echo "<p><a href='admin-dashboard.php' style='color: #1976d2;'>📊 Go to Admin Dashboard</a> to add students and results</p>";
echo "<p><a href='add-test-data.php' style='color: #388e3c;'>🧪 Add Test Data</a> with sample results</p>";
echo "<p><a href='result.php' style='color: #f57c00;'>🔍 Check Results</a> page</p>";
?>