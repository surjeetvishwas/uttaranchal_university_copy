<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin.php');
    exit();
}

require_once 'includes/cloud-storage.php';

$studentManager = new StudentManager();
$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_student':
                try {
                    $rollNumber = trim($_POST['roll_number']);
                    $studentName = trim($_POST['student_name']);
                    
                    if (empty($rollNumber) || empty($studentName)) {
                        throw new Exception('Roll number and student name are required.');
                    }
                    
                    $studentManager->saveStudent($rollNumber, $studentName);
                    $message = 'Student added successfully!';
                } catch (Exception $e) {
                    $error = $e->getMessage();
                }
                break;
                
            case 'upload_result':
                try {
                    $rollNumber = trim($_POST['roll_number']);
                    $semester = intval($_POST['semester']);
                    
                    if (empty($rollNumber) || !in_array($semester, [1, 2, 3])) {
                        throw new Exception('Valid roll number and semester are required.');
                    }
                    
                    if (!isset($_FILES['pdf_file']) || $_FILES['pdf_file']['error'] !== UPLOAD_ERR_OK) {
                        throw new Exception('Please select a valid PDF file.');
                    }
                    
                    $file = $_FILES['pdf_file'];
                    if ($file['type'] !== 'application/pdf') {
                        throw new Exception('Only PDF files are allowed.');
                    }
                    
                    $fileContent = file_get_contents($file['tmp_name']);
                    $fileName = $file['name'];
                    
                    $result = $studentManager->saveResult($rollNumber, $semester, $fileContent, $fileName);
                    $message = 'Result uploaded successfully for Semester ' . $semester . '!';
                } catch (Exception $e) {
                    $error = $e->getMessage();
                }
                break;
                
            case 'delete_student':
                try {
                    $rollNumber = trim($_POST['roll_number']);
                    $studentManager->deleteStudent($rollNumber);
                    $message = 'Student deleted successfully!';
                } catch (Exception $e) {
                    $error = $e->getMessage();
                }
                break;
        }
    }
}

