<?php
require_once 'includes/cloud-storage.php';

// Check if required parameters are provided
if (!isset($_GET['roll']) || !isset($_GET['sem'])) {
    http_response_code(400);
    die('Missing required parameters.');
}

$rollNumber = trim($_GET['roll']);
$semester = intval($_GET['sem']);

if (empty($rollNumber) || !in_array($semester, [1, 2, 3])) {
    http_response_code(400);
    die('Invalid parameters.');
}

try {
    $studentManager = new StudentManager();
    
    // Verify student exists
    $student = $studentManager->getStudent($rollNumber);
    if (!$student) {
        http_response_code(404);
        die('Student not found.');
    }
    
    // Get the result for the specified semester
    $result = $studentManager->getStudentResult($rollNumber, $semester);
    if (!$result) {
        http_response_code(404);
        die('Result not found for the selected semester.');
    }
    
    // Get the file from cloud storage
    $cloudStorage = new CloudStorageHelper();
    $fileContent = $cloudStorage->downloadFile($result['file_path']);
    
    if (!$fileContent) {
        http_response_code(500);
        die('Result file could not be retrieved.');
    }
    
    // Set headers for PDF download
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $rollNumber . '_Semester_' . $semester . '_Result.pdf"');
    header('Content-Length: ' . strlen($fileContent));
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('Pragma: public');
    
    // Output the PDF content
    echo $fileContent;
    
} catch (Exception $e) {
    http_response_code(500);
    die('Error: ' . $e->getMessage());
}
?>