/**
 * Admin Panel JavaScript
 * Handles authentication and CRUD operations
 */
document.addEventListener('DOMContentLoaded', function() {
    // Admin credentials
    const ADMIN_USERNAME = 'admin';
    const ADMIN_PASSWORD = 'Admin@123';
    
    // DOM elements
    const loginForm = document.getElementById('loginForm');
    const adminPanel = document.getElementById('adminPanel');
    const loginFormElement = document.getElementById('loginFormElement');
    const loginError = document.getElementById('loginError');
    const logoutBtn = document.getElementById('logoutBtn');
    
    // Tab elements
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    
    // Check if already logged in
    if (sessionStorage.getItem('adminLoggedIn') === 'true') {
        showAdminPanel();
    }
    
    // Login form handler
    loginFormElement.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const username = document.getElementById('username').value;
        const password = document.getElementById('password').value;
        
        if (username === ADMIN_USERNAME && password === ADMIN_PASSWORD) {
            sessionStorage.setItem('adminLoggedIn', 'true');
            showAdminPanel();
            loginError.style.display = 'none';
        } else {
            loginError.style.display = 'block';
        }
    });
    
    // Logout handler
    logoutBtn.addEventListener('click', function() {
        sessionStorage.removeItem('adminLoggedIn');
        location.reload();
    });
    
    // Tab navigation
    tabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const tabName = this.getAttribute('data-tab');
            switchTab(tabName);
        });
    });
    
    function showAdminPanel() {
        loginForm.style.display = 'none';
        adminPanel.style.display = 'block';
        initializeAdmin();
    }
    
    function switchTab(tabName) {
        // Update tab buttons
        tabBtns.forEach(btn => btn.classList.remove('active'));
        document.querySelector(`[data-tab="${tabName}"]`).classList.add('active');
        
        // Update tab content
        tabContents.forEach(content => content.classList.remove('active'));
        document.getElementById(`${tabName}Tab`).classList.add('active');
        
        // Load tab-specific data
        if (tabName === 'students') {
            loadStudents();
        } else if (tabName === 'results') {
            loadResults();
            loadStudentOptions();
        } else if (tabName === 'database') {
            loadDatabasePreview();
        }
    }
    
    async function initializeAdmin() {
        try {
            showAlert('Loading admin panel...', 'info');
            await updateStats();
            await loadStudents();
            hideAlert();
        } catch (error) {
            console.error('Admin initialization error:', error);
            showAlert('Failed to load admin panel', 'danger');
        }
    }
    
    async function updateStats() {
        try {
            const database = await window.cloudStorage.downloadDatabase();
            
            document.getElementById('totalStudents').textContent = database.students.length;
            document.getElementById('totalResults').textContent = database.results.length;
            document.getElementById('databaseStatus').textContent = 'Online';
            document.getElementById('databaseStatus').style.color = '#28a745';
        } catch (error) {
            document.getElementById('databaseStatus').textContent = 'Error';
            document.getElementById('databaseStatus').style.color = '#dc3545';
        }
    }
    
    // Student Management
    async function loadStudents() {
        const loading = document.getElementById('studentsLoading');
        const table = document.getElementById('studentsTable');
        const tbody = document.getElementById('studentsTableBody');
        
        try {
            loading.style.display = 'block';
            table.style.display = 'none';
            
            const students = await window.cloudStorage.getAllStudents();
            
            tbody.innerHTML = '';
            students.forEach(student => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${student.roll_number}</td>
                    <td>${student.enroll_number}</td>
                    <td>${new Date(student.created_at).toLocaleDateString()}</td>
                    <td>
                        <button class="btn btn-danger btn-sm" onclick="deleteStudent('${student.roll_number}')">
                            <i class="fa fa-trash"></i> Delete
                        </button>
                    </td>
                `;
                tbody.appendChild(row);
            });
            
            loading.style.display = 'none';
            table.style.display = 'table';
        } catch (error) {
            console.error('Error loading students:', error);
            loading.innerHTML = 'Error loading students';
        }
    }
    
    // Add student form
    document.getElementById('addStudentForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const rollNumber = document.getElementById('studentRoll').value.trim();
        const studentName = document.getElementById('studentNameInput').value.trim();
        
        if (!rollNumber || !studentName) {
            showAlert('Please fill in all fields', 'danger');
            return;
        }
        
        try {
            showAlert('Adding student...', 'info');
            await window.cloudStorage.saveStudent(rollNumber, studentName);
            showAlert('Student added successfully!', 'success');
            
            // Reset form
            document.getElementById('addStudentForm').reset();
            
            // Reload data
            await loadStudents();
            await updateStats();
        } catch (error) {
            console.error('Error adding student:', error);
            showAlert('Failed to add student', 'danger');
        }
    });
    
    // Results Management
    async function loadResults() {
        const loading = document.getElementById('resultsLoading');
        const table = document.getElementById('resultsTable');
        const tbody = document.getElementById('resultsTableBody');
        
        try {
            loading.style.display = 'block';
            table.style.display = 'none';
            
            const results = await window.cloudStorage.getAllResults();
            
            tbody.innerHTML = '';
            results.forEach(result => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${result.roll_number}</td>
                    <td>${result.semester}</td>
                    <td>${result.file_path}</td>
                    <td>${new Date(result.uploaded_at).toLocaleDateString()}</td>
                    <td>
                        <a href="${window.cloudStorage.getPDFUrl(result.roll_number, result.semester)}" 
                           target="_blank" class="btn btn-success btn-sm">
                            <i class="fa fa-eye"></i> View
                        </a>
                    </td>
                `;
                tbody.appendChild(row);
            });
            
            loading.style.display = 'none';
            table.style.display = 'table';
        } catch (error) {
            console.error('Error loading results:', error);
            loading.innerHTML = 'Error loading results';
        }
    }
    
    async function loadStudentOptions() {
        const select = document.getElementById('resultStudentRoll');
        
        try {
            const students = await window.cloudStorage.getAllStudents();
            
            select.innerHTML = '<option value="">Select Student</option>';
            students.forEach(student => {
                const option = document.createElement('option');
                option.value = student.roll_number;
                option.textContent = `${student.roll_number} - ${student.enroll_number}`;
                select.appendChild(option);
            });
        } catch (error) {
            console.error('Error loading student options:', error);
        }
    }
    
    // PDF Upload
    const pdfUploadArea = document.getElementById('pdfUploadArea');
    const pdfFileInput = document.getElementById('pdfFile');
    const selectedFileDiv = document.getElementById('selectedFile');
    const fileNameSpan = document.getElementById('fileName');
    
    pdfUploadArea.addEventListener('click', () => pdfFileInput.click());
    
    pdfUploadArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.style.borderColor = '#21337d';
        this.style.background = '#f8f9fa';
    });
    
    pdfUploadArea.addEventListener('dragleave', function(e) {
        e.preventDefault();
        this.style.borderColor = '#dee2e6';
        this.style.background = 'white';
    });
    
    pdfUploadArea.addEventListener('drop', function(e) {
        e.preventDefault();
        this.style.borderColor = '#dee2e6';
        this.style.background = 'white';
        
        const files = e.dataTransfer.files;
        if (files.length > 0 && files[0].type === 'application/pdf') {
            handleFileSelect(files[0]);
        }
    });
    
    pdfFileInput.addEventListener('change', function(e) {
        if (e.target.files.length > 0) {
            handleFileSelect(e.target.files[0]);
        }
    });
    
    function handleFileSelect(file) {
        if (file.type !== 'application/pdf') {
            showAlert('Please select a PDF file', 'danger');
            return;
        }
        
        fileNameSpan.textContent = file.name;
        selectedFileDiv.style.display = 'block';
    }
    
    // Upload result form
    document.getElementById('uploadResultForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const rollNumber = document.getElementById('resultStudentRoll').value;
        const semester = document.getElementById('resultSemester').value;
        const file = pdfFileInput.files[0];
        
        if (!rollNumber || !semester || !file) {
            showAlert('Please fill in all fields and select a PDF file', 'danger');
            return;
        }
        
        try {
            showAlert('Uploading result... This may take a moment.', 'info');
            await window.cloudStorage.saveResult(rollNumber, semester, file);
            showAlert('Result uploaded successfully!', 'success');
            
            // Reset form
            document.getElementById('uploadResultForm').reset();
            selectedFileDiv.style.display = 'none';
            
            // Reload data
            await loadResults();
            await updateStats();
        } catch (error) {
            console.error('Error uploading result:', error);
            showAlert('Failed to upload result', 'danger');
        }
    });
    
    // Database Management
    async function loadDatabasePreview() {
        const preview = document.getElementById('databasePreview');
        
        try {
            const database = await window.cloudStorage.downloadDatabase();
            preview.textContent = JSON.stringify(database, null, 2);
        } catch (error) {
            preview.textContent = 'Error loading database preview';
        }
    }
    
    document.getElementById('refreshDataBtn').addEventListener('click', async function() {
        try {
            showAlert('Refreshing data...', 'info');
            await updateStats();
            await loadStudents();
            await loadResults();
            await loadDatabasePreview();
            showAlert('Data refreshed successfully!', 'success');
        } catch (error) {
            showAlert('Failed to refresh data', 'danger');
        }
    });
    
    document.getElementById('exportDataBtn').addEventListener('click', async function() {
        try {
            const database = await window.cloudStorage.downloadDatabase();
            const dataStr = JSON.stringify(database, null, 2);
            const dataBlob = new Blob([dataStr], { type: 'application/json' });
            
            const link = document.createElement('a');
            link.href = URL.createObjectURL(dataBlob);
            link.download = `database_${new Date().toISOString().split('T')[0]}.json`;
            link.click();
            
            showAlert('Database exported successfully!', 'success');
        } catch (error) {
            showAlert('Failed to export database', 'danger');
        }
    });
    
    // JSON import
    const jsonUploadArea = document.getElementById('jsonUploadArea');
    const jsonFileInput = document.getElementById('jsonFile');
    const importDataBtn = document.getElementById('importDataBtn');
    
    jsonUploadArea.addEventListener('click', () => jsonFileInput.click());
    
    jsonFileInput.addEventListener('change', function(e) {
        if (e.target.files.length > 0) {
            const file = e.target.files[0];
            if (file.type === 'application/json') {
                importDataBtn.disabled = false;
                showAlert(`Selected: ${file.name}`, 'info');
            } else {
                showAlert('Please select a JSON file', 'danger');
            }
        }
    });
    
    importDataBtn.addEventListener('click', async function() {
        const file = jsonFileInput.files[0];
        if (!file) return;
        
        try {
            const text = await file.text();
            const data = JSON.parse(text);
            
            if (confirm('This will replace the current database. Are you sure?')) {
                showAlert('Importing database...', 'info');
                await window.cloudStorage.uploadDatabase(data);
                showAlert('Database imported successfully!', 'success');
                
                // Refresh all data
                await updateStats();
                await loadStudents();
                await loadResults();
                await loadDatabasePreview();
            }
        } catch (error) {
            console.error('Import error:', error);
            showAlert('Failed to import database. Please check the file format.', 'danger');
        }
    });
    
    // Utility functions
    function showAlert(message, type) {
        const container = document.getElementById('alertContainer');
        const alertClass = type === 'info' ? 'alert-primary' : `alert-${type}`;
        
        container.innerHTML = `
            <div class="alert ${alertClass}">
                <i class="fa fa-${type === 'success' ? 'check' : type === 'danger' ? 'exclamation-triangle' : 'info-circle'}"></i>
                ${message}
            </div>
        `;
        
        if (type === 'success' || type === 'danger') {
            setTimeout(() => {
                container.innerHTML = '';
            }, 5000);
        }
    }
    
    function hideAlert() {
        document.getElementById('alertContainer').innerHTML = '';
    }
    
    // Global functions for inline handlers
    window.deleteStudent = async function(rollNumber) {
        if (confirm(`Are you sure you want to delete student ${rollNumber} and all their results?`)) {
            try {
                showAlert('Deleting student...', 'info');
                await window.cloudStorage.deleteStudent(rollNumber);
                showAlert('Student deleted successfully!', 'success');
                
                await loadStudents();
                await loadResults();
                await updateStats();
            } catch (error) {
                console.error('Delete error:', error);
                showAlert('Failed to delete student', 'danger');
            }
        }
    };
});