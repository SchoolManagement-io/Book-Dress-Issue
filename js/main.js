/**
 * School Inventory Management System - Main JavaScript
 * Provides shared functionality across all pages
 */

// ============================================
// Utility Functions
// ============================================

/**
 * Format currency in Indian Rupees (INR)
 * @param {number} amount - Amount to format
 * @return {string} Formatted amount with ₹ symbol
 */
function formatINR(amount) {
    return '₹' + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}

/**
 * Get Indian greeting based on time of day
 * @param {string} name - Person's name
 * @return {string} Greeting with name
 */
function getIndianGreeting(name = '') {
    const hour = new Date().getHours();
    let greeting = '';
    let hindiGreeting = '';
    
    if (hour >= 5 && hour < 12) {
        greeting = 'Good Morning';
        hindiGreeting = 'सुप्रभात';
    } else if (hour >= 12 && hour < 17) {
        greeting = 'Good Afternoon';
        hindiGreeting = 'नमस्कार';
    } else {
        greeting = 'Good Evening';
        hindiGreeting = 'शुभ संध्या';
    }
    
    return `${hindiGreeting}, ${greeting}${name ? ' ' + name : ''}!`;
}

/**
 * Check if a date falls on an Indian festival
 * @param {Date} date - Date to check
 * @return {object|null} Festival info or null
 */
function getIndianFestival() {
    const today = new Date();
    const day = today.getDate();
    const month = today.getMonth() + 1; // JS months are 0-indexed
    
    const festivals = [
        { date: [15, 8], name: 'Independence Day', message: 'Celebrate freedom and education for all!' },
        { date: [5, 9], name: 'Teachers\' Day', message: 'Honor those who guide our future generations.' },
        { date: [2, 10], name: 'Gandhi Jayanti', message: 'Remember the values of peace and non-violence.' },
        { date: [25, 12], name: 'Christmas', message: 'Season\'s greetings to all our staff and students.' },
        { date: [26, 1], name: 'Republic Day', message: 'Celebrating the spirit of unity in diversity.' }
    ];
    
    // Check for festivals based on solar calendar (fixed dates)
    for (const festival of festivals) {
        if (festival.date[0] === day && festival.date[1] === month) {
            return festival;
        }
    }
    
    // For lunar calendar festivals (Diwali, Holi, etc.) we would need more complex logic
    // This is just a placeholder for demonstration
    
    return null;
}

// ============================================
// UI Enhancement Functions
// ============================================

/**
 * Initialize floating notifications
 * @param {string} message - Notification message
 * @param {string} type - Notification type (success, warning, error)
 */
function showNotification(message, type = 'info', duration = 5000) {
    // Create notification container if it doesn't exist
    let container = document.querySelector('.notification-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'notification-container';
        document.body.appendChild(container);
    }
    
    // Create notification element
    const notification = document.createElement('div');
    notification.className = 'notification notification-' + type + ' animate__animated animate__fadeInRight';
    
    // Set icon based on type
    let icon = '';
    switch (type) {
        case 'success':
            icon = '<i class="fas fa-check-circle"></i>';
            break;
        case 'warning':
            icon = '<i class="fas fa-exclamation-triangle"></i>';
            break;
        case 'error':
            icon = '<i class="fas fa-times-circle"></i>';
            break;
        default:
            icon = '<i class="fas fa-info-circle"></i>';
    }
    
    // Set content
    notification.innerHTML = `
        <div class="notification-icon">${icon}</div>
        <div class="notification-content">${message}</div>
        <button class="notification-close"><i class="fas fa-times"></i></button>
    `;
    
    // Add to container
    container.appendChild(notification);
    
    // Add close functionality
    const closeBtn = notification.querySelector('.notification-close');
    closeBtn.addEventListener('click', () => {
        notification.classList.remove('animate__fadeInRight');
        notification.classList.add('animate__fadeOutRight');
        
        notification.addEventListener('animationend', () => {
            notification.remove();
        });
    });
    
    // Auto-remove after duration
    setTimeout(() => {
        if (notification.parentNode) {
            notification.classList.remove('animate__fadeInRight');
            notification.classList.add('animate__fadeOutRight');
            
            notification.addEventListener('animationend', () => {
                notification.remove();
            });
        }
    }, duration);
}

/**
 * Initialize interactive data counter animations
 */
