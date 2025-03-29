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
 * @return {object|null} Festival info or null
 */
function getIndianFestival() {
    const today = new Date();
    const day = today.getDate();
    const month = today.getMonth() + 1; // JS months are 0-indexed
    
    const festivals = [
        { date: [15, 8], name: 'Independence Day', message: 'Celebrate freedom and education for all!', icon: 'flag' },
        { date: [5, 9], name: 'Teachers\' Day', message: 'Honor those who guide our future generations.', icon: 'chalkboard-teacher' },
        { date: [2, 10], name: 'Gandhi Jayanti', message: 'Remember the values of peace and non-violence.', icon: 'dove' },
        { date: [25, 12], name: 'Christmas', message: 'Season\'s greetings to all our staff and students.', icon: 'gift' },
        { date: [26, 1], name: 'Republic Day', message: 'Celebrating the spirit of unity in diversity.', icon: 'flag' },
        { date: [21, 6], name: 'International Yoga Day', message: 'Mind and body wellness for students and teachers.', icon: 'om' },
        { date: [14, 11], name: 'Children\'s Day', message: 'Celebrating the future of our nation!', icon: 'child' },
        { date: [14, 1], name: 'Makar Sankranti', message: 'Harvest festival celebrating new beginnings.', icon: 'sun' },
        { date: [13, 4], name: 'Baisakhi', message: 'Celebrate the spring harvest festival!', icon: 'wheat-alt' },
        { date: [10, 11], name: 'Diwali', message: 'Festival of lights and prosperity.', icon: 'diya-lamp' }
    ];
    
    // Check for festivals based on solar calendar (fixed dates)
    for (const festival of festivals) {
        if (festival.date[0] === day && festival.date[1] === month) {
            return festival;
        }
    }
    
    // For lunar calendar festivals (exact dates vary), we would need more complex logic
    // This is just a placeholder for demonstration
    
    return null;
}

/**
 * Converts English numbers to Devanagari numerals
 * @param {number} num - The number to convert
 * @return {string} Number in Devanagari script
 */
function toDevanagariNumerals(num) {
    const devanagariNumerals = ['०', '१', '२', '३', '४', '५', '६', '७', '८', '९'];
    return num.toString().split('').map(digit => {
        if (digit >= '0' && digit <= '9') {
            return devanagariNumerals[parseInt(digit)];
        }
        return digit;
    }).join('');
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
    if (!counters.length) return;
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const counter = entry.target;
                const target = parseInt(counter.getAttribute('data-target'));
                const duration = 2500; // milliseconds
                const stepTime = 20; // milliseconds
                const totalSteps = duration / stepTime;
                const stepValue = target / totalSteps;
                let currentValue = 0;
                let prefix = counter.getAttribute('data-prefix') || '';
                let suffix = counter.getAttribute('data-suffix') || '';
                
                // Add Hindi numeral version if element has data-hindi attribute
                let hindiCounter = null;
                if (counter.hasAttribute('data-hindi')) {
                    hindiCounter = document.createElement('div');
                    hindiCounter.className = 'hindi-counter';
                    hindiCounter.style.fontFamily = "'Tiro Devanagari Hindi', serif";
                    hindiCounter.style.fontSize = "1.2rem";
                    hindiCounter.style.color = "var(--accent-orange)";
                    hindiCounter.style.opacity = "0.8";
                    counter.parentNode.insertBefore(hindiCounter, counter.nextSibling);
                }
                
                const updateCounter = () => {
                    currentValue += stepValue;
                    if (currentValue >= target) {
                        counter.textContent = prefix + target.toLocaleString() + suffix;
                        if (hindiCounter) {
                            hindiCounter.textContent = prefix + toDevanagariNumerals(target) + suffix;
                        }
                    } else {
                        counter.textContent = prefix + Math.ceil(currentValue).toLocaleString() + suffix;
                        if (hindiCounter) {
                            hindiCounter.textContent = prefix + toDevanagariNumerals(Math.ceil(currentValue)) + suffix;
                        }
                        requestAnimationFrame(updateCounter);
                    }
                };
                
                updateCounter();
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.2 });
    
    counters.forEach(counter => {
        observer.observe(counter);
    });
}