// Get all students
$students = $studentManager->getAllStudents();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="IE=edge" http-equiv="X-UA-Compatible"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Admin Dashboard - Student Result Management</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet"/>
    <link href="assets/css/font-awesome.min.css" rel="stylesheet"/>
    <link href="assets/images/favicon.ico" rel="icon" type="image/ico">
    <style>
        body {
            background: #f8f9fa;
            font-family: 'Metropolis', sans-serif;
        }
        .sidebar {
            background: linear-gradient(135deg, #21337d 0%, #1e2a6b 100%);
            min-height: 100vh;
            color: white;
            padding: 20px 0;
        }
        .sidebar h4 {
            text-align: center;
            margin-bottom: 30px;
            padding: 0 20px;
        }
        .nav-item {
            margin: 5px 0;
        }
        .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            border-radius: 0;
            transition: all 0.3s ease;
        }
        .nav-link:hover, .nav-link.active {
            background: rgba(255,255,255,0.1);
            color: white;
        }
        .main-content {
            padding: 20px;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .card-header {
            background: linear-gradient(135deg, #21337d 0%, #1e2a6b 100%);
            color: white;
            border-radius: 10px 10px 0 0 !important;
            padding: 15px 20px;
        }
        .btn-primary {
            background: linear-gradient(135deg, #21337d 0%, #1e2a6b 100%);
            border: none;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #1e2a6b 0%, #19245a 100%);
            transform: translateY(-1px);
        }
        .table th {
            background: #f8f9fa;
            border-top: none;
        }
        .alert {
            border-radius: 8px;
        }
        .form-control:focus {
            border-color: #21337d;
            box-shadow: 0 0 0 0.2rem rgba(33, 51, 125, 0.25);
        }
        .student-card {
            border-left: 4px solid #21337d;
        }
        .result-badge {
            display: inline-block;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            text-align: center;
            line-height: 30px;
            margin: 2px;
            font-weight: bold;
        }
        .result-available {
            background: #28a745;
            color: white;
        }
        .result-missing {
            background: #dc3545;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar">
                <h4><i class="fa fa-graduation-cap"></i> Admin Panel</h4>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="#dashboard" onclick="showSection('dashboard')">
                            <i class="fa fa-dashboard"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#add-student" onclick="showSection('add-student')">
                            <i class="fa fa-user-plus"></i> Add Student
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#upload-result" onclick="showSection('upload-result')">
                            <i class="fa fa-upload"></i> Upload Result
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#manage-students" onclick="showSection('manage-students')">
                            <i class="fa fa-users"></i> Manage Students
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin-logout.php">
                            <i class="fa fa-sign-out"></i> Logout
                        </a>
                    </li>
                </ul>
                
                <div class="mt-4 text-center" style="padding: 0 20px;">
                    <small>
                        <i class="fa fa-user"></i> Logged in as: <strong><?php echo $_SESSION['admin_id']; ?></strong><br>
                        <i class="fa fa-clock-o"></i> <?php echo date('Y-m-d H:i', $_SESSION['login_time']); ?>
                    </small>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <?php if ($message): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fa fa-check-circle"></i> <?php echo htmlspecialchars($message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fa fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Dashboard Section -->
                <div id="dashboard" class="section">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card text-center">
                                <div class="card-body">
                                    <i class="fa fa-users fa-3x text-primary mb-3"></i>
                                    <h3><?php echo count($students); ?></h3>
                                    <p class="text-muted">Total Students</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card text-center">
                                <div class="card-body">
                                    <i class="fa fa-file-pdf-o fa-3x text-success mb-3"></i>
                                    <h3><?php 
                                        $totalResults = 0;
                                        try {
                                            foreach ($students as $student) {
                                                $results = $studentManager->getStudentResults($student['roll_number']);
                                                $totalResults += count($results);
                                            }
                                        } catch (Exception $e) {
                                            // Handle error gracefully
                                            $totalResults = "Error";
                                        }
                                        echo $totalResults;
                                    ?></h3>
                                    <p class="text-muted">Total Results</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card text-center">
                                <div class="card-body">
                                    <i class="fa fa-cloud fa-3x text-info mb-3"></i>
                                    <h3>GCS</h3>
                                    <p class="text-muted">Cloud Storage</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fa fa-chart-bar"></i> Recent Activity</h5>
                        </div>
                        <div class="card-body">
                            <p>Welcome to the Student Result Management System!</p>
                            <ul>
                                <li>Add students using the "Add Student" section</li>
                                <li>Upload PDF results for each semester (1, 2, 3)</li>
                                <li>Students can check their results using the main result page</li>
                                <li>All data is stored securely in Google Cloud Storage</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <!-- Add Student Section -->
                <div id="add-student" class="section" style="display: none;">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fa fa-user-plus"></i> Add New Student</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="action" value="add_student">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="roll_number" class="form-label">Roll Number</label>
                                            <input type="text" class="form-control" id="roll_number" name="roll_number" 
                                                   placeholder="Enter roll number" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="student_name" class="form-label">Student Name</label>
                                            <input type="text" class="form-control" id="student_name" name="student_name" 
                                                   placeholder="Enter student name" required>
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-plus"></i> Add Student
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Upload Result Section -->
                <div id="upload-result" class="section" style="display: none;">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fa fa-upload"></i> Upload Student Result</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="action" value="upload_result">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="upload_roll_number" class="form-label">Roll Number</label>
                                            <select class="form-control" id="upload_roll_number" name="roll_number" required>
                                                <option value="">Select Roll Number</option>
                                                <?php foreach ($students as $student): ?>
                                                    <option value="<?php echo htmlspecialchars($student['roll_number']); ?>">
                                                        <?php echo htmlspecialchars($student['roll_number'] . ' - ' . $student['student_name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="semester" class="form-label">Semester</label>
                                            <select class="form-control" id="semester" name="semester" required>
                                                <option value="">Select Semester</option>
                                                <option value="1">1st Semester</option>
                                                <option value="2">2nd Semester</option>
                                                <option value="3">3rd Semester</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="pdf_file" class="form-label">PDF File</label>
                                            <input type="file" class="form-control" id="pdf_file" name="pdf_file" 
                                                   accept=".pdf" required>
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-upload"></i> Upload Result
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Manage Students Section -->
                <div id="manage-students" class="section" style="display: none;">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fa fa-users"></i> Manage Students</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($students)): ?>
                                <div class="text-center py-4">
                                    <i class="fa fa-users fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No students found. Add some students to get started.</p>
                                </div>
                            <?php else: ?>
                                <div class="row">
                                    <?php foreach ($students as $student): ?>
                                        <?php $results = $studentManager->getStudentResults($student['roll_number']); ?>
                                        <div class="col-md-6 mb-3">
                                            <div class="card student-card">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-start">
                                                        <div>
                                                            <h6 class="card-title"><?php echo htmlspecialchars($student['student_name']); ?></h6>
                                                            <p class="card-text text-muted">Roll: <?php echo htmlspecialchars($student['roll_number']); ?></p>
                                                        </div>
                                                        <div class="text-end">
                                                            <?php for ($sem = 1; $sem <= 3; $sem++): ?>
                                                                <?php 
                                                                $hasResult = false;
                                                                foreach ($results as $result) {
                                                                    if ($result['semester'] == $sem) {
                                                                        $hasResult = true;
                                                                        break;
                                                                    }
                                                                }
                                                                ?>
                                                                <span class="result-badge <?php echo $hasResult ? 'result-available' : 'result-missing'; ?>" 
                                                                      title="Semester <?php echo $sem; ?> - <?php echo $hasResult ? 'Available' : 'Missing'; ?>">
                                                                    <?php echo $sem; ?>
                                                                </span>
                                                            <?php endfor; ?>
                                                        </div>
                                                    </div>
                                                    <div class="mt-2">
                                                        <small class="text-muted">
                                                            Results: <?php echo count($results); ?>/3 | 
                                                            Added: <?php echo date('Y-m-d', strtotime($student['created_at'])); ?>
                                                        </small>
                                                    </div>
                                                    <div class="mt-2">
                                                        <form method="POST" class="d-inline" 
                                                              onsubmit="return confirm('Are you sure you want to delete this student and all their results?')">
                                                            <input type="hidden" name="action" value="delete_student">
                                                            <input type="hidden" name="roll_number" value="<?php echo htmlspecialchars($student['roll_number']); ?>">
                                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                                <i class="fa fa-trash"></i> Delete
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="assets/js/bootstrap.min.js"></script>
    <script>
        // Ensure DOM is loaded before defining functions
        document.addEventListener('DOMContentLoaded', function() {
            // Define showSection function globally
            window.showSection = function(sectionId) {
                // Hide all sections
                const sections = document.querySelectorAll('.section');
                sections.forEach(section => {
                    section.style.display = 'none';
                });
                
                // Show selected section
                const targetSection = document.getElementById(sectionId);
                if (targetSection) {
                    targetSection.style.display = 'block';
                }
                
                // Update nav links
                const navLinks = document.querySelectorAll('.nav-link');
                navLinks.forEach(link => {
                    link.classList.remove('active');
                });
                
                // Add active class to clicked link
                if (event && event.target) {
                    event.target.classList.add('active');
                }
            };
            
            // Show dashboard by default
            showSection('dashboard');
        });
    </script>
</body>
</html>