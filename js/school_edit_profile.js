// School Edit Profile JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Form elements
    const basicInfoForm = document.getElementById('basicInfoForm');
    const passwordForm = document.getElementById('passwordForm');
    const addressForm = document.getElementById('addressForm');
    const notificationForm = document.getElementById('notificationForm');
    
    // Password strength elements
    const newPasswordInput = document.getElementById('newPassword');
    const confirmPasswordInput = document.getElementById('confirmPassword');
    const strengthMeter = document.getElementById('passwordStrength');
    const strengthText = document.getElementById('strengthText');
    
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
    
    // Basic Info Form Validation
    if (basicInfoForm) {
        basicInfoForm.addEventListener('submit', function(e) {
            if (!validateBasicInfoForm()) {
                e.preventDefault();
            } else {
                showLoadingOverlay();
            }
        });
    }
    
    // Password Form Validation
    if (passwordForm) {
        passwordForm.addEventListener('submit', function(e) {
            if (!validatePasswordForm()) {
                e.preventDefault();
            } else {
                showLoadingOverlay();
            }
        });
    }
    
    // Address Form Validation
    if (addressForm) {
        addressForm.addEventListener('submit', function(e) {
            if (!validateAddressForm()) {
                e.preventDefault();
            } else {
                showLoadingOverlay();
            }
        });
    }
    
    // Notification Form Submission
    if (notificationForm) {
        notificationForm.addEventListener('submit', function(e) {
            showLoadingOverlay();
        });
    }
    
    // Password strength meter
    if (newPasswordInput && strengthMeter && strengthText) {
        newPasswordInput.addEventListener('input', function() {
            const password = this.value;
            const strength = checkPasswordStrength(password);
            
            // Update strength meter
            strengthMeter.style.width = strength.percentage + '%';
            strengthMeter.className = 'progress-bar ' + strength.class;
            strengthText.innerText = strength.text;
        });
    }
    
    // Confirm password validation
    if (confirmPasswordInput && newPasswordInput) {
        confirmPasswordInput.addEventListener('input', function() {
            if (newPasswordInput.value !== this.value) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });
        
        newPasswordInput.addEventListener('input', function() {
            if (confirmPasswordInput.value && confirmPasswordInput.value !== this.value) {
                confirmPasswordInput.setCustomValidity('Passwords do not match');
            } else {
                confirmPasswordInput.setCustomValidity('');
            }
        });
    }
    
    // Basic Info Form Validation
    function validateBasicInfoForm() {
        let isValid = true;
        const schoolName = document.getElementById('schoolName');
        const emailAddress = document.getElementById('emailAddress');
        const phoneNumber = document.getElementById('phoneNumber');
        
        // School Name validation
        if (!schoolName.value.trim()) {
            showError(schoolName, 'School name is required');
            isValid = false;
        } else {
            removeError(schoolName);
        }
        
        // Email validation
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailAddress.value.trim() || !emailRegex.test(emailAddress.value.trim())) {
            showError(emailAddress, 'Valid email address is required');
            isValid = false;
        } else {
            removeError(emailAddress);
        }
        
        // Phone validation (basic)
        const phoneRegex = /^[0-9\s\-\+]{10,15}$/;
        if (!phoneNumber.value.trim() || !phoneRegex.test(phoneNumber.value.trim())) {
            showError(phoneNumber, 'Valid phone number is required');
            isValid = false;
        } else {
            removeError(phoneNumber);
        }
        
        return isValid;
    }
    
    // Password Form Validation
    function validatePasswordForm() {
        let isValid = true;
        const currentPassword = document.getElementById('currentPassword');
        const newPassword = document.getElementById('newPassword');
        const confirmPassword = document.getElementById('confirmPassword');
        
        // Current Password validation
        if (!currentPassword.value) {
            showError(currentPassword, 'Current password is required');
            isValid = false;
        } else {
            removeError(currentPassword);
        }
        
        // New Password validation
        if (!newPassword.value) {
            showError(newPassword, 'New password is required');
            isValid = false;
        } else if (newPassword.value.length < 8) {
            showError(newPassword, 'Password must be at least 8 characters');
            isValid = false;
        } else {
            removeError(newPassword);
        }
        
        // Confirm Password validation
        if (!confirmPassword.value) {
            showError(confirmPassword, 'Please confirm your password');
            isValid = false;
        } else if (newPassword.value !== confirmPassword.value) {
            showError(confirmPassword, 'Passwords do not match');
            isValid = false;
        } else {
            removeError(confirmPassword);
        }
        
        return isValid;
    }
    
    // Address Form Validation
    function validateAddressForm() {
        let isValid = true;
        const addressLine1 = document.getElementById('addressLine1');
        const city = document.getElementById('city');
        const state = document.getElementById('state');
        const pincode = document.getElementById('pincode');
        
        // Address Line 1 validation
        if (!addressLine1.value.trim()) {
            showError(addressLine1, 'Address line 1 is required');
            isValid = false;
        } else {
            removeError(addressLine1);
        }
        
        // City validation
        if (!city.value.trim()) {
            showError(city, 'City is required');
            isValid = false;
        } else {
            removeError(city);
        }
        
        // State validation
        if (!state.value) {
            showError(state, 'Please select a state');
            isValid = false;
        } else {
            removeError(state);
        }
        
        // PIN Code validation
        const pincodeRegex = /^[0-9]{6}$/;
        if (!pincode.value.trim() || !pincodeRegex.test(pincode.value.trim())) {
            showError(pincode, 'Valid 6-digit PIN code is required');
            isValid = false;
        } else {
            removeError(pincode);
        }
        
        return isValid;
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
    
    // Show error message for an input
    function showError(input, message) {
        // Remove any existing error first
        removeError(input);
        
        input.classList.add('is-invalid');
        const errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback';
        errorDiv.innerText = message;
        input.parentNode.appendChild(errorDiv);
    }
    
    // Remove error message from an input
    function removeError(input) {
        input.classList.remove('is-invalid');
        const errorDiv = input.parentNode.querySelector('.invalid-feedback');
        if (errorDiv) {
            errorDiv.remove();
        }
    }
    
    // Show loading overlay
    function showLoadingOverlay() {
        const overlay = document.getElementById('loading-overlay');
        if (overlay) {
            overlay.classList.remove('d-none');
        }
    }
}); 