/**
 * Initialize chart.js charts with Indian styling
 * @param {string} selector - CSS selector for canvas element
 * @param {string} type - Chart type (line, bar, pie, etc.)
 * @param {object} data - Chart data
 * @param {object} options - Chart options
 */
function initChart(selector, type, data, options = {}) {
    if (!document.querySelector(selector)) return;
    
    const ctx = document.querySelector(selector).getContext('2d');
    
    // Set default Indian color scheme
    const indianColors = [
        'rgba(255, 153, 51, 0.8)', // Saffron
        'rgba(19, 136, 8, 0.8)',   // Green
        'rgba(0, 102, 204, 0.8)',  // Blue
        'rgba(220, 53, 69, 0.8)',  // Red
        'rgba(255, 193, 7, 0.8)'   // Yellow
    ];
    
    // Apply Indian colors to datasets if not specified
    if (data.datasets) {
        data.datasets.forEach((dataset, index) => {
            if (!dataset.backgroundColor) {
                if (type === 'pie' || type === 'doughnut') {
                    dataset.backgroundColor = dataset.data.map((_, i) => indianColors[i % indianColors.length]);
                } else {
                    dataset.backgroundColor = indianColors[index % indianColors.length];
                }
            }
            
            if (!dataset.borderColor && type !== 'pie' && type !== 'doughnut') {
                dataset.borderColor = dataset.backgroundColor;
            }
        });
    }
    
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
    
    // Create chart
    new Chart(ctx, {
        type: type,
        data: data,
        options: mergedOptions
    });
}

/**
 * Load content via AJAX
 * @param {string} url - URL to load content from
 * @param {string} targetSelector - CSS selector for target element
 * @param {function} callback - Callback function after content is loaded
 */
function loadContent(url, targetSelector, callback = null) {
    const target = document.querySelector(targetSelector);
    if (!target) return;
    
    // Show loading indicator
    target.innerHTML = '<div class="text-center p-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-3">Loading content...</p></div>';
    
    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(html => {
            target.innerHTML = html;
            
            // Execute callback if provided
            if (callback && typeof callback === 'function') {
                callback();
            }
        })
        .catch(error => {
            target.innerHTML = `<div class="alert alert-danger" role="alert">
                <h4 class="alert-heading">Error Loading Content</h4>
                <p>We couldn't load the requested content. Please try again later.</p>
                <hr>
                <p class="mb-0">Error details: ${error.message}</p>
            </div>`;
            console.error('Error loading content:', error);
        });
}

/**
 * Initialize search functionality
 * @param {string} inputSelector - CSS selector for search input
 * @param {string} itemsSelector - CSS selector for items to search
 */
