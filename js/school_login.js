// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Get elements
    const loginForm = document.getElementById('schoolLoginForm');
    const loadingOverlay = document.getElementById('loading-overlay');
    const togglePasswordBtn = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    const schoolIdInput = document.getElementById('schoolId');
    const rememberMeCheckbox = document.getElementById('rememberMe');
    const alertsContainer = document.querySelector('.alerts-container');
    
    // Auto-hide alerts after 5 seconds
    if (alertsContainer) {
        const alerts = alertsContainer.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.opacity = '0';
                setTimeout(() => {
                    alert.remove();
                }, 500);
            }, 5000);
        });
    }
    
    // Handle password visibility toggle
    if (togglePasswordBtn) {
      togglePasswordBtn.addEventListener('click', function() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        
        // Toggle eye icon
        const eyeIcon = togglePasswordBtn.querySelector('i');
        if (type === 'text') {
            eyeIcon.classList.remove('fa-eye');
            eyeIcon.classList.add('fa-eye-slash');
        } else {
            eyeIcon.classList.remove('fa-eye-slash');
            eyeIcon.classList.add('fa-eye');
        }
      });
    }
    
    // Check if there are saved credentials in localStorage
    if (localStorage.getItem('rememberSchool') === 'true') {
        const savedSchoolId = localStorage.getItem('schoolId');
        if (savedSchoolId) {
            schoolIdInput.value = savedSchoolId;
            rememberMeCheckbox.checked = true;
        }
    }
    
    // Handle form submission
    if (loginForm) {
      loginForm.addEventListener('submit', function(event) {
        event.preventDefault();
        
        // Validate form
        if (!validateForm()) return;
        
        // Show loading overlay
        showLoading();
        
        // Get form data
        const schoolId = document.getElementById('schoolId').value.trim();
        const password = document.getElementById('password').value;
        const rememberMe = document.getElementById('rememberMe').checked;
        
        // Create form data for API call
        const formData = new FormData();
        formData.append('school_id', schoolId);
        formData.append('password', password);
        formData.append('remember_me', rememberMe);
        
        // Send login request
        fetch('api/school_login.php', {
          method: 'POST',
          body: formData,
          credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
          hideLoading();
          
          if (data.success) {
            // Login successful - redirect
            showSuccessMessage('Login successful! Redirecting to dashboard...');
            setTimeout(() => {
              window.location.href = data.redirect || 'school_dashboard.php';
            }, 1000);
          } else {
            // Login failed - show error
            showErrorMessage(data.message || 'Invalid School ID or Password');
            highlightInvalidFields();
          }
        })
        .catch(error => {
          hideLoading();
          console.error('Login error:', error);
          showErrorMessage('Network error. Please try again later.');
        });
      });
    }
    
    // Form validation
    function validateForm() {
      let isValid = true;
      
      // School ID validation
      if (!schoolIdInput.value.trim()) {
        schoolIdInput.classList.add('is-invalid');
        isValid = false;
      } else if (!/^[A-Za-z0-9]{6,}$/.test(schoolIdInput.value.trim())) {
        schoolIdInput.classList.add('is-invalid');
        isValid = false;
      } else {
        schoolIdInput.classList.remove('is-invalid');
      }
      
      // Password validation
      if (!passwordInput.value) {
        passwordInput.classList.add('is-invalid');
        isValid = false;
      } else if (passwordInput.value.length < 8) {
        passwordInput.classList.add('is-invalid');
        isValid = false;
      } else {
        passwordInput.classList.remove('is-invalid');
      }
      
      return isValid;
    }
    
    // Highlight invalid fields on login error
    function highlightInvalidFields() {
      schoolIdInput.classList.add('is-invalid');
      passwordInput.classList.add('is-invalid');
    }
    
    // Show loading overlay
    function showLoading() {
      if (loadingOverlay) {
        loadingOverlay.classList.remove('d-none');
      }
    }
    
    // Hide loading overlay
    function hideLoading() {
      if (loadingOverlay) {
        loadingOverlay.classList.add('d-none');
      }
    }
    
    // Show success message as toast
    function showSuccessMessage(message) {
      createToast(message, 'success');
    }
    
    // Show error message as toast
    function showErrorMessage(message) {
      createToast(message, 'danger');
    }
    
    // Create toast notification
    function createToast(message, type = 'info') {
      // Remove existing toasts first
      const existingToasts = document.querySelectorAll('.toast');
      existingToasts.forEach(toast => {
        const bsToast = bootstrap.Toast.getInstance(toast);
        if (bsToast) bsToast.dispose();
        toast.remove();
      });
      
      // Create new toast
      const toastContainer = document.createElement('div');
      toastContainer.className = 'position-fixed bottom-0 end-0 p-3';
      toastContainer.style.zIndex = '5';
      
      const iconMap = {
        success: 'fa-check-circle',
        danger: 'fa-exclamation-circle',
        warning: 'fa-exclamation-triangle',
        info: 'fa-info-circle'
      };
      
      const icon = iconMap[type] || iconMap.info;
      
      toastContainer.innerHTML = `
        <div class="toast align-items-center text-white bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true">
          <div class="d-flex">
            <div class="toast-body">
              <i class="fas ${icon} me-2"></i> ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
          </div>
        </div>
      `;
      
      document.body.appendChild(toastContainer);
      
      // Show the toast
      const toastElement = toastContainer.querySelector('.toast');
      const toast = new bootstrap.Toast(toastElement, { delay: 5000 });
      toast.show();
      
      // Remove toast when hidden
      toastElement.addEventListener('hidden.bs.toast', function() {
        toastContainer.remove();
      });
    }
    
    // Auto-remove error toast after 5 seconds
    const errorToast = document.getElementById('errorToast');
    if (errorToast) {
      const bsToast = new bootstrap.Toast(errorToast, { delay: 5000 });
      bsToast.show();
    }
});