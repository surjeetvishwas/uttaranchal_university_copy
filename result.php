<?php
require_once 'includes/cloud-storage.php';

$message = '';
$error = '';
$resultFile = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $rollNumber = trim($_POST['roll_number']);
        $semester = intval($_POST['semester']);
        $studentName = trim($_POST['student_name']);
        
        if (empty($rollNumber) || !in_array($semester, [1, 2, 3]) || empty($studentName)) {
            throw new Exception('All fields are required.');
        }
        
        $studentManager = new StudentManager();
        
        // Verify student exists with matching name
        $student = $studentManager->getStudent($rollNumber);
        if (!$student) {
            throw new Exception('Student not found with this roll number.');
        }
        
        // Verify the student name matches (case-insensitive)
        if (strtolower($student['name']) !== strtolower($studentName)) {
            throw new Exception('Student name does not match our records.');
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
<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':

    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],

    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=

    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);

    })(window,document,'script','dataLayer','GTM-MHSQLWN');</script>
<!-- End Google Tag Manager -->
<!-- Required meta tags -->
<meta charset="utf-8"/>
<meta content="IE=edge" http-equiv="X-UA-Compatible"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<meta content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1" name="robots">
<title>Examination Results - Uttaranchal University</title>
<meta content="Check your examination results online. View UG, PG, Diploma and PhD results from Uttaranchal University, Dehradun." name="description">
<link href="result.php" rel="canonical"/>
<meta content="en_US" property="og:locale">
<meta content="website" property="og:type"/>
<meta content="Examination Results - Uttaranchal University" property="og:title"/>
<meta content="Check your examination results online. View UG, PG, Diploma and PhD results from Uttaranchal University, Dehradun." property="og:description"/>
<meta content="https://www.uudoon.in/result.php" property="og:url"/>
<meta content="Uttaranchal University" property="og:site_name"/>
<meta content="https://www.facebook.com/UttaranchalUniversityDehradun" property="article:publisher"/>
<meta content="2022-08-26T07:33:34+00:00" property="article:modified_time"/>
<meta content="https://uu-img.s3.ap-south-1.amazonaws.com/2018/03/Placeholder.jpg" property="og:image"/>
<meta content="383" property="og:image:width"/>
<meta content="256" property="og:image:height"/>
<meta content="image/jpeg" property="og:image:type"/>
<meta content="summary_large_image" name="twitter:card"/>
<meta content="@UUDehradun" name="twitter:site"/>
<meta content="Est. reading time" name="twitter:label1"/>
<meta content="3 minutes" name="twitter:data1"/>
<meta content="7zib4ympoxfz5urwig867pv0pag4wd" name="facebook-domain-verification"/>
<link as="font" crossorigin="" href="assets/fonts/Metropolis-Thin.woff2" rel="preload" type="font/woff2"/>
<link as="font" crossorigin="" href="assets/fonts/Metropolis-Light.woff2" rel="preload" type="font/woff2"/>
<link as="font" crossorigin="" href="assets/fonts/Metropolis-Regular.woff2" rel="preload" type="font/woff2"/>
<link as="font" crossorigin="" href="assets/fonts/Metropolis-Medium.woff2" rel="preload" type="font/woff2"/>
<link as="font" crossorigin="" href="assets/fonts/Metropolis-SemiBold.woff2" rel="preload" type="font/woff2"/>
<link as="font" crossorigin="" href="assets/fonts/Metropolis-Bold.woff2" rel="preload" type="font/woff2"/>
<link as="font" crossorigin="" href="assets/fonts/Metropolis-Black.woff2" rel="preload" type="font/woff2"/>
<link href="assets/css/bootstrap.min.css" rel="stylesheet"/>
<link href="assets/css/font-awesome.min.css" rel="stylesheet"/>
<link href="assets/css/slick.css" rel="stylesheet"/>
<link href="assets/css/jquery.fancybox.min.css" rel="stylesheet"/>
<link href="assets/css/global.css" rel="stylesheet"/>
<link href="assets/css/header-footer.css" rel="stylesheet"/>
<link href="assets/css/program.css" rel="stylesheet"/>
<link href="assets/css/landing-page.css" rel="stylesheet"/>
<link href="assets/css/form-widget.css" rel="stylesheet"/>
<link href="assets/css/animations.css" rel="stylesheet"/>
<link href="assets/css/dark-mode-new.css" rel="stylesheet"/>
<link href="assets/images/favicon.ico" rel="icon" type="image/ico">
<style>
body {
    font-family: 'Metropolis', sans-serif;
    background: linear-gradient(135deg, #21337d, #4fc3f7);
    margin: 0;
    padding: 0;
}

main {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem 1rem;
}

.result-container {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
    padding: 3rem;
    max-width: 600px;
    width: 100%;
    text-align: center;
}

.university-header {
    margin-bottom: 2rem;
}

.university-header h1 {
    color: #21337d;
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.university-header p {
    color: #666;
    font-size: 1rem;
    margin: 0;
}

.result-form {
    background: white;
    border-radius: 15px;
    padding: 2rem;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    margin-bottom: 2rem;
}

.form-title {
    color: #21337d;
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 1.5rem;
}

.form-group {
    margin-bottom: 1.5rem;
    text-align: left;
}

.form-group label {
    display: block;
    color: #333;
    font-weight: 600;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
}

.form-control {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid #e1e5f2;
    border-radius: 8px;
    font-size: 1rem;
    transition: all 0.3s ease;
    box-sizing: border-box;
}

.form-control:focus {
    outline: none;
    border-color: #21337d;
    box-shadow: 0 0 0 3px rgba(33, 51, 125, 0.1);
}

.btn-primary {
    background: linear-gradient(135deg, #21337d, #4fc3f7);
    border: none;
    border-radius: 10px;
    padding: 15px 30px;
    color: white;
    font-size: 1rem;
    font-weight: 600;
    width: 100%;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #1a2a6b, #3fa9d4);
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(33, 51, 125, 0.3);
}

.services-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-top: 2rem;
}

.service-card {
    background: white;
    border-radius: 15px;
    padding: 1.5rem;
    text-align: center;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.service-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
}

.service-icon {
    font-size: 2rem;
    margin-bottom: 1rem;
}

.service-card h6 {
    color: #21337d;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.service-card p {
    color: #666;
    font-size: 0.9rem;
    margin-bottom: 1rem;
}

.btn-outline {
    display: inline-block;
    padding: 8px 20px;
    border: 2px solid #21337d;
    color: #21337d;
    text-decoration: none;
    border-radius: 25px;
    font-size: 0.9rem;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-outline:hover {
    background: #21337d;
    color: white;
    text-decoration: none;
}

.alert {
    border-radius: 10px;
    margin-bottom: 1rem;
    padding: 1rem;
}

.alert-danger {
    background: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
}

.alert-success {
    background: #d4edda;
    border: 1px solid #c3e6cb;
    color: #155724;
}

.result-display {
    background: #e8f5e8;
    border-radius: 10px;
    padding: 2rem;
    margin-top: 1rem;
}

.download-btn {
    background: #28a745;
    border: none;
    color: white;
    padding: 12px 24px;
    border-radius: 8px;
    text-decoration: none;
    display: inline-block;
    margin-top: 1rem;
    font-weight: 600;
    transition: all 0.3s ease;
}

.download-btn:hover {
    background: #218838;
    color: white;
    text-decoration: none;
    transform: translateY(-2px);
}

.pdf-viewer-container {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 10px;
    margin: 20px 0;
}

.pdf-viewer-container iframe {
    border-radius: 8px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

@media (max-width: 768px) {
    .result-container {
        padding: 2rem 1.5rem;
        margin: 1rem;
    }
    
    .pdf-viewer-container iframe {
        height: 500px; /* Smaller height on mobile */
    }
    
    .services-grid {
        grid-template-columns: 1fr;
    }
    
    .university-header h1 {
        font-size: 1.5rem;
    }
    
    .download-btn {
        display: block;
        margin: 10px 0;
        text-align: center;
    }
}
</style>
</head>

<body>
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-MHSQLWN"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->

<main>
    <div class="result-container">
        <div class="university-header">
            <h1><i class="fa fa-graduation-cap"></i> Uttaranchal University</h1>
            <p>Examination Result Portal</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fa fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($resultFile): ?>
            <div class="result-display">
                <h5><i class="fa fa-check-circle text-success"></i> Result Found!</h5>
                <p>Your result for Semester <?php echo $semester; ?> is ready for download.</p>
                
                <div style="margin-top: 15px;">
                    <a href="download-result.php?roll=<?php echo urlencode($rollNumber); ?>&sem=<?php echo $semester; ?>&inline=1" 
                       class="download-btn" target="_blank" style="background: #17a2b8;">
                        <i class="fa fa-eye"></i> View Result Online
                    </a>
                    <a href="download-result.php?roll=<?php echo urlencode($rollNumber); ?>&sem=<?php echo $semester; ?>" 
                       class="download-btn" target="_blank" style="margin-left: 10px;">
                        <i class="fa fa-download"></i> Download PDF
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="result-form">
                <h3 class="form-title"><i class="fa fa-search"></i> Check Your Result</h3>
                <form method="POST">
                    <div class="form-group">
                        <label for="roll_number">Roll Number</label>
                        <input type="text" class="form-control" id="roll_number" name="roll_number" 
                               placeholder="Enter your roll number" required 
                               value="<?php echo isset($_POST['roll_number']) ? htmlspecialchars($_POST['roll_number']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="semester">Semester</label>
                        <select class="form-control" id="semester" name="semester" required>
                            <option value="">Select Semester</option>
                            <option value="1" <?php echo (isset($_POST['semester']) && $_POST['semester'] == '1') ? 'selected' : ''; ?>>1st Semester</option>
                            <option value="2" <?php echo (isset($_POST['semester']) && $_POST['semester'] == '2') ? 'selected' : ''; ?>>2nd Semester</option>
                            <option value="3" <?php echo (isset($_POST['semester']) && $_POST['semester'] == '3') ? 'selected' : ''; ?>>3rd Semester</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="student_name">Student Name</label>
                        <input type="text" class="form-control" id="student_name" name="student_name" 
                               placeholder="Enter your full name as per records" required 
                               value="<?php echo isset($_POST['student_name']) ? htmlspecialchars($_POST['student_name']) : ''; ?>">
                    </div>
                    <button type="submit" class="btn-primary">
                        <i class="fa fa-search"></i> View Result
                    </button>
                </form>
            </div>
        <?php endif; ?>
        
        <div class="services-grid">
            <div class="service-card">
                <div class="service-icon text-primary">
                    <i class="fa fa-calendar-check-o"></i>
                </div>
                <h6>Academic Calendar</h6>
                <p>View exam schedules</p>
                <a href="academics/academic-calendar.php" class="btn-outline">View Calendar</a>
            </div>
            <div class="service-card">
                <div class="service-icon text-success">
                    <i class="fa fa-download"></i>
                </div>
                <h6>Grade Card Download</h6>
                <p>Download transcripts</p>
                <a href="#" class="btn-outline">Download</a>
            </div>
            <div class="service-card">
                <div class="service-icon text-warning">
                    <i class="fa fa-refresh"></i>
                </div>
                <h6>Re-evaluation</h6>
                <p>Apply for re-evaluation</p>
                <a href="#" class="btn-outline">Apply Now</a>
            </div>
            <div class="service-card">
                <div class="service-icon text-info">
                    <i class="fa fa-question-circle"></i>
                </div>
                <h6>Help & Support</h6>
                <p>Get assistance</p>
                <a href="contact/index.html" class="btn-outline">Contact Us</a>
            </div>
        </div>
    </div>
</main>

<footer style="background: #21337d; color: white; text-align: center; padding: 20px 0; margin-top: 50px;">
    <div class="container">
        <p>&copy; 2024 Uttaranchal University. All rights reserved.</p>
        <p>
            <a href="index.html" style="color: #4fc3f7; text-decoration: none;">Back to Home</a> | 
            <a href="contact/index.html" style="color: #4fc3f7; text-decoration: none;">Contact Us</a> | 
            <a href="admissions/index.html" style="color: #4fc3f7; text-decoration: none;">Admissions</a>
        </p>
    </div>
</footer>

<script src="assets/js/bootstrap.min.js"></script>
</body>
</html>