function initCounters() {
    const counters = document.querySelectorAll('.counter-value');
    
    counters.forEach(counter => {
        const target = parseInt(counter.getAttribute('data-target'));
        const duration = 1500; // milliseconds
        const stepTime = 20; // milliseconds
        const totalSteps = duration / stepTime;
        const stepValue = target / totalSteps;
        let currentValue = 0;
        let prefix = counter.getAttribute('data-prefix') || '';
        let suffix = counter.getAttribute('data-suffix') || '';
        
        const updateCounter = () => {
            currentValue += stepValue;
            if (currentValue >= target) {
                counter.textContent = prefix + target + suffix;
            } else {
                counter.textContent = prefix + Math.ceil(currentValue) + suffix;
                requestAnimationFrame(updateCounter);
            }
        };
        
        // Start the animation when element is in viewport
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    updateCounter();
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });
        
        observer.observe(counter);
    });
}

/**
 * Initialize chart.js charts
 * @param {string} selector - CSS selector for canvas element
 * @param {string} type - Chart type (line, bar, pie, etc.)
 * @param {object} data - Chart data
 * @param {object} options - Chart options
 */
function initChart(selector, type, data, options = {}) {
    const ctx = document.querySelector(selector).getContext('2d');
    
    // Set default options with Indian styling
    const defaultOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            title: {
                display: true,
                font: {
                    family: "'Poppins', sans-serif",
                    size: 16,
                    weight: 'bold'
                }
            },
            legend: {
                labels: {
                    font: {
                        family: "'Roboto', sans-serif",
                        size: 12
                    }
                }
            },
            tooltip: {
                backgroundColor: 'rgba(0, 0, 0, 0.7)',
                titleFont: {
                    family: "'Poppins', sans-serif",
                    size: 14
                },
                bodyFont: {
                    family: "'Roboto', sans-serif",
                    size: 12
                },
                callbacks: {
                    label: function(context) {
                        let label = context.dataset.label || '';
                        if (label) {
                            label += ': ';
                        }
                        if (context.parsed.y !== null) {
                            // Check if the data should be formatted as currency
                            if (context.dataset.isCurrency) {
                                label += formatINR(context.parsed.y);
                            } else {
                                label += context.parsed.y;
                            }
                        }
                        return label;
                    }
                }
            }
        }
    };
    
    // Merge default options with custom options
    const mergedOptions = { ...defaultOptions, ...options };
    
    // Create the chart
    return new Chart(ctx, {
        type: type,
        data: data,
        options: mergedOptions
    });
}

/**
 * AJAX function for loading content without page refresh
 * @param {string} url - URL to fetch content from
 * @param {string} targetSelector - CSS selector for target element
 * @param {function} callback - Callback function after content is loaded
 */
function loadContent(url, targetSelector, callback = null) {
    const target = document.querySelector(targetSelector);
    
    if (!target) {
        console.error(`Target element "${targetSelector}" not found`);
        return;
    }
    
    // Show loading spinner
    target.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
    
    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(html => {
            // Insert the HTML into the target element
            target.innerHTML = html;
            
            // Call the callback function if provided
            if (callback && typeof callback === 'function') {
                callback();
            }
        })
        .catch(error => {
            target.innerHTML = `
                <div class="alert alert-danger" role="alert">
                    <h4 class="alert-heading">Oops, something went wrong!</h4>
                    <p>We couldn't load the content. Please try again or contact support.</p>
                    <hr>
                    <p class="mb-0">Error: ${error.message}</p>
                </div>
            `;
            console.error('Error loading content:', error);
        });
}

/**
 * Initialize interactive search functionality
 * @param {string} inputSelector - CSS selector for search input
 * @param {string} itemsSelector - CSS selector for items to search within
 */
