<?php
// Google Cloud Storage Configuration for Cloud Run
class CloudStorageHelper {
    private $bucketName = 'resultexyx';
    private $baseFolder = 'UUD';
    
    public function __construct() {
        // Initialize Google Cloud Storage client
        // Cloud Run will automatically use service account credentials
    }
    
    /**
     * Upload PDF file to Cloud Storage
     */
    public function uploadPDF($rollNumber, $semester, $fileContent, $fileName) {
        try {
            $objectName = $this->baseFolder . '/' . $rollNumber . '/sem' . $semester . '.pdf';
            
            // Use gsutil command for file upload (available in Cloud Run)
            $tempFile = tempnam(sys_get_temp_dir(), 'pdf_upload');
            file_put_contents($tempFile, $fileContent);
            
            $bucketPath = 'gs://' . $this->bucketName . '/' . $objectName;
            $command = "gsutil cp {$tempFile} {$bucketPath} 2>&1";
            
            $output = shell_exec($command);
            $success = (strpos($output, 'Operation completed') !== false || empty($output));
            
            unlink($tempFile); // Clean up temp file
            
            if ($success) {
                return [
                    'success' => true,
                    'path' => $objectName,
                    'bucket_path' => $bucketPath
                ];
            } else {
                throw new Exception('Upload failed: ' . $output);
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Download file content from Cloud Storage
     */
    public function downloadFile($objectPath) {
        try {
            $bucketPath = 'gs://' . $this->bucketName . '/' . $objectPath;
            $tempFile = tempnam(sys_get_temp_dir(), 'pdf_download');
            
            $command = "gsutil cp {$bucketPath} {$tempFile} 2>&1";
            $output = shell_exec($command);
            
            if (file_exists($tempFile) && filesize($tempFile) > 0) {
                $content = file_get_contents($tempFile);
                unlink($tempFile);
                return $content;
            } else {
                throw new Exception('Download failed: ' . $output);
            }
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Check if PDF exists in Cloud Storage
     */
    public function fileExists($objectPath) {
        try {
            $bucketPath = 'gs://' . $this->bucketName . '/' . $objectPath;
            $command = "gsutil ls {$bucketPath} 2>/dev/null";
            $output = shell_exec($command);
            return !empty(trim($output));
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Upload database to Cloud Storage
     */
    public function uploadDatabase($dbContent) {
        try {
            $objectName = $this->baseFolder . '/mapping.db';
            $tempFile = tempnam(sys_get_temp_dir(), 'db_upload');
            file_put_contents($tempFile, $dbContent);
            
            $bucketPath = 'gs://' . $this->bucketName . '/' . $objectName;
            $command = "gsutil cp {$tempFile} {$bucketPath} 2>&1";
            
            $output = shell_exec($command);
            $success = (strpos($output, 'Operation completed') !== false || empty($output));
            
            unlink($tempFile);
            return $success;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Download database from Cloud Storage
     */
    public function downloadDatabase() {
        try {
            $objectName = $this->baseFolder . '/mapping.db';
            $bucketPath = 'gs://' . $this->bucketName . '/' . $objectName;
            $localDb = '/tmp/mapping.db';
            
            $command = "gsutil cp {$bucketPath} {$localDb} 2>/dev/null";
            shell_exec($command);
            
            return file_exists($localDb) ? $localDb : null;
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * Get database connection with Cloud Storage sync
     */
    public function getDatabaseConnection() {
        // Try to download existing database from Cloud Storage
        $cloudDb = $this->downloadDatabase();
        
        if ($cloudDb) {
            $dbPath = $cloudDb;
        } else {
            // Create new database in temp directory
            $dbPath = '/tmp/mapping.db';
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
     * Save database back to Cloud Storage
     */
    public function saveDatabase($pdo) {
        try {
            // Get database file path
            $result = $pdo->query("PRAGMA database_list")->fetch();
            $dbFile = $result['file'];
            
            if ($dbFile && file_exists($dbFile)) {
                $dbContent = file_get_contents($dbFile);
                return $this->uploadDatabase($dbContent);
            }
            return false;
        } catch (Exception $e) {
            return false;
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
                file_path VARCHAR(255) NOT NULL,
                uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (roll_number) REFERENCES students(roll_number),
                UNIQUE(roll_number, semester)
            );
        ";
        
        $pdo->exec($sql);
    }
}

/**
 * Student Database Operations with Cloud Storage Integration
 */
class StudentManager {
    private $db;
    private $storage;
    
    public function __construct() {
        $this->storage = new CloudStorageHelper();
        $this->db = $this->storage->getDatabaseConnection();
    }
    
    /**
     * Save changes to cloud after database operations
     */
    private function syncToCloud() {
        return $this->storage->saveDatabase($this->db);
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
            $result = $stmt->execute([$rollNumber, $studentName]);
            
            // Sync to cloud storage
            $this->syncToCloud();
            
            return $result;
        } catch (PDOException $e) {
            throw new Exception('Error saving student: ' . $e->getMessage());
        }
    }
    
    /**
     * Save result PDF
     */
    public function saveResult($rollNumber, $semester, $fileContent, $fileName) {
        try {
            // Upload PDF to Cloud Storage
            $uploadResult = $this->storage->uploadPDF($rollNumber, $semester, $fileContent, $fileName);
            
            if (!$uploadResult['success']) {
                throw new Exception('File upload failed: ' . $uploadResult['error']);
            }
            
            // Save to database
            $stmt = $this->db->prepare("
                INSERT OR REPLACE INTO results (roll_number, semester, file_path, uploaded_at) 
                VALUES (?, ?, ?, CURRENT_TIMESTAMP)
            ");
            $stmt->execute([$rollNumber, $semester, $uploadResult['path']]);
            
            // Sync database to cloud
            $this->syncToCloud();
            
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
    public function getStudentResult($rollNumber, $semester) {
        $stmt = $this->db->prepare("
            SELECT r.*, s.student_name 
            FROM results r 
            LEFT JOIN students s ON r.roll_number = s.roll_number 
            WHERE r.roll_number = ? AND r.semester = ?
        ");
        $stmt->execute([$rollNumber, $semester]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
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
            
            // Get results to delete files
            $results = $this->getStudentResults($rollNumber);
            
            // Delete from database
            $stmt = $this->db->prepare("DELETE FROM results WHERE roll_number = ?");
            $stmt->execute([$rollNumber]);
            
            $stmt = $this->db->prepare("DELETE FROM students WHERE roll_number = ?");
            $stmt->execute([$rollNumber]);
            
            $this->db->commit();
            
            // Sync to cloud
            $this->syncToCloud();
            
            // TODO: Delete PDF files from cloud storage
            // This would require implementing file deletion
            
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw new Exception('Error deleting student: ' . $e->getMessage());
        }
    }
}
?>