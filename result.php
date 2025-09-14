<?php
require_once 'includes/cloud-storage.php';

$message = '';
$error = '';
$resultFile = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $rollNumber = trim($_POST['roll_number']);
        $semester = intval($_POST['semester']);
        $dob = trim($_POST['dob']);
        
        if (empty($rollNumber) || !in_array($semester, [1, 2, 3]) || empty($dob)) {
            throw new Exception('All fields are required.');
        }
        
        $studentManager = new StudentManager();
        
        // Verify student exists with matching DOB
        $student = $studentManager->getStudent($rollNumber);
        if (!$student) {
            throw new Exception('Student not found.');
        }
        
        // For demo purposes, we'll check if DOB matches the format YYYY-MM-DD
        // In a real system, you would store and verify actual DOB
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dob)) {
            throw new Exception('Invalid date format. Use YYYY-MM-DD.');
        }
        
        // Get the result for the specified semester
        $result = $studentManager->getStudentResult($rollNumber, $semester);
        if (!$result) {
            throw new Exception('Result not found for the selected semester.');
        }
        
        // Get the file from cloud storage
        $cloudStorage = new CloudStorageHelper();
        $resultFile = $cloudStorage->downloadFile($result['file_path']);
        
        if (!$resultFile) {
            throw new Exception('Result file could not be retrieved.');
        }
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="IE=edge" http-equiv="X-UA-Compatible"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Check Results - Uttaranchal University</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet"/>
    <link href="assets/css/font-awesome.min.css" rel="stylesheet"/>
    <link href="assets/images/favicon.ico" rel="icon" type="image/ico">
    <style>
        body {
            font-family: 'Metropolis', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
        }
        .result-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            padding: 40px;
            max-width: 500px;
            width: 100%;
        }
        .logo-section {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo-section h1 {
            color: #21337d;
            font-size: 24px;
            font-weight: bold;
            margin: 15px 0 5px 0;
        }
        .logo-section p {
            color: #666;
            font-size: 14px;
            margin: 0;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }
        .form-control {
            border: 2px solid #e1e5f2;
            border-radius: 8px;
            padding: 12px 15px;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: #21337d;
            box-shadow: 0 0 0 0.2rem rgba(33, 51, 125, 0.25);
        }
        .btn-check {
            background: linear-gradient(135deg, #21337d 0%, #1e2a6b 100%);
            border: none;
            border-radius: 8px;
            padding: 12px 30px;
            font-weight: 600;
            color: white;
            width: 100%;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        .btn-check:hover {
            background: linear-gradient(135deg, #1e2a6b 0%, #19245a 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .back-home {
            text-align: center;
            margin-top: 20px;
        }
        .back-home a {
            color: #21337d;
            text-decoration: none;
            font-weight: 500;
        }
        .back-home a:hover {
            text-decoration: underline;
        }
        .alert {
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .result-display {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-top: 20px;
        }
        .result-display h5 {
            color: #28a745;
            margin-bottom: 15px;
        }
        .download-btn {
            background: #28a745;
            border: none;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            margin-top: 10px;
        }
        .download-btn:hover {
            background: #218838;
            color: white;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="result-container">
        <div class="logo-section">
            <div style="width: 60px; height: 60px; background: #21337d; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 15px;">
                <i class="fa fa-graduation-cap" style="color: white; font-size: 24px;"></i>
            </div>
            <h1>Check Your Results</h1>
            <p>Uttaranchal University</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fa fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($resultFile): ?>
            <div class="result-display">
                <h5><i class="fa fa-check-circle"></i> Result Found!</h5>
                <p>Your result for Semester <?php echo $semester; ?> is ready for download.</p>
                <a href="download-result.php?roll=<?php echo urlencode($rollNumber); ?>&sem=<?php echo $semester; ?>" 
                   class="download-btn" target="_blank">
                    <i class="fa fa-download"></i> Download Result PDF
                </a>
            </div>
        <?php endif; ?>
        
        <?php if (!$resultFile): ?>
            <form method="POST">
                <div class="form-group">
                    <label for="roll_number" class="form-label">Roll Number</label>
                    <input type="text" class="form-control" id="roll_number" name="roll_number" 
                           placeholder="Enter your roll number" required 
                           value="<?php echo isset($_POST['roll_number']) ? htmlspecialchars($_POST['roll_number']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="semester" class="form-label">Semester</label>
                    <select class="form-control" id="semester" name="semester" required>
                        <option value="">Select Semester</option>
                        <option value="1" <?php echo (isset($_POST['semester']) && $_POST['semester'] == '1') ? 'selected' : ''; ?>>1st Semester</option>
                        <option value="2" <?php echo (isset($_POST['semester']) && $_POST['semester'] == '2') ? 'selected' : ''; ?>>2nd Semester</option>
                        <option value="3" <?php echo (isset($_POST['semester']) && $_POST['semester'] == '3') ? 'selected' : ''; ?>>3rd Semester</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="dob" class="form-label">Date of Birth</label>
                    <input type="date" class="form-control" id="dob" name="dob" required 
                           value="<?php echo isset($_POST['dob']) ? htmlspecialchars($_POST['dob']) : ''; ?>">
                </div>
                
                <button type="submit" class="btn-check">
                    <i class="fa fa-search"></i> Check Result
                </button>
            </form>
        <?php endif; ?>
        
        <div class="back-home">
            <a href="index.html">
                <i class="fa fa-arrow-left"></i> Back to Home
            </a>
        </div>
    </div>
</body>
</html>