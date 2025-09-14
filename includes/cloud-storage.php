<?php
// Cloud Storage Helper for UUD folder structure
class CloudStorageHelper {
    private $bucketName = 'resultexyx';
    private $baseFolder = 'UUD';
    
    public function __construct() {
        // Cloud Run automatically provides service account credentials
    }
    
    /**
     * Get access token for Google Cloud API
     */
    private function getAccessToken    /**
     * Sync database to cloud after operations
     */
    private function syncToCloud() {
        try {
            // Create CloudStorageHelper instance to upload database
            $cloudHelper = new CloudStorageHelper();
            $success = $cloudHelper->saveDatabase($this->db);
            if ($success) {
                error_log("Database synced to cloud: gs://resultexyx/UUD/mapping.db");
            } else {
                error_log("Failed to sync database to cloud");
            }
            return $success;
        } catch (Exception $e) {
            error_log("Error syncing to cloud: " . $e->getMessage());
            return false;
        }
    } {
            // Get token from metadata server (available in Cloud Run)
            $url = 'http://metadata.google.internal/computeMetadata/v1/instance/service-accounts/default/token';
            $context = stream_context_create([
                'http' => [
                    'header' => 'Metadata-Flavor: Google',
                    'timeout' => 10
                ]
            ]);
            
            $response = @file_get_contents($url, false, $context);
            if ($response === false) {
                return null;
            }
            
            $data = json_decode($response, true);
            return $data['access_token'] ?? null;
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * Upload PDF file to Cloud Storage in UUD folder
     */
    public function uploadPDF($rollNumber, $semester, $fileContent, $fileName) {
        try {
            $objectName = $this->baseFolder . '/' . $rollNumber . '/sem' . $semester . '.pdf';
            $token = $this->getAccessToken();
            
            if (!$token) {
                // Fallback to local storage
                return $this->saveLocalFile($rollNumber, $semester, $fileContent);
            }
            
            // Upload to Google Cloud Storage using REST API
            $url = "https://storage.googleapis.com/upload/storage/v1/b/{$this->bucketName}/o?uploadType=media&name=" . urlencode($objectName);
            
            $context = stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => [
                        'Authorization: Bearer ' . $token,
                        'Content-Type: application/pdf',
                        'Content-Length: ' . strlen($fileContent)
                    ],
                    'content' => $fileContent,
                    'timeout' => 30
                ]
            ]);
            
            $response = @file_get_contents($url, false, $context);
            