function initSearch(inputSelector, itemsSelector) {
    const searchInput = document.querySelector(inputSelector);
    const items = document.querySelectorAll(itemsSelector);
    
    if (!searchInput || !items.length) return;
    
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase().trim();
        
        items.forEach(item => {
            const text = item.textContent.toLowerCase();
            if (text.includes(searchTerm)) {
                item.style.display = '';
                // Highlight matching text
                if (searchTerm) {
                    const regex = new RegExp(searchTerm, 'gi');
                    const originalHTML = item.innerHTML;
                    item.innerHTML = originalHTML.replace(regex, match => `<mark>${match}</mark>`);
                    
                    // Reset after a brief delay to avoid performance issues during typing
                    setTimeout(() => {
                        if (searchInput.value.toLowerCase().trim() === searchTerm) {
                            // Only reset if search term hasn't changed
                            item.innerHTML = originalHTML.replace(regex, match => `<mark>${match}</mark>`);
                        }
                    }, 200);
                }
            } else {
                item.style.display = 'none';
            }
        });
        
        // Show no results message if all items are hidden
        let visibleItems = 0;
        items.forEach(item => {
            if (item.style.display !== 'none') {
                visibleItems++;
            }
        });
        
        const noResultsMsg = document.querySelector('.no-results-message');
        if (visibleItems === 0) {
            if (!noResultsMsg) {
                const container = items[0].parentElement;
                const message = document.createElement('div');
                message.className = 'no-results-message alert alert-info mt-3';
                message.innerHTML = `<i class="fas fa-info-circle me-2"></i>No results found for "${searchTerm}"`;
                container.appendChild(message);
            }
        } else if (noResultsMsg) {
            noResultsMsg.remove();
        }
    });
}

/**
 * Initialize image card flips
 */
function initCardFlips() {
    const flipCards = document.querySelectorAll('.flip-card');
    
    flipCards.forEach(card => {
        card.addEventListener('click', function() {
            this.classList.toggle('flipped');
        });
    });
}

/**
 * Initialize sticky navigation
 */
function initStickyNav() {
    const navbar = document.querySelector('.navbar');
    if (!navbar) return;
    
    const sticky = navbar.offsetTop;
    
    function handleScroll() {
        if (window.pageYOffset > sticky) {
            navbar.classList.add('sticky-nav');
        } else {
            navbar.classList.remove('sticky-nav');
        }
    }
    
    window.addEventListener('scroll', handleScroll);
}

/**
 * Initialize festival notifications
 */
function initFestivalNotifications() {
    const festival = getIndianFestival();
    
    if (festival) {
        const festivalBanner = document.createElement('div');
        festivalBanner.className = 'festival-banner';
        festivalBanner.innerHTML = `
            <div class="festival-content">
                <h3><span class="festival-icon">🪔</span> Happy ${festival.name}!</h3>
                <p>${festival.message}</p>
            </div>
            <button class="festival-close"><i class="fas fa-times"></i></button>
        `;
        
        document.body.appendChild(festivalBanner);
        
        // Show the banner with animation
        setTimeout(() => {
            festivalBanner.classList.add('show');
        }, 1000);
        
        // Close button
        festivalBanner.querySelector('.festival-close').addEventListener('click', () => {
            festivalBanner.classList.remove('show');
            setTimeout(() => {
                document.body.removeChild(festivalBanner);
            }, 500);
        });
    }
}

/**
 * Initialize all components when DOM is loaded
 */
document.addEventListener('DOMContentLoaded', function() {
    // Initialize interactive components
    initStickyNav();
    initCounters();
    initCardFlips();
    initFestivalNotifications();
    
    // Add active class to current page in navigation
    const currentPage = window.location.pathname.split('/').pop();
    const navLinks = document.querySelectorAll('.navbar-nav .nav-link, .sidebar-menu a');
    
    navLinks.forEach(link => {
        const linkHref = link.getAttribute('href');
        if (linkHref === currentPage) {
            link.classList.add('active');
        }
    });
    
    // Password visibility toggle
    const passwordToggles = document.querySelectorAll('.password-toggle');
    
    passwordToggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
            const input = this.previousElementSibling;
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            
            // Toggle eye icon
            const eyeIcon = this.querySelector('i');
            if (type === 'text') {
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        });
    });
    
    // Form validation with animation
    const forms = document.querySelectorAll('form.needs-validation');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
                
                // Add shake animation to invalid fields
                const invalidInputs = form.querySelectorAll(':invalid');
                invalidInputs.forEach(input => {
                    input.parentElement.classList.add('shake');
                    setTimeout(() => {
                        input.parentElement.classList.remove('shake');
                    }, 650);
                });
            }
            
            form.classList.add('was-validated');
        });
    });
    
    // Auto-initialize search if elements exist
    if (document.querySelector('#searchInput') && document.querySelector('.searchable-item')) {
        initSearch('#searchInput', '.searchable-item');
    }
    
    // Initialize tooltips and popovers
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
}); 