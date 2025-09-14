/**
 * Results Page JavaScript
 * Handles student result lookup and display
 */
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('resultForm');
    const loadingState = document.getElementById('loadingState');
    const errorMessage = document.getElementById('errorMessage');
    const errorText = document.getElementById('errorText');
    const resultDisplay = document.getElementById('resultDisplay');
    const submitBtn = document.getElementById('submitBtn');

    // Form submission handler
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const rollNumber = document.getElementById('rollNumber').value.trim();
        const semester = document.getElementById('semester').value;
        const studentName = document.getElementById('studentName').value.trim();
        
        // Reset display states
        hideAllMessages();
        showLoading();
        
        try {
            // Validate inputs
            if (!rollNumber || !semester || !studentName) {
                throw new Error('Please fill in all fields');
            }
            
            // Verify student credentials
            console.log('Verifying student:', rollNumber, studentName);
            const verification = await window.cloudStorage.verifyStudent(rollNumber, studentName);
            
            if (!verification.valid) {
                throw new Error(verification.error);
            }
            
            // Get student result
            console.log('Getting result for semester:', semester);
            const resultData = await window.cloudStorage.getStudentResult(rollNumber, semester);
            
            if (!resultData.found) {
                throw new Error(resultData.error);
            }
            
            // Display result
            displayResult(verification.student, resultData.result, resultData.pdfUrl);
            
        } catch (error) {
            console.error('Error:', error);
            showError(error.message);
        } finally {
            hideLoading();
        }
    });

    function hideAllMessages() {
        loadingState.style.display = 'none';
        errorMessage.style.display = 'none';
        resultDisplay.style.display = 'none';
    }

    function showLoading() {
        loadingState.style.display = 'block';
        submitBtn.disabled = true;
    }

    function hideLoading() {
        loadingState.style.display = 'none';
        submitBtn.disabled = false;
    }

    function showError(message) {
        errorText.textContent = message;
        errorMessage.style.display = 'block';
        
        // Auto-hide error after 5 seconds
        setTimeout(() => {
            errorMessage.style.display = 'none';
        }, 5000);
    }

    function displayResult(student, result, pdfUrl) {
        // Update result display elements
        document.getElementById('resultStudentName').textContent = student.student_name;
        document.getElementById('resultRollNumber').textContent = student.roll_number;
        document.getElementById('resultSemester').textContent = result.semester;
        
        // Set PDF viewer source
        const pdfViewer = document.getElementById('pdfViewer');
        pdfViewer.src = pdfUrl;
        
        // Set download link
        const downloadLink = document.getElementById('downloadLink');
        downloadLink.href = pdfUrl;
        downloadLink.download = `${student.roll_number}_sem${result.semester}_result.pdf`;
        
        // Show result display
        resultDisplay.style.display = 'block';
        
        // Scroll to result
        resultDisplay.scrollIntoView({ behavior: 'smooth' });
        
        console.log('Result displayed successfully');
    }

    // Handle PDF load errors
    document.getElementById('pdfViewer').addEventListener('error', function() {
        console.error('PDF failed to load');
        showError('Unable to display PDF. Please try downloading the file directly.');
    });

    // Input validation
    document.getElementById('rollNumber').addEventListener('input', function(e) {
        // Remove any characters that aren't alphanumeric, dash, or underscore
        e.target.value = e.target.value.replace(/[^a-zA-Z0-9\-_]/g, '');
    });

    document.getElementById('studentName').addEventListener('input', function(e) {
        // Capitalize first letter of each word
        const words = e.target.value.split(' ');
        const capitalizedWords = words.map(word => {
            if (word.length > 0) {
                return word.charAt(0).toUpperCase() + word.slice(1).toLowerCase();
            }
            return word;
        });
        e.target.value = capitalizedWords.join(' ');
    });
});