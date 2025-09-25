/**
 * School Students Management JavaScript
 * Handles student search, edit, modal control, and export functions
 */
document.addEventListener('DOMContentLoaded', function() {
    // Elements
    const searchInput = document.getElementById('searchStudents');
    const studentRows = document.querySelectorAll('#all-students tbody tr');
    const exportExcelBtn = document.getElementById('exportExcel');
    const printPDFBtn = document.getElementById('printPDF');
    const editStudentModal = new bootstrap.Modal(document.getElementById('editStudentModal'));
    const addStudentForm = document.getElementById('addStudentForm');
    const editStudentForm = document.getElementById('editStudentForm');
    const loadingOverlay = document.getElementById('loading-overlay');
    
    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
    
    // Student search functionality
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            
            studentRows.forEach(row => {
                let found = false;
                // Search through all cells except the last one (actions)
                const cells = row.querySelectorAll('td:not(:last-child)');
                
                cells.forEach(cell => {
                    if (cell.textContent.toLowerCase().includes(query)) {
                        found = true;
                    }
                });
                
                row.style.display = found ? '' : 'none';
            });
        });
    }
    
    // Form submission handlers
    if (addStudentForm) {
        addStudentForm.addEventListener('submit', function(e) {
            if (!validateStudentForm(this)) {
                e.preventDefault();
                return false;
            }
            
            showLoadingOverlay();
            return true;
        });
    }
    
    if (editStudentForm) {
        editStudentForm.addEventListener('submit', function(e) {
            if (!validateStudentForm(this)) {
                e.preventDefault();
                return false;
            }
            
            showLoadingOverlay();
            return true;
        });
    }
    
    // Export to Excel
    if (exportExcelBtn) {
        exportExcelBtn.addEventListener('click', function() {
            exportTableToExcel('all-students');
        });
    }
    
    // Print as PDF
    if (printPDFBtn) {
        printPDFBtn.addEventListener('click', function() {
            window.print();
        });
    }
});

/**
 * Validates student form data
 * @param {HTMLFormElement} form - The form to validate
 * @returns {boolean} - Whether the form is valid
 */
function validateStudentForm(form) {
    let isValid = true;
    
    // Reset previous error messages
    const errorMessages = form.querySelectorAll('.error-message');
    errorMessages.forEach(el => el.remove());
    
    // Validate student name
    const studentName = form.querySelector('[name="student_name"]');
    if (!studentName.value.trim()) {
        showError(studentName, 'Student name is required');
        isValid = false;
    }
    
    // Validate parent name
    const parentName = form.querySelector('[name="parent_name"]');
    if (!parentName.value.trim()) {
        showError(parentName, 'Parent name is required');
        isValid = false;
    }
    
    // Validate email
    const email = form.querySelector('[name="email"]');
    if (!email.value.trim()) {
        showError(email, 'Email is required');
        isValid = false;
    } else if (!isValidEmail(email.value.trim())) {
        showError(email, 'Please enter a valid email address');
        isValid = false;
    }
    
    // Validate mobile
    const mobile = form.querySelector('[name="mobile"]');
    if (!mobile.value.trim()) {
        showError(mobile, 'Mobile number is required');
        isValid = false;
    } else if (!isValidMobile(mobile.value.trim())) {
        showError(mobile, 'Please enter a valid 10-digit mobile number');
        isValid = false;
    }
    
    // Validate class
    const classSelect = form.querySelector('[name="class"]');
    if (!classSelect.value) {
        showError(classSelect, 'Please select a class');
        isValid = false;
    }
    
    // Validate password (only for new students or if provided for existing)
    const password = form.querySelector('[name="password"]');
    if (password && 
        ((form.id === 'addStudentForm' && !password.value.trim()) || 
        (password.value.trim() !== '' && password.value.trim().length < 8))) {
        
        showError(password, 'Password must be at least 8 characters');
        isValid = false;
    }
    
    return isValid;
}

/**
 * Shows an error message under an input
 * @param {HTMLElement} input - The input with an error
 * @param {string} message - The error message
 */
function showError(input, message) {
    const parentElement = input.parentElement;
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message text-danger mt-1 small';
    errorDiv.textContent = message;
    parentElement.appendChild(errorDiv);
    input.classList.add('is-invalid');
    
    input.addEventListener('input', function removeError() {
        const errorMsg = parentElement.querySelector('.error-message');
        if (errorMsg) {
            errorMsg.remove();
            input.classList.remove('is-invalid');
            input.removeEventListener('input', removeError);
        }
    });
}

/**
 * Validates an email using regex
 * @param {string} email - Email address to validate
 * @returns {boolean} - Whether the email is valid
 */
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

/**
 * Validates a mobile number
 * @param {string} mobile - Mobile number to validate
 * @returns {boolean} - Whether the mobile number is valid
 */
function isValidMobile(mobile) {
    // Remove any non-digit characters
    const digits = mobile.replace(/\D/g, '');
    return digits.length === 10;
}

/**
 * Sets up the edit student modal with data
 * @param {number} id - Student ID
 * @param {string} studentName - Student name
 * @param {string} parentName - Parent name
 * @param {string} email - Email address
 * @param {string} mobile - Mobile number
 * @param {string} parentId - Parent ID
 * @param {string} classValue - Class value
 */
function editStudent(id, studentName, parentName, email, mobile, parentId, classValue) {
    // Set form values
    document.getElementById('editStudentId').value = id;
    document.getElementById('editStudentName').value = studentName;
    document.getElementById('editParentName').value = parentName;
    document.getElementById('editEmail').value = email;
    document.getElementById('editMobile').value = mobile;
    document.getElementById('editParentID').value = parentId;
    document.getElementById('editClass').value = classValue;
    
    // Clear password field
    document.getElementById('editPassword').value = '';
    
    // Show modal
    const editStudentModal = new bootstrap.Modal(document.getElementById('editStudentModal'));
    editStudentModal.show();
}

/**
 * Exports the students table to Excel
 * @param {string} tableId - ID of the table to export
 */
function exportTableToExcel(tableId) {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    // Get the table HTML
    const tableHTML = table.outerHTML.replace(/ /g, '%20');
    
    // Create a download link
    const downloadLink = document.createElement('a');
    document.body.appendChild(downloadLink);
    
    // Create the file name with current date
    const date = new Date();
    const fileName = `students_data_${date.getFullYear()}-${(date.getMonth()+1)}-${date.getDate()}.xls`;
    
    // Set link attributes
    downloadLink.href = 'data:application/vnd.ms-excel,' + tableHTML;
    downloadLink.download = fileName;
    
    // Click the link and remove it
    downloadLink.click();
    document.body.removeChild(downloadLink);
}

/**
 * Shows the loading overlay
 */
function showLoadingOverlay() {
    const loadingOverlay = document.getElementById('loading-overlay');
    if (loadingOverlay) {
        loadingOverlay.classList.remove('d-none');
    }
}

/**
 * Hides the loading overlay
 */
function hideLoadingOverlay() {
    const loadingOverlay = document.getElementById('loading-overlay');
    if (loadingOverlay) {
        loadingOverlay.classList.add('d-none');
    }
} 