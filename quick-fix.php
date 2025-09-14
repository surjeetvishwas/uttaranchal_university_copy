<?php
// Quick diagnostic and fix script
require_once 'includes/cloud-storage.php';

echo "<h2>Database Diagnostic & Quick Fix</h2>";

try {
    $studentManager = new StudentManager();
    
    // Check what students exist
    echo "<h3>Students in Database:</h3>";
    $students = $studentManager->getAllStudents();
    
    if (empty($students)) {
        echo "<p style='color: red;'>❌ No students found. Please add students via admin dashboard first.</p>";
        echo "<p><a href='admin-dashboard.php'>Go to Admin Dashboard</a></p>";
        exit;
    }
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f0f0f0;'><th>ID</th><th>Roll Number</th><th>Name</th></tr>";
    foreach ($students as $student) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($student['id']) . "</td>";
        echo "<td>" . htmlspecialchars($student['roll_number']) . "</td>";
        echo "<td>" . htmlspecialchars($student['name']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Check what results exist
    echo "<h3>Results in Database:</h3>";
    $results = $studentManager->getAllResults();
    
    if (empty($results)) {
        echo "<div style='background: #ffebee; padding: 15px; border-radius: 5px; color: #c62828; margin: 20px 0;'>";
        echo "<strong>❌ NO RESULTS FOUND!</strong><br>";
        echo "This is why you're getting 'Result not found for the selected semester'.<br>";
        echo "Click the button below to add test results for all students.";
        echo "</div>";
        
        // Add quick fix button
        if (isset($_POST['add_test_results'])) {
            echo "<h3>Adding Test Results...</h3>";
            
            // Simple PDF content template
            $pdfTemplate = "%PDF-1.4
1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj
2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj
3 0 obj<</Type/Page/Parent 2 0 R/MediaBox[0 0 612 792]/Contents 4 0 R>>endobj
4 0 obj<</Length 100>>stream
BT/Helvetica 12 Tf 50 750 Td ({STUDENT_NAME} - Roll: {ROLL_NUMBER}) Tj 0 -20 Td (Semester: {SEMESTER} - Result: PASS) Tj ET
endstream endobj
xref 0 5 0000000000 65535 f 0000000009 00000 n 0000000058 00000 n 0000000115 00000 n 0000000226 00000 n trailer<</Size 5/Root 1 0 R>>startxref 326 %%EOF";
            
            $addedCount = 0;
            foreach ($students as $student) {
                for ($sem = 1; $sem <= 3; $sem++) {
                    // Create PDF content
                    $pdfContent = str_replace(
                        ['{STUDENT_NAME}', '{ROLL_NUMBER}', '{SEMESTER}'],
                        [$student['name'], $student['roll_number'], $sem],
                        $pdfTemplate
                    );
                    
                    // Save as local file
                    $fileName = "result_" . $student['roll_number'] . "_sem_" . $sem . ".pdf";
                    $filePath = "uploads/" . $fileName;
                    
                    if (!file_exists('uploads')) {
                        mkdir('uploads', 0777, true);
                    }
                    
                    file_put_contents($filePath, $pdfContent);
                    
                    // Add to database
                    $success = $studentManager->addStudentResult($student['id'], $sem, $filePath);
                    if ($success) {
                        echo "<p>✅ Added result for {$student['roll_number']} - Semester {$sem}</p>";
                        $addedCount++;
                    } else {
                        echo "<p>❌ Failed to add result for {$student['roll_number']} - Semester {$sem}</p>";
                    }
                }
            }
            
            echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 5px; color: #2e7d32; margin: 20px 0;'>";
            echo "<strong>✅ Added {$addedCount} test results!</strong><br>";
            echo "Now try checking results again.";
            echo "</div>";
            
        } else {
            echo "<form method='POST' style='margin: 20px 0;'>";
            echo "<button type='submit' name='add_test_results' style='background: #4caf50; color: white; padding: 15px 30px; border: none; border-radius: 5px; font-size: 16px; cursor: pointer;'>";
            echo "🔧 Add Test Results for All Students";
            echo "</button>";
            echo "</form>";
        }
        
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f0f0f0;'><th>ID</th><th>Roll Number</th><th>Semester</th><th>File Path</th></tr>";
        foreach ($results as $result) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($result['id']) . "</td>";
            echo "<td>" . htmlspecialchars($result['roll_number']) . "</td>";
            echo "<td>" . htmlspecialchars($result['semester']) . "</td>";
            echo "<td>" . htmlspecialchars($result['file_path']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 5px; color: #2e7d32; margin: 20px 0;'>";
        echo "<strong>✅ Results found in database!</strong><br>";
        echo "The issue might be with the query logic. Try testing with the exact data shown above.";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #ffebee; padding: 15px; border-radius: 5px; color: #c62828;'>";
    echo "❌ Error: " . htmlspecialchars($e->getMessage());
    echo "</div>";
}

echo "<h3>Navigation:</h3>";
echo "<p><a href='admin-dashboard.php'>📊 Admin Dashboard</a></p>";
echo "<p><a href='result.php'>🔍 Test Result Page</a></p>";
echo "<p><a href='index.html'>🏠 Home Page</a></p>";
?>