<?php
require_once 'includes/cloud-storage.php';

echo "<h2>Add Test Result Data</h2>";

try {
    $studentManager = new StudentManager();
    
    // Get all students first
    $students = $studentManager->getAllStudents();
    
    if (empty($students)) {
        echo "<div style='background: #fff3e0; padding: 15px; border-radius: 5px; color: #ef6c00;'>";
        echo "⚠️ No students found. Please add students first via the admin dashboard.";
        echo "</div>";
        echo "<p><a href='admin-dashboard.php'>Go to Admin Dashboard</a></p>";
        exit;
    }
    
    echo "<h3>Available Students:</h3>";
    echo "<ul>";
    foreach ($students as $student) {
        echo "<li><strong>" . htmlspecialchars($student['roll_number']) . "</strong> - " . htmlspecialchars($student['name']) . "</li>";
    }
    echo "</ul>";
    
    // Create sample PDF content for each student and semester
    $samplePdfContent = "%PDF-1.4
1 0 obj
<<
/Type /Catalog
/Pages 2 0 R
>>
endobj

2 0 obj
<<
/Type /Pages
/Kids [3 0 R]
/Count 1
>>
endobj

3 0 obj
<<
/Type /Page
/Parent 2 0 R
/MediaBox [0 0 612 792]
/Contents 4 0 R
/Resources <<
/Font <<
/F1 5 0 R
>>
>>
>>
endobj

4 0 obj
<<
/Length 200
>>
stream
BT
/F1 12 Tf
50 750 Td
(UTTARANCHAL UNIVERSITY) Tj
0 -30 Td
(EXAMINATION RESULT) Tj
0 -30 Td
(Roll Number: {ROLL_NUMBER}) Tj
0 -30 Td
(Name: {STUDENT_NAME}) Tj
0 -30 Td
(Semester: {SEMESTER}) Tj
0 -30 Td
(Result: PASS) Tj
0 -30 Td
(Marks: 85%) Tj
ET
endstream
endobj

5 0 obj
<<
/Type /Font
/Subtype /Type1
/BaseFont /Helvetica
>>
endobj

xref
0 6
0000000000 65535 f 
0000000009 00000 n 
0000000058 00000 n 
0000000115 00000 n 
0000000274 00000 n 
0000000524 00000 n 
trailer
<<
/Size 6
/Root 1 0 R
>>
startxref
593
%%EOF";

    $cloudStorage = new CloudStorageHelper();
    $addedResults = 0;
    
    echo "<h3>Adding Test Results:</h3>";
    
    foreach ($students as $student) {
        for ($semester = 1; $semester <= 3; $semester++) {
            // Check if result already exists
            $existingResult = $studentManager->getStudentResult($student['roll_number'], $semester);
            if ($existingResult) {
                echo "<p>⏭️ Result already exists for {$student['roll_number']} - Semester {$semester}</p>";
                continue;
            }
            
            // Create PDF with student-specific content
            $pdfContent = str_replace(
                ['{ROLL_NUMBER}', '{STUDENT_NAME}', '{SEMESTER}'],
                [$student['roll_number'], $student['name'], $semester],
                $samplePdfContent
            );
            
            // Create local file
            $fileName = "result_{$student['roll_number']}_sem_{$semester}.pdf";
            $localPath = "uploads/{$fileName}";
            
            // Ensure uploads directory exists
            if (!file_exists('uploads')) {
                mkdir('uploads', 0777, true);
            }
            
            // Save PDF locally
            file_put_contents($localPath, $pdfContent);
            
            // Upload to cloud storage
            $cloudPath = "results/{$fileName}";
            $uploadSuccess = $cloudStorage->uploadFile($localPath, $cloudPath);
            
            if ($uploadSuccess) {
                // Add result record to database
                $resultAdded = $studentManager->addStudentResult($student['id'], $semester, $cloudPath);
                
                if ($resultAdded) {
                    echo "<p>✅ Added result for <strong>{$student['roll_number']}</strong> - Semester {$semester}</p>";
                    $addedResults++;
                } else {
                    echo "<p>❌ Failed to add database record for {$student['roll_number']} - Semester {$semester}</p>";
                }
            } else {
                // If cloud upload fails, use local path
                $resultAdded = $studentManager->addStudentResult($student['id'], $semester, $localPath);
                if ($resultAdded) {
                    echo "<p>⚠️ Added result for <strong>{$student['roll_number']}</strong> - Semester {$semester} (local file)</p>";
                    $addedResults++;
                }
            }
            
            // Clean up local file if it was uploaded to cloud
            if ($uploadSuccess && file_exists($localPath)) {
                unlink($localPath);
            }
        }
    }
    
    echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 5px; color: #2e7d32; margin: 20px 0;'>";
    echo "<strong>✅ Process Complete!</strong><br>";
    echo "Added {$addedResults} test results to the database.";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #ffebee; padding: 15px; border-radius: 5px; color: #c62828;'>";
    echo "❌ Error: " . $e->getMessage();
    echo "</div>";
}

echo "<h3>Next Steps:</h3>";
echo "<ul>";
echo "<li><a href='check-data.php'>🔍 Check Database Content</a> - Verify the results were added</li>";
echo "<li><a href='result.php'>📋 Test Result Portal</a> - Try checking a result</li>";
echo "<li><a href='admin-dashboard.php'>📊 Admin Dashboard</a> - Manage data</li>";
echo "</ul>";
?>