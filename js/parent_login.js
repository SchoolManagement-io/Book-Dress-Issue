/**
 * Samridhi Book Dress - Parent Login JavaScript
 * Handles form validation and user interaction
 */

document.addEventListener('DOMContentLoaded', function() {
  // Toggle password visibility
  const togglePassword = document.getElementById('togglePassword');
  const passwordField = document.getElementById('password');
  
  if (togglePassword && passwordField) {
    togglePassword.addEventListener('click', function() {
      const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
      passwordField.setAttribute('type', type);
      
      // Toggle eye icon
      this.querySelector('i').classList.toggle('fa-eye');
      this.querySelector('i').classList.toggle('fa-eye-slash');
    });
  }
  
  // Form validation
  const loginForm = document.getElementById('loginForm');
  
  if (loginForm) {
    loginForm.addEventListener('submit', function(e) {
      let isValid = true;
      const parentID = document.getElementById('parentID');
      const password = document.getElementById('password');
      
      // Validate Parent ID
      if (!parentID.value.trim()) {
        parentID.classList.add('is-invalid');
        isValid = false;
      } else {
        parentID.classList.remove('is-invalid');
        parentID.classList.add('is-valid');
      }
      
      // Validate Password
      if (!password.value.trim()) {
        password.classList.add('is-invalid');
        isValid = false;
      } else {
        password.classList.remove('is-invalid');
        password.classList.add('is-valid');
      }
      
      if (!isValid) {
        e.preventDefault();
      } else {
        // Show loading overlay
        const loadingOverlay = document.getElementById('loading-overlay');
        if (loadingOverlay) {
          loadingOverlay.classList.remove('d-none');
        }
      }
    });
  }
  
  // Handle "Remember Me" checkbox
  const rememberCheckbox = document.getElementById('rememberMe');
  
  if (rememberCheckbox) {
    // Check if there's a saved parent ID
    const savedParentID = localStorage.getItem('parentID');
    if (savedParentID) {
      document.getElementById('parentID').value = savedParentID;
      rememberCheckbox.checked = true;
    }
    
    // Save parent ID if checkbox is checked
    rememberCheckbox.addEventListener('change', function() {
      if (this.checked) {
        const parentID = document.getElementById('parentID').value;
        if (parentID) {
          localStorage.setItem('parentID', parentID);
        }
      } else {
        localStorage.removeItem('parentID');
      }
    });
  }
  
  // Check for login error in URL parameters
  const urlParams = new URLSearchParams(window.location.search);
  if (urlParams.get('error') === 'login') {
    const errorToast = document.getElementById('errorToast');
    if (errorToast) {
      const toast = new bootstrap.Toast(errorToast);
      toast.show();
    }
  }
}); 