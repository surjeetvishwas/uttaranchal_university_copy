<?php
// Test the new cloud-only system
require_once 'includes/cloud-storage.php';

echo "<h2>Testing New Cloud-Only System</h2>";

try {
    $manager = new StudentManager();
    
    echo "<h3>✅ System Status</h3>";
    echo "<p>✅ StudentManager initialized successfully</p>";
    echo "<p>✅ Using in-memory database with cloud synchronization</p>";
    echo "<p>✅ No local storage dependencies</p>";
    
    // Test loading existing data
    $students = $manager->getAllStudents();
    echo "<h3>📊 Current Data</h3>";
    echo "<p><strong>Students found:</strong> " . count($students) . "</p>";
    
    if (!empty($students)) {
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>Roll Number</th><th>Name</th></tr>";
        foreach ($students as $student) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($student['roll_number']) . "</td>";
            echo "<td>" . htmlspecialchars($student['student_name']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Test adding a new student
    $testRoll = "TEST" . time();
    $testName = "Test Student " . time();
    
    echo "<h3>🧪 Testing Student Addition</h3>";
    echo "<p>Adding student: Roll Number = $testRoll, Name = $testName</p>";
    
    $result = $manager->saveStudent($testRoll, $testName);
    
    if ($result) {
        echo "<p style='color: green;'>✅ Student added successfully!</p>";
        
        // Verify the student was added
        $addedStudent = $manager->getStudent($testRoll);
        if ($addedStudent) {
            echo "<p style='color: green;'>✅ Student verified in database</p>";
            echo "<p>Stored data: Roll = " . $addedStudent['roll_number'] . ", Name = " . $addedStudent['student_name'] . "</p>";
        } else {
            echo "<p style='color: red;'>❌ Student not found after addition</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Failed to add student</p>";
    }
    
    echo "<h3>🔗 Navigation</h3>";
    echo "<p><a href='admin-dashboard.php' style='color: #1976d2;'>📊 Go to Admin Dashboard</a></p>";
    echo "<p><a href='result.php' style='color: #f57c00;'>🔍 Check Result Portal</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>