            if ($response !== false) {
                return [
                    'success' => true,
                    'path' => $objectName,
                    'method' => 'cloud_storage',
                    'size' => strlen($fileContent)
                ];
            } else {
                // Fallback to local storage
                return $this->saveLocalFile($rollNumber, $semester, $fileContent);
            }
        } catch (Exception $e) {
            // Fallback to local storage
            return $this->saveLocalFile($rollNumber, $semester, $fileContent);
        }
    }
    
    /**
     * Upload database to UUD folder in Cloud Storage
     */
    public function uploadDatabase($dbContent) {
        try {
            $objectName = $this->baseFolder . '/mapping.db';
            $token = $this->getAccessToken();
            
            if (!$token) {
                error_log("No access token available for cloud upload");
                return false;
            }
            
            $url = "https://storage.googleapis.com/upload/storage/v1/b/{$this->bucketName}/o?uploadType=media&name=" . urlencode($objectName);
            
            $context = stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => [
                        'Authorization: Bearer ' . $token,
                        'Content-Type: application/octet-stream',
                        'Content-Length: ' . strlen($dbContent)
                    ],
                    'content' => $dbContent,
                    'timeout' => 30
                ]
            ]);
            
            $response = @file_get_contents($url, false, $context);
            
            if ($response !== false) {
                error_log("Database uploaded successfully to gs://{$this->bucketName}/{$objectName}");
                return true;
            } else {
                error_log("Failed to upload database to cloud storage");
                return false;
            }
        } catch (Exception $e) {
            error_log("Database upload error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Download database from UUD folder
     */
    public function downloadDatabase() {
        try {
            $objectName = $this->baseFolder . '/mapping.db';
            $token = $this->getAccessToken();
            
            if (!$token) {
                return null;
            }
            
            $url = "https://storage.googleapis.com/storage/v1/b/{$this->bucketName}/o/" . urlencode($objectName) . "?alt=media";
            
            $context = stream_context_create([
                'http' => [
                    'header' => 'Authorization: Bearer ' . $token,
                    'timeout' => 30
                ]
            ]);
            
            $content = @file_get_contents($url, false, $context);
            
            if ($content !== false) {
                $tempDb = '/tmp/uud_mapping.db';
                file_put_contents($tempDb, $content);
                return $tempDb;
            }
            
            return null;
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * Fallback: Save to local storage with UUD structure
     */
    private function saveLocalFile($rollNumber, $semester, $fileContent) {
        try {
            $localPath = '/tmp/UUD/' . $rollNumber;
            if (!is_dir($localPath)) {
                mkdir($localPath, 0777, true);
            }
            
            $localFile = $localPath . '/sem' . $semester . '.pdf';
            file_put_contents($localFile, $fileContent);
            
            return [
                'success' => true,
                'path' => $this->baseFolder . '/' . $rollNumber . '/sem' . $semester . '.pdf',
                'local_path' => $localFile,
                'method' => 'local_storage'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Download file from Cloud Storage
     */
    public function downloadFile($objectPath) {
        try {
            // Check if it's a local file
            if (file_exists('/tmp/' . $objectPath)) {
                return file_get_contents('/tmp/' . $objectPath);
            }
            
            $token = $this->getAccessToken();
            if (!$token) {
                return false;
            }
            
            $url = "https://storage.googleapis.com/storage/v1/b/{$this->bucketName}/o/" . urlencode($objectPath) . "?alt=media";
            
            $context = stream_context_create([
                'http' => [
                    'header' => 'Authorization: Bearer ' . $token,
                    'timeout' => 30
                ]
            ]);
            
            return @file_get_contents($url, false, $context);
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Get database connection - ONLY uses cloud storage gs://resultexyx/UUD/mapping.db
     */
    public function getDatabaseConnection() {
        // ALWAYS try to download from cloud storage first
        $cloudDb = $this->downloadDatabase();
        
        if ($cloudDb && file_exists($cloudDb)) {
            // Cloud database exists, use it
            $dbPath = $cloudDb;
            error_log("Using cloud database from gs://resultexyx/UUD/mapping.db");
        } else {
            // Cloud database doesn't exist, create new empty database in temp
            $dbPath = '/tmp/uud_mapping.db';
            error_log("Cloud database not found, creating new database at: " . $dbPath);
            
            // Delete any existing temp file to start fresh
            if (file_exists($dbPath)) {
                unlink($dbPath);
            }
        }
        
        try {
            $pdo = new PDO('sqlite:' . $dbPath);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Create tables if not exist (for new databases)
            $this->initializeDatabase($pdo);
            
            return $pdo;
        } catch (PDOException $e) {
            throw new Exception('Database connection failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Save database to UUD folder in Cloud Storage
     */
    public function saveDatabase($pdo) {
        try {
            // Get the database file path
            $result = $pdo->query("PRAGMA database_list")->fetch();
            $dbFile = $result['file'];
            
            if ($dbFile && file_exists($dbFile)) {
                $dbContent = file_get_contents($dbFile);
                $success = $this->uploadDatabase($dbContent);
                
                if ($success) {
                    error_log("Database successfully synced to gs://resultexyx/UUD/mapping.db");
                } else {
                    error_log("Failed to sync database to cloud storage");
                }
                
                return $success;
            } else {
                error_log("Database file not found: " . $dbFile);
                return false;
            }
        } catch (Exception $e) {
            error_log("Error saving database: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Initialize database tables
     */
    private function initializeDatabase($pdo) {
        try {
            // Check if students table exists
            $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='students'");
            $studentsTableExists = $stmt->fetchColumn() ? true : false;
            
            // Check if results table exists
            $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='results'");
            $resultsTableExists = $stmt->fetchColumn() ? true : false;
            
            if (!$studentsTableExists) {
                // Create students table with comprehensive schema
                $sql = "
                    CREATE TABLE students (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        roll_number VARCHAR(50) UNIQUE NOT NULL,
                        student_name VARCHAR(100) NOT NULL,
                        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                    );
                ";
                $pdo->exec($sql);
                error_log("Created students table");
            }
            
            if (!$resultsTableExists) {
                // Create results table
                $sql = "
                    CREATE TABLE results (
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
                error_log("Created results table");
            }
            
            // If tables exist, check if we need to add missing columns
            if ($studentsTableExists) {
                $columns = $pdo->query("PRAGMA table_info(students)")->fetchAll();
                $existingColumns = array_column($columns, 'name');
                
                // Add student_name column if it doesn't exist
                if (!in_array('student_name', $existingColumns)) {
                    try {
                        $pdo->exec("ALTER TABLE students ADD COLUMN student_name VARCHAR(100)");
                        // Copy data from name column if it exists
                        if (in_array('name', $existingColumns)) {
                            $pdo->exec("UPDATE students SET student_name = name WHERE student_name IS NULL");
                        }
                        error_log("Added student_name column");
                    } catch (Exception $e) {
                        error_log("Could not add student_name column: " . $e->getMessage());
                    }
                }
                
                // Add created_at column if it doesn't exist
                if (!in_array('created_at', $existingColumns)) {
                    try {
                        $pdo->exec("ALTER TABLE students ADD COLUMN created_at DATETIME DEFAULT CURRENT_TIMESTAMP");
                        error_log("Added created_at column");
                    } catch (Exception $e) {
                        error_log("Could not add created_at column: " . $e->getMessage());
                    }
                }
            }
            
        } catch (Exception $e) {
            error_log("Database initialization error: " . $e->getMessage());
            throw new Exception("Failed to initialize database: " . $e->getMessage());
        }
    }
}

/**
 * Student Manager with proper UUD folder integration
 */
class StudentManager {
    private $db;
    private $storage;
    
    public function __construct() {
        $this->storage = new CloudStorageHelper();
        $this->db = $this->storage->getDatabaseConnection();
    }
    
    /**
     * Sync database to cloud after operations
     */
    private function syncToCloud() {
        try {
            // Create CloudStorageHelper instance to upload database
            $cloudHelper = new CloudStorageHelper();
            $success = $cloudHelper->saveDatabase($this->db);
            if ($success) {
                error_log("Database synced to cloud: gs://resultexyx/UUD/mapping.db");
            } else {
                error_log("Failed to sync database to cloud");
            }
            return $success;
        } catch (Exception $e) {
            error_log("Sync error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Add or update student - ONLY uses student_name column
     */
    public function saveStudent($rollNumber, $studentName) {
        try {
            // Use simple, clean schema with student_name column
            $sql = "INSERT OR REPLACE INTO students (roll_number, student_name) VALUES (?, ?)";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$rollNumber, $studentName]);
            
            if ($result) {
                error_log("Student saved successfully: $rollNumber - $studentName");
                $this->syncToCloud();
                return true;
            } else {
                error_log("Failed to execute INSERT for student: $rollNumber - $studentName");
                return false;
            }
            
        } catch (PDOException $e) {
            error_log("PDO Error in saveStudent: " . $e->getMessage());
            throw new Exception('Error saving student: ' . $e->getMessage());
        }
    }
    
    /**
     * Add student (alias for backward compatibility)
     */
    public function addStudent($rollNumber, $studentName) {
        return $this->saveStudent($rollNumber, $studentName);
    }
    
    /**
     * Save result PDF to UUD folder structure
     */
    public function saveResult($rollNumber, $semester, $fileContent, $fileName) {
        try {
            // Upload PDF to UUD folder in Cloud Storage
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
            
            // Sync database to UUD folder
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
        try {
            $stmt = $this->db->prepare("SELECT *, student_name as name FROM students WHERE roll_number = ?");
            $stmt->execute([$rollNumber]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getStudent: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all students
     */
    public function getAllStudents() {
        $stmt = $this->db->query("SELECT *, student_name as name FROM students ORDER BY roll_number");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get result for specific roll number and semester
     */
    public function getStudentResult($rollNumber, $semester) {
        try {
            $stmt = $this->db->prepare("
                SELECT r.*, s.student_name as name
                FROM results r 
                LEFT JOIN students s ON r.roll_number = s.roll_number 
                WHERE r.roll_number = ? AND r.semester = ?
            ");
            
            $stmt->execute([$rollNumber, $semester]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getStudentResult: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all results for a roll number
     */
    public function getStudentResults($rollNumber) {
        try {
            $stmt = $this->db->prepare("
                SELECT r.*, s.student_name as name
                FROM results r 
                LEFT JOIN students s ON r.roll_number = s.roll_number 
                WHERE r.roll_number = ? 
                ORDER BY r.semester
            ");
            
            $stmt->execute([$rollNumber]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getStudentResults: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Add student result
     */
    public function addStudentResult($rollNumber, $semester, $filePath) {
        try {
            $stmt = $this->db->prepare("
                INSERT OR REPLACE INTO results (roll_number, semester, file_path) 
                VALUES (?, ?, ?)
            ");
            $result = $stmt->execute([$rollNumber, $semester, $filePath]);
            
            if ($result) {
                error_log("Result saved: $rollNumber - Semester $semester - $filePath");
                // Sync to UUD folder in cloud
                $this->syncToCloud();
            } else {
                error_log("Failed to save result: $rollNumber - Semester $semester");
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("Error adding result: " . $e->getMessage());
            throw new Exception('Error adding result: ' . $e->getMessage());
        }
    }
    
    /**
     * Delete student and all results
     */
    public function deleteStudent($rollNumber) {
        try {
            $this->db->beginTransaction();
            
            // Delete results from database
            $stmt = $this->db->prepare("DELETE FROM results WHERE roll_number = ?");
            $stmt->execute([$rollNumber]);
            
            // Delete student from database
            $stmt = $this->db->prepare("DELETE FROM students WHERE roll_number = ?");
            $stmt->execute([$rollNumber]);
            
            $this->db->commit();
            
            // Sync to UUD folder
            $this->syncToCloud();
            
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw new Exception('Error deleting student: ' . $e->getMessage());
        }
    }
}
?>