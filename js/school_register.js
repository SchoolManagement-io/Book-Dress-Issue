// School Registration JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Get form elements
    const registerForm = document.getElementById('schoolRegisterForm');
    const schoolNameInput = document.getElementById('schoolName');
    const schoolIdInput = document.getElementById('schoolId');
    const emailInput = document.getElementById('email');
    const phoneInput = document.getElementById('phone');
    const addressInput = document.getElementById('address');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirmPassword');
    const termsCheckbox = document.getElementById('terms');
    const strengthMeter = document.getElementById('passwordStrength');
    const strengthText = document.getElementById('strengthText');
    
    // Toggle password visibility
    const togglePasswordButtons = document.querySelectorAll('.toggle-password');
    togglePasswordButtons.forEach(button => {
        button.addEventListener('click', function() {
            const input = this.previousElementSibling;
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            
            // Toggle eye icon
            const icon = this.querySelector('i');
            if (type === 'text') {
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });
    
    // Password strength meter
    if (passwordInput && strengthMeter && strengthText) {
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            const strength = checkPasswordStrength(password);
            
            // Update strength meter
            strengthMeter.style.width = strength.percentage + '%';
            strengthMeter.className = 'progress-bar ' + strength.class;
            strengthText.innerText = strength.text;
        });
    }
    
    // Confirm password validation
    if (confirmPasswordInput && passwordInput) {
        confirmPasswordInput.addEventListener('input', function() {
            if (passwordInput.value !== this.value) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });
        
        passwordInput.addEventListener('input', function() {
            if (confirmPasswordInput.value && confirmPasswordInput.value !== this.value) {
                confirmPasswordInput.setCustomValidity('Passwords do not match');
            } else {
                confirmPasswordInput.setCustomValidity('');
            }
        });
    }
    
    // School ID validation - check for alphanumeric and min length
    if (schoolIdInput) {
        schoolIdInput.addEventListener('input', function() {
            const value = this.value.trim();
            const isValid = /^[A-Za-z0-9]{6,}$/.test(value);
            
            if (!isValid && value) {
                this.setCustomValidity('School ID must contain at least 6 alphanumeric characters');
            } else {
                this.setCustomValidity('');
            }
        });
    }
    
    // Phone number validation (basic validation, can be enhanced further)
    if (phoneInput) {
        phoneInput.addEventListener('input', function() {
            const value = this.value.trim();
            // Allow only numbers, spaces, plus, and hyphens
            const isValid = /^[0-9\s\-\+]{10,15}$/.test(value);
            
            if (!isValid && value) {
                this.setCustomValidity('Please enter a valid phone number');
            } else {
                this.setCustomValidity('');
            }
        });
    }
    
    // Form submission
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            let isValid = true;
            
            // Validate all required fields
            if (!schoolNameInput.value.trim()) {
                isValid = false;
                schoolNameInput.classList.add('is-invalid');
            }
            
            if (!schoolIdInput.value.trim() || !/^[A-Za-z0-9]{6,}$/.test(schoolIdInput.value.trim())) {
                isValid = false;
                schoolIdInput.classList.add('is-invalid');
            }
            
            if (!emailInput.value.trim() || !/^\S+@\S+\.\S+$/.test(emailInput.value.trim())) {
                isValid = false;
                emailInput.classList.add('is-invalid');
            }
            
            if (!phoneInput.value.trim()) {
                isValid = false;
                phoneInput.classList.add('is-invalid');
            }
            
            if (!addressInput.value.trim()) {
                isValid = false;
                addressInput.classList.add('is-invalid');
            }
            
            if (!passwordInput.value || passwordInput.value.length < 8) {
                isValid = false;
                passwordInput.classList.add('is-invalid');
            }
            
            if (!confirmPasswordInput.value || confirmPasswordInput.value !== passwordInput.value) {
                isValid = false;
                confirmPasswordInput.classList.add('is-invalid');
            }
            
            if (!termsCheckbox.checked) {
                isValid = false;
                termsCheckbox.classList.add('is-invalid');
            }
            
            // If form is valid, show loading overlay
            if (isValid) {
                showLoadingOverlay();
            } else {
                e.preventDefault();
            }
        });
    }
    
    // Check password strength
    function checkPasswordStrength(password) {
        // Default values
        let strength = {
            percentage: 0,
            class: 'bg-danger',
            text: 'Very Weak'
        };
        
        if (!password) {
            return strength;
        }
        
        let score = 0;
        
        // Length check
        if (password.length >= 8) score += 20;
        if (password.length >= 12) score += 10;
        
        // Complexity checks
        if (/[a-z]/.test(password)) score += 15; // lowercase
        if (/[A-Z]/.test(password)) score += 15; // uppercase
        if (/[0-9]/.test(password)) score += 15; // numbers
        if (/[^A-Za-z0-9]/.test(password)) score += 15; // special characters
        
        // Mixed character types
        const variations = {
            digits: /\d/.test(password),
            lower: /[a-z]/.test(password),
            upper: /[A-Z]/.test(password),
            nonWords: /\W/.test(password)
        };
        
        let variationCount = 0;
        for (let check in variations) {
            variationCount += (variations[check] === true) ? 1 : 0;
        }
        
        score += (variationCount - 1) * 10;
        
        // Set strength based on score
        if (score >= 80) {
            strength = { percentage: 100, class: 'bg-success', text: 'Very Strong' };
        } else if (score >= 60) {
            strength = { percentage: 75, class: 'bg-info', text: 'Strong' };
        } else if (score >= 40) {
            strength = { percentage: 50, class: 'bg-warning', text: 'Medium' };
        } else if (score >= 20) {
            strength = { percentage: 25, class: 'bg-danger', text: 'Weak' };
        } else {
            strength = { percentage: 10, class: 'bg-danger', text: 'Very Weak' };
        }
        
        return strength;
    }
    
    // Show loading overlay
    function showLoadingOverlay() {
        const overlay = document.createElement('div');
        overlay.id = 'loading-overlay';
        overlay.className = 'position-fixed top-0 start-0 w-100 h-100 d-flex flex-column justify-content-center align-items-center';
        overlay.style.backgroundColor = 'rgba(0, 0, 0, 0.7)';
        overlay.style.zIndex = '9999';
        
        overlay.innerHTML = `
            <div class="spinner-border text-light mb-3" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <div class="text-white mb-2">Processing Registration...</div>
            <div class="text-white small">Please wait, this may take a moment</div>
        `;
        
        document.body.appendChild(overlay);
    }
    
    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.remove();
            }, 500);
        }, 5000);
    });
}); 