<?php
require_once 'includes/cloud-storage.php';

echo "<h2>Database Test and Upload</h2>";

try {
    echo "<p>1. Creating StudentManager...</p>";
    $studentManager = new StudentManager();
    
    echo "<p>2. Adding test student...</p>";
    $result = $studentManager->saveStudent('TEST123', 'Test Student');
    
    if ($result) {
        echo "<p>✅ Student added successfully!</p>";
    } else {
        echo "<p>❌ Failed to add student</p>";
    }
    
    echo "<p>3. Adding test result...</p>";
    $resultAdded = $studentManager->addStudentResult('TEST123', 1, 'UUD/TEST123/sem1.pdf');
    
    if ($resultAdded) {
        echo "<p>✅ Result added successfully!</p>";
    } else {
        echo "<p>❌ Failed to add result</p>";
    }
    
    echo "<p>4. Verifying data...</p>";
    $student = $studentManager->getStudent('TEST123');
    if ($student) {
        echo "<p>✅ Student retrieved: " . htmlspecialchars($student['student_name']) . "</p>";
    } else {
        echo "<p>❌ Student not found</p>";
    }
    
    $results = $studentManager->getStudentResults('TEST123');
    echo "<p>✅ Found " . count($results) . " results for student</p>";
    
    echo "<p>5. Database operations completed.</p>";
    echo "<p>Check the error logs and gs://resultexyx/UUD/mapping.db for the uploaded database.</p>";
    
} catch (Exception $e) {
    echo "<p>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
?>