function initSearch(inputSelector, itemsSelector) {
    const searchInput = document.querySelector(inputSelector);
    const items = document.querySelectorAll(itemsSelector);
    
    if (!searchInput || !items.length) return;
    
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase().trim();
        let resultsFound = false;
        
        // Show/hide items based on search term
        items.forEach(item => {
            const text = item.textContent.toLowerCase();
            if (text.includes(searchTerm)) {
                item.style.display = '';
                resultsFound = true;
                
                // Highlight matching text if search term is not empty
                if (searchTerm.length > 0) {
                    highlightText(item, searchTerm);
                } else {
                    // Remove highlighting if search is cleared
                    removeHighlighting(item);
                }
            } else {
                item.style.display = 'none';
            }
        });
        
        // Show message if no results found
        let noResultsMessage = document.querySelector('.no-results-message');
        if (!resultsFound && searchTerm.length > 0) {
            if (!noResultsMessage) {
                noResultsMessage = document.createElement('div');
                noResultsMessage.className = 'no-results-message alert alert-warning mt-3';
                noResultsMessage.innerHTML = '<i class="fas fa-exclamation-circle me-2"></i>No results found for <strong>"' + searchTerm + '"</strong>';
                searchInput.parentNode.parentNode.appendChild(noResultsMessage);
            } else {
                noResultsMessage.innerHTML = '<i class="fas fa-exclamation-circle me-2"></i>No results found for <strong>"' + searchTerm + '"</strong>';
                noResultsMessage.style.display = '';
            }
        } else if (noResultsMessage) {
            noResultsMessage.style.display = 'none';
        }
    });
    
    // Helper function to highlight matching text
    function highlightText(element, searchTerm) {
        // First remove any existing highlights
        removeHighlighting(element);
        
        // Get all text nodes in the element
        const walk = document.createTreeWalker(element, NodeFilter.SHOW_TEXT, null, false);
        const textNodes = [];
        let node;
        while (node = walk.nextNode()) {
            textNodes.push(node);
        }
        
        // Highlight search term in each text node
        textNodes.forEach(textNode => {
            const text = textNode.nodeValue;
            const lowerText = text.toLowerCase();
            const index = lowerText.indexOf(searchTerm);
            
            if (index !== -1) {
                const span = document.createElement('span');
                span.className = 'highlight';
                span.style.backgroundColor = 'rgba(255, 193, 7, 0.3)';
                span.style.borderRadius = '2px';
                span.style.padding = '0 2px';
                
                const before = document.createTextNode(text.substring(0, index));
                const match = document.createTextNode(text.substring(index, index + searchTerm.length));
                const after = document.createTextNode(text.substring(index + searchTerm.length));
                
                span.appendChild(match);
                
                const fragment = document.createDocumentFragment();
                fragment.appendChild(before);
                fragment.appendChild(span);
                fragment.appendChild(after);
                
                textNode.parentNode.replaceChild(fragment, textNode);
            }
        });
    }
    
    // Helper function to remove highlighting
    function removeHighlighting(element) {
        const highlights = element.querySelectorAll('.highlight');
        highlights.forEach(highlight => {
            const parent = highlight.parentNode;
            const text = highlight.textContent;
            const textNode = document.createTextNode(text);
            parent.replaceChild(textNode, highlight);
            parent.normalize(); // Combine adjacent text nodes
        });
    }
}

/**
 * Initialize card flip functionality
 */
function initCardFlips() {
    const flipCards = document.querySelectorAll('.flip-card');
    
    flipCards.forEach(card => {
        card.addEventListener('click', () => {
            card.classList.toggle('flipped');
        });
    });
}

/**
 * Initialize sticky navigation
 */
function initStickyNav() {
    const navbar = document.querySelector('.navbar');
    if (!navbar) return;
    
    const navbarHeight = navbar.offsetHeight;
    
    function handleScroll() {
        if (window.scrollY > navbarHeight) {
            navbar.classList.add('navbar-sticky');
            document.body.style.paddingTop = navbarHeight + 'px';
        } else {
            navbar.classList.remove('navbar-sticky');
            document.body.style.paddingTop = '0';
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
        // Small delay to show after page load
        setTimeout(() => {
            showNotification(
                `<strong>${festival.name}:</strong> ${festival.message}`,
                'info',
                8000
            );
        }, 2000);
        
        // Add festival banner to home page if we're on the index page
        if (window.location.pathname.includes('index') || window.location.pathname.endsWith('/')) {
            const cta = document.querySelector('.cta-section');
            if (cta) {
                const banner = document.createElement('div');
                banner.className = 'festival-banner alert alert-info text-center animate__animated animate__fadeIn';
                banner.style.margin = '0';
                banner.style.borderRadius = '0';
                banner.style.background = 'linear-gradient(135deg, var(--accent-orange) 0%, var(--accent-blue) 100%)';
                banner.style.color = 'white';
                banner.style.padding = '1rem';
                banner.style.boxShadow = '0 2px 10px rgba(0, 0, 0, 0.1)';
                
                let icon = festival.icon ? `<i class="fas fa-${festival.icon} me-2"></i>` : '';
                banner.innerHTML = `${icon}<strong>${festival.name}:</strong> ${festival.message}`;
                
                // Insert before the CTA section
                cta.parentNode.insertBefore(banner, cta);
            }
        }
    }
}

/**
 * Create floating decorative elements
 * @param {string} selector - CSS selector for the container
 * @param {number} count - Number of elements to create
 */
function createFloatingElements(selector, count = 10) {
    const container = document.querySelector(selector);
    if (!container) return;
    
    // Clear existing floating elements
    const existingElements = container.querySelectorAll('.floating-element');
    existingElements.forEach(el => el.remove());
    
    // Add position relative to container if not already set
    if (window.getComputedStyle(container).position === 'static') {
        container.style.position = 'relative';
    }
    
    // Array of Indian-themed shapes
    const shapes = [
        'circle', // Chakra
        'square',  // Building blocks
        'triangle', // Pyramid/Temple shape
        'diamond' // Diamond shape
    ];
    
    // Array of Indian-themed colors
    const colors = [
        'rgba(255, 153, 51, 0.2)', // Saffron
        'rgba(255, 255, 255, 0.3)', // White
        'rgba(19, 136, 8, 0.2)',    // Green
        'rgba(0, 102, 204, 0.15)'   // Blue
    ];
    
    // Create floating elements
    for (let i = 0; i < count; i++) {
        const element = document.createElement('div');
        element.className = 'floating-element';
        
        // Random shape
        const shape = shapes[Math.floor(Math.random() * shapes.length)];
        
        // Random size between 20 and 60px
        const size = Math.floor(Math.random() * 40) + 20;
        
        // Random position within container
        const left = Math.floor(Math.random() * 100);
        const top = Math.floor(Math.random() * 100);
        
        // Random color
        const color = colors[Math.floor(Math.random() * colors.length)];
        
        // Random animation duration between 10 and 25 seconds
        const duration = Math.floor(Math.random() * 15) + 10;
        
        // Random animation delay
        const delay = Math.floor(Math.random() * 5);
        
        // Apply styles based on shape
        element.style.position = 'absolute';
        element.style.width = `${size}px`;
        element.style.height = `${size}px`;
        element.style.left = `${left}%`;
        element.style.top = `${top}%`;
        element.style.backgroundColor = color;
        element.style.animation = `float ${duration}s ease-in-out ${delay}s infinite`;
        element.style.zIndex = '0';
        element.style.opacity = '0.7';
        
        // Shape-specific styles
        switch (shape) {
            case 'circle':
                element.style.borderRadius = '50%';
                break;
            case 'triangle':
                element.style.width = '0';
                element.style.height = '0';
                element.style.backgroundColor = 'transparent';
                element.style.borderLeft = `${size/2}px solid transparent`;
                element.style.borderRight = `${size/2}px solid transparent`;
                element.style.borderBottom = `${size}px solid ${color}`;
                break;
            case 'diamond':
                element.style.transform = 'rotate(45deg)';
                break;
        }
        
        container.appendChild(element);
    }
    
    // Add keyframes if they don't exist
    if (!document.querySelector('#floating-keyframes')) {
        const style = document.createElement('style');
        style.id = 'floating-keyframes';
        style.textContent = `
            @keyframes float {
                0%, 100% {
                    transform: translateY(0) rotate(0);
                }
                50% {
                    transform: translateY(-20px) rotate(10deg);
                }
            }
        `;
        document.head.appendChild(style);
    }
}

/**
 * Initialize Indianized page elements
 */
function initIndianPageElements() {
    // Add decorative elements to hero section
    createFloatingElements('.hero', 15);
    
    // Update greeting with Hindi text if greeting element exists
    const greetingElement = document.getElementById('greeting');
    if (greetingElement) {
        greetingElement.textContent = getIndianGreeting();
    }
    
    // Add Hindi counter values to stats
    const counterElements = document.querySelectorAll('.counter-value');
    counterElements.forEach(counter => {
        counter.setAttribute('data-hindi', 'true');
    });
    
    // Register festival notification system
    initFestivalNotifications();
}

// Initialize components on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    initStickyNav();
    initCardFlips();
    initCounters();
    
    // Initialize page-specific elements
    if (window.location.pathname.includes('index') || window.location.pathname.endsWith('/')) {
        initIndianPageElements();
    }
    
    // Initialize search functionality if search elements exist
    const searchInput = document.querySelector('.search-input');
    if (searchInput) {
        const itemsSelector = searchInput.getAttribute('data-search-target') || '.searchable-item';
        initSearch('.search-input', itemsSelector);
    }
}); 