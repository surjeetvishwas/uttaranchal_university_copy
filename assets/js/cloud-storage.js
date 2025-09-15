/**
 * Cloud Storage Helper for gs://resultexyx/UUD/
 * Pure frontend JavaScript implementation
 */
class CloudStorage {
    constructor() {
        this.bucketUrl = 'https://storage.googleapis.com/resultexyx';
        this.basePath = 'UUD';
        this.databaseFile = 'database.json';
    }

    /**
     * Download database.json from cloud storage
     */
    async downloadDatabase() {
        try {
            const url = `${this.bucketUrl}/${this.basePath}/${this.databaseFile}?t=${Date.now()}`;
            const response = await fetch(url);
            
            if (!response.ok) {
                if (response.status === 404) {
                    // Database doesn't exist, return empty structure
                    return { students: [], results: [] };
                }
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const data = await response.json();
            console.log('Database downloaded successfully');
            return data;
        } catch (error) {
            console.error('Error downloading database:', error);
            // Return empty structure if download fails
            return { students: [], results: [] };
        }
    }

    /**
     * Upload database.json to cloud storage
     */
    async uploadDatabase(data) {
        try {
            const jsonData = JSON.stringify(data, null, 2);
            
            // Use XML API endpoint which works with public permissions
            const url = `https://storage.googleapis.com/resultexyx/${this.basePath}/${this.databaseFile}`;
            
            const response = await fetch(url, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: jsonData
            });

            if (!response.ok) {
                throw new Error(`Upload failed: ${response.status} ${response.statusText}`);
            }

            console.log('Database uploaded successfully');
            return true;
        } catch (error) {
            console.error('Error uploading database:', error);
            throw error;
        }
    }

    /**
     * Upload PDF file to cloud storage
     */
    async uploadPDF(rollNumber, semester, file) {
        try {
            const fileName = `sem${semester}.pdf`;
            const objectPath = `${this.basePath}/${rollNumber}/${fileName}`;
            // Use XML API endpoint which works with public permissions
            const url = `https://storage.googleapis.com/resultexyx/${objectPath}`;
            
            const response = await fetch(url, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/pdf',
                },
                body: file
            });

            if (!response.ok) {
                throw new Error(`PDF upload failed: ${response.status} ${response.statusText}`);
            }

            console.log('PDF uploaded successfully:', objectPath);
            
            return {
                success: true,
                path: objectPath,
                url: `${this.bucketUrl}/${objectPath}`
            };
        } catch (error) {
            console.error('Error uploading PDF:', error);
            throw error;
        }
    }

    /**
     * Get direct URL to PDF file
     */
    getPDFUrl(rollNumber, semester) {
        return `${this.bucketUrl}/${this.basePath}/${rollNumber}/sem${semester}.pdf`;
    }

    /**
     * Check if PDF exists
     */
    async checkPDFExists(rollNumber, semester) {
        try {
            const url = this.getPDFUrl(rollNumber, semester);
            const response = await fetch(url, { method: 'HEAD' });
            return response.ok;
        } catch (error) {
            return false;
        }
    }

    /**
     * Verify student credentials
     */
    async verifyStudent(rollNumber, studentName) {
        try {
            const database = await this.downloadDatabase();
            const student = database.students.find(s => 
                s.roll_number.toLowerCase() === rollNumber.toLowerCase()
            );
            
            if (!student) {
                return { valid: false, error: 'Student not found' };
            }
            
            if (student.enroll_number.toLowerCase() !== studentName.toLowerCase()) {
                return { valid: false, error: 'Student name does not match' };
            }
            
            return { valid: true, student };
        } catch (error) {
            console.error('Error verifying student:', error);
            return { valid: false, error: 'Database error' };
        }
    }

    /**
     * Get student result
     */
    async getStudentResult(rollNumber, semester) {
        try {
            const database = await this.downloadDatabase();
            const result = database.results.find(r => 
                r.roll_number.toLowerCase() === rollNumber.toLowerCase() && 
                (r.semester === semester.toString() || r.semester === parseInt(semester))
            );
            
            if (!result) {
                return { found: false, error: 'Result not found for this semester' };
            }
            
            // Check if PDF exists
            const pdfExists = await this.checkPDFExists(rollNumber, semester);
            if (!pdfExists) {
                return { found: false, error: 'Result file not available' };
            }
            
            return {
                found: true,
                result,
                pdfUrl: this.getPDFUrl(rollNumber, semester)
            };
        } catch (error) {
            console.error('Error getting student result:', error);
            return { found: false, error: 'Database error' };
        }
    }

    /**
     * Add or update student
     */
    async saveStudent(rollNumber, studentName) {
        try {
            const database = await this.downloadDatabase();
            
            // Check if student exists
            const existingIndex = database.students.findIndex(s => 
                s.roll_number.toLowerCase() === rollNumber.toLowerCase()
            );
            
            const studentData = {
                roll_number: rollNumber,
                enroll_number: studentName,
                created_at: new Date().toISOString()
            };
            
            if (existingIndex >= 0) {
                // Update existing student
                database.students[existingIndex] = {
                    ...database.students[existingIndex],
                    enroll_number: studentName
                };
            } else {
                // Add new student
                database.students.push(studentData);
            }
            
            await this.uploadDatabase(database);
            return { success: true };
        } catch (error) {
            console.error('Error saving student:', error);
            throw error;
        }
    }

    /**
     * Add or update result
     */
    async saveResult(rollNumber, semester, pdfFile) {
        try {
            // First upload the PDF
            const uploadResult = await this.uploadPDF(rollNumber, semester, pdfFile);
            
            // Then update the database
            const database = await this.downloadDatabase();
            
            // Check if result exists
            const existingIndex = database.results.findIndex(r => 
                r.roll_number.toLowerCase() === rollNumber.toLowerCase() && 
                (r.semester === semester.toString() || r.semester === parseInt(semester))
            );
            
            const resultData = {
                roll_number: rollNumber,
                semester: semester.toString(),
                file_path: uploadResult.path,
                uploaded_at: new Date().toISOString()
            };
            
            if (existingIndex >= 0) {
                // Update existing result
                database.results[existingIndex] = resultData;
            } else {
                // Add new result
                database.results.push(resultData);
            }
            
            await this.uploadDatabase(database);
            return { success: true, pdfUrl: uploadResult.url };
        } catch (error) {
            console.error('Error saving result:', error);
            throw error;
        }
    }

    /**
     * Get all students
     */
    async getAllStudents() {
        try {
            const database = await this.downloadDatabase();
            return database.students;
        } catch (error) {
            console.error('Error getting students:', error);
            return [];
        }
    }

    /**
     * Get all results
     */
    async getAllResults() {
        try {
            const database = await this.downloadDatabase();
            return database.results;
        } catch (error) {
            console.error('Error getting results:', error);
            return [];
        }
    }

    /**
     * Delete student and all their results
     */
    async deleteStudent(rollNumber) {
        try {
            const database = await this.downloadDatabase();
            
            // Remove student
            database.students = database.students.filter(s => 
                s.roll_number.toLowerCase() !== rollNumber.toLowerCase()
            );
            
            // Remove all results for this student
            database.results = database.results.filter(r => 
                r.roll_number.toLowerCase() !== rollNumber.toLowerCase()
            );
            
            await this.uploadDatabase(database);
            return { success: true };
        } catch (error) {
            console.error('Error deleting student:', error);
            throw error;
        }
    }
}

// Global instance
window.cloudStorage = new CloudStorage();