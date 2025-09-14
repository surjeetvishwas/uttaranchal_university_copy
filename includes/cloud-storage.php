<?php
// Google Cloud Storage Configuration
class CloudStorageHelper {
    private $bucketName = 'resultexyx';
    private $baseFolder = 'UUD';
    
    public function __construct() {
        // Initialize Google Cloud Storage client
        // Note: Service account credentials should be set via environment variable
        // or service account key file in production
    }
    
    /**
     * Upload PDF file to Cloud Storage
     */
    public function uploadPDF($rollNumber, $semester, $fileContent, $fileName) {
        try {
            $objectName = $this->baseFolder . '/' . $rollNumber . '/sem' . $semester . '.pdf';
            
            // For local development, create directories and save files
            $localPath = 'uploads/' . $rollNumber;
            if (!is_dir($localPath)) {
                mkdir($localPath, 0777, true);
            }
            
            $localFile = $localPath . '/sem' . $semester . '.pdf';
            file_put_contents($localFile, $fileContent);
            
            // In production, this would upload to Google Cloud Storage:
            /*
            $storage = new Google\Cloud\Storage\StorageClient();
            $bucket = $storage->bucket($this->bucketName);
            $object = $bucket->upload($fileContent, [
                'name' => $objectName
            ]);
            */
            
            return [
                'success' => true,
                'path' => $objectName,
                'localPath' => $localFile
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get PDF URL from Cloud Storage
     */
    public function getPDFUrl($rollNumber, $semester) {
        $objectName = $this->baseFolder . '/' . $rollNumber . '/sem' . $semester . '.pdf';
        
        // For local development
        $localFile = 'uploads/' . $rollNumber . '/sem' . $semester . '.pdf';
        if (file_exists($localFile)) {
            return $localFile;
        }
        
        // In production, this would generate a signed URL:
        /*
        $storage = new Google\Cloud\Storage\StorageClient();
        $bucket = $storage->bucket($this->bucketName);
        $object = $bucket->object($objectName);
        
        return $object->signedUrl(
            new \DateTime('+1 hour')
        );
        */
        
        return null;
    }
    
    /**
     * Check if PDF exists
     */
    public function pdfExists($rollNumber, $semester) {
        // For local development
        $localFile = 'uploads/' . $rollNumber . '/sem' . $semester . '.pdf';
        return file_exists($localFile);
        
        // In production:
        /*
        $storage = new Google\Cloud\Storage\StorageClient();
        $bucket = $storage->bucket($this->bucketName);
        $objectName = $this->baseFolder . '/' . $rollNumber . '/sem' . $semester . '.pdf';
        
        return $bucket->object($objectName)->exists();
        */
    }
    
    /**
     * Get database connection (SQLite stored in Cloud Storage)
     */
    public function getDatabaseConnection() {
        $dbPath = 'data/mapping.db';
        
        // Create directory if not exists
        if (!is_dir('data')) {
            mkdir('data', 0777, true);
        }
        
        try {
            $pdo = new PDO('sqlite:' . $dbPath);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Create tables if not exist
            $this->initializeDatabase($pdo);
            
            return $pdo;
        } catch (PDOException $e) {
            throw new Exception('Database connection failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Initialize database tables
     */
    private function initializeDatabase($pdo) {
        $sql = "
            CREATE TABLE IF NOT EXISTS students (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                roll_number VARCHAR(50) UNIQUE NOT NULL,
                student_name VARCHAR(100) NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );
            
            CREATE TABLE IF NOT EXISTS results (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                roll_number VARCHAR(50) NOT NULL,
                semester INTEGER NOT NULL,
                pdf_path VARCHAR(255) NOT NULL,
                uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (roll_number) REFERENCES students(roll_number),
                UNIQUE(roll_number, semester)
            );
        ";
        
        $pdo->exec($sql);
    }
}

/**
 * Student Database Operations
 */
class StudentManager {
    private $db;
    private $storage;
    
    public function __construct() {
        $this->storage = new CloudStorageHelper();
        $this->db = $this->storage->getDatabaseConnection();
    }
    
    /**
     * Add or update student
     */
    public function saveStudent($rollNumber, $studentName) {
        try {
            $stmt = $this->db->prepare("
                INSERT OR REPLACE INTO students (roll_number, student_name, updated_at) 
                VALUES (?, ?, CURRENT_TIMESTAMP)
            ");
            return $stmt->execute([$rollNumber, $studentName]);
        } catch (PDOException $e) {
            throw new Exception('Error saving student: ' . $e->getMessage());
        }
    }
    
    /**
     * Save result PDF
     */
    public function saveResult($rollNumber, $semester, $fileContent, $fileName) {
        try {
            // Upload PDF to storage
            $uploadResult = $this->storage->uploadPDF($rollNumber, $semester, $fileContent, $fileName);
            
            if (!$uploadResult['success']) {
                throw new Exception('File upload failed: ' . $uploadResult['error']);
            }
            
            // Save to database
            $stmt = $this->db->prepare("
                INSERT OR REPLACE INTO results (roll_number, semester, pdf_path, uploaded_at) 
                VALUES (?, ?, ?, CURRENT_TIMESTAMP)
            ");
            $stmt->execute([$rollNumber, $semester, $uploadResult['path']]);
            
            return $uploadResult;
        } catch (Exception $e) {
            throw new Exception('Error saving result: ' . $e->getMessage());
        }
    }
    
    /**
     * Get student by roll number
     */
    public function getStudent($rollNumber) {
        $stmt = $this->db->prepare("SELECT * FROM students WHERE roll_number = ?");
        $stmt->execute([$rollNumber]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get all students
     */
    public function getAllStudents() {
        $stmt = $this->db->query("SELECT * FROM students ORDER BY roll_number");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get result for roll number and semester
     */
    public function getResult($rollNumber, $semester) {
        $stmt = $this->db->prepare("
            SELECT r.*, s.student_name 
            FROM results r 
            LEFT JOIN students s ON r.roll_number = s.roll_number 
            WHERE r.roll_number = ? AND r.semester = ?
        ");
        $stmt->execute([$rollNumber, $semester]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            // Get PDF URL
            $result['pdf_url'] = $this->storage->getPDFUrl($rollNumber, $semester);
        }
        
        return $result;
    }
    
    /**
     * Get all results for a roll number
     */
    public function getStudentResults($rollNumber) {
        $stmt = $this->db->prepare("
            SELECT r.*, s.student_name 
            FROM results r 
            LEFT JOIN students s ON r.roll_number = s.roll_number 
            WHERE r.roll_number = ? 
            ORDER BY r.semester
        ");
        $stmt->execute([$rollNumber]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Delete student and all results
     */
    public function deleteStudent($rollNumber) {
        try {
            $this->db->beginTransaction();
            
            // Delete results
            $stmt = $this->db->prepare("DELETE FROM results WHERE roll_number = ?");
            $stmt->execute([$rollNumber]);
            
            // Delete student
            $stmt = $this->db->prepare("DELETE FROM students WHERE roll_number = ?");
            $stmt->execute([$rollNumber]);
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw new Exception('Error deleting student: ' . $e->getMessage());
        }
    }
}
?>