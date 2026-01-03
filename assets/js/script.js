/**
 * SEKUWA KERNER - Premium JavaScript
 * Modern animations and interactions
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('🍢 Sekuwa Kerner - Premium Experience Loaded');
    
    // Initialize all features
    initNavbar();
    initAnimations();
    initFormValidation();
    initAlertDismiss();
    initQuantityControls();
    initParallax();
    initCardHovers();
    initSmoothScroll();
    initLoadingStates();
});

/**
 * Navbar Scroll Effect
 */
function initNavbar() {
    const navbar = document.querySelector('.navbar');
    if (!navbar) return;
    
    let lastScroll = 0;
    
    window.addEventListener('scroll', () => {
        const currentScroll = window.pageYOffset;
        
        if (currentScroll > 100) {
            navbar.style.background = 'rgba(13, 13, 13, 0.98)';
            navbar.style.boxShadow = '0 4px 30px rgba(0, 0, 0, 0.3)';
        } else {
            navbar.style.background = 'rgba(13, 13, 13, 0.95)';
            navbar.style.boxShadow = 'none';
        }
        
        lastScroll = currentScroll;
    });
    
    // Mobile menu toggle
    const navLinks = document.querySelector('.nav-links');
    if (navLinks && window.innerWidth <= 768) {
        createMobileMenu();
    }
}

function createMobileMenu() {
    const navbar = document.querySelector('.navbar .container');
    const navLinks = document.querySelector('.nav-links');
    
    if (!navbar || !navLinks || document.querySelector('.nav-toggle')) return;
    
    const toggle = document.createElement('button');
    toggle.className = 'nav-toggle';
    toggle.innerHTML = '☰';
    toggle.setAttribute('aria-label', 'Toggle navigation');
    toggle.style.cssText = `
        background: linear-gradient(135deg, #E85D04, #DC2F02);
        border: none;
        color: white;
        font-size: 1.5rem;
        cursor: pointer;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        display: block;
    `;
    
    toggle.addEventListener('click', () => {
        navLinks.classList.toggle('show');
        toggle.innerHTML = navLinks.classList.contains('show') ? '✕' : '☰';
    });
    
    navbar.appendChild(toggle);
    
    // Add styles for mobile nav
    const style = document.createElement('style');
    style.textContent = `
        @media (max-width: 768px) {
            .nav-links {
                display: none !important;
                flex-direction: column;
                width: 100%;
                padding: 1rem 0;
                gap: 0.5rem;
            }
            .nav-links.show {
                display: flex !important;
            }
        }
    `;
    document.head.appendChild(style);
}

/**
 * Scroll Animations (Intersection Observer)
 */
function initAnimations() {
    // Animate elements on scroll
    const animatedElements = document.querySelectorAll(
        '.product-card, .category-card, .feature, .stat-card, .order-card, .dashboard-section'
    );
    
    if (!animatedElements.length) return;
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry, index) => {
            if (entry.isIntersecting) {
                entry.target.style.transitionDelay = `${index * 0.05}s`;
                entry.target.classList.add('animate-in');
                observer.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });
    
    animatedElements.forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(el);
    });
    
    // Add animation class styles
    const style = document.createElement('style');
    style.textContent = `
        .animate-in {
            opacity: 1 !important;
            transform: translateY(0) !important;
        }
    `;
    document.head.appendChild(style);
    
    // Animate hero content
    const heroContent = document.querySelector('.hero-content');
    if (heroContent) {
        heroContent.style.opacity = '0';
        heroContent.style.transform = 'translateY(40px)';
        
        setTimeout(() => {
            heroContent.style.transition = 'opacity 1s ease, transform 1s ease';
            heroContent.style.opacity = '1';
            heroContent.style.transform = 'translateY(0)';
        }, 200);
    }
}

/**
 * Parallax Effect for Hero
 */
function initParallax() {
    const hero = document.querySelector('.hero');
    if (!hero) return;
    
    window.addEventListener('scroll', () => {
        const scrolled = window.pageYOffset;
        const heroContent = hero.querySelector('.hero-content');
        
        if (heroContent && scrolled < window.innerHeight) {
            heroContent.style.transform = `translateY(${scrolled * 0.3}px)`;
            heroContent.style.opacity = 1 - (scrolled / 700);
        }
    });
}

/**
 * Card Hover Effects
 */
function initCardHovers() {
    const cards = document.querySelectorAll('.product-card, .category-card, .stat-card');
    
    cards.forEach(card => {
        card.addEventListener('mouseenter', (e) => {
            const rect = card.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            card.style.setProperty('--mouse-x', `${x}px`);
            card.style.setProperty('--mouse-y', `${y}px`);
        });
    });
    
    // Add glow effect styles
    const style = document.createElement('style');
    style.textContent = `
        .product-card::after,
        .category-card::after,
        .stat-card::after {
            content: '';
            position: absolute;
            top: var(--mouse-y, 50%);
            left: var(--mouse-x, 50%);
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, rgba(255, 186, 8, 0.1) 0%, transparent 70%);
            transform: translate(-50%, -50%);
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .product-card:hover::after,
        .category-card:hover::after,
        .stat-card:hover::after {
            opacity: 1;
        }
    `;
    document.head.appendChild(style);
}

/**
 * Smooth Scroll
 */
function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href === '#') return;
            
            e.preventDefault();
            const target = document.querySelector(href);
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

/**
 * Form Validation with Visual Feedback
 */
function initFormValidation() {
    // Add floating label effect
    document.querySelectorAll('.form-group input, .form-group textarea').forEach(input => {
        input.addEventListener('focus', () => {
            input.parentElement.classList.add('focused');
        });
        
        input.addEventListener('blur', () => {
            if (!input.value) {
                input.parentElement.classList.remove('focused');
            }
        });
        
        // Check initial state
        if (input.value) {
            input.parentElement.classList.add('focused');
        }
    });
    
    // Registration form validation
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                showAlert('error', 'Passwords do not match!');
                shakeElement(document.getElementById('confirm_password'));
                return false;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                showAlert('error', 'Password must be at least 6 characters!');
                shakeElement(document.getElementById('password'));
                return false;
            }
        });
    }
    
    // Checkout form validation
    const checkoutForm = document.getElementById('checkoutForm');
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function(e) {
            const phone = document.getElementById('phone').value;
            const address = document.getElementById('address').value;
            
            if (!/^\d{10}$/.test(phone)) {
                e.preventDefault();
                showAlert('error', 'Please enter a valid 10-digit phone number!');
                shakeElement(document.getElementById('phone'));
                return false;
            }
            
            if (address.trim().length < 10) {
                e.preventDefault();
                showAlert('error', 'Please enter a complete delivery address!');
                shakeElement(document.getElementById('address'));
                return false;
            }
        });
    }
    
    // Product form validation
    const productForm = document.querySelector('.admin-form');
    if (productForm) {
        productForm.addEventListener('submit', function(e) {
            const price = parseFloat(document.getElementById('price')?.value);
            
            if (isNaN(price) || price <= 0) {
                e.preventDefault();
                showAlert('error', 'Please enter a valid price!');
                shakeElement(document.getElementById('price'));
                return false;
            }
        });
    }
}

/**
 * Shake Element Animation
 */
function shakeElement(element) {
    if (!element) return;
    
    element.style.animation = 'shake 0.5s ease';
    element.style.borderColor = '#EF4444';
    
    setTimeout(() => {
        element.style.animation = '';
        element.style.borderColor = '';
    }, 500);
}

// Add shake animation
const shakeStyle = document.createElement('style');
shakeStyle.textContent = `
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
        20%, 40%, 60%, 80% { transform: translateX(5px); }
    }
`;
document.head.appendChild(shakeStyle);

/**
 * Alert Dismiss with Animation
 */
function initAlertDismiss() {
    const alerts = document.querySelectorAll('.alert');
    
    alerts.forEach(alert => {
        // Add close button
        const closeBtn = document.createElement('span');
        closeBtn.innerHTML = '×';
        closeBtn.style.cssText = `
            margin-left: auto;
            cursor: pointer;
            font-size: 1.5rem;
            opacity: 0.7;
            transition: opacity 0.2s;
        `;
        closeBtn.addEventListener('click', () => dismissAlert(alert));
        closeBtn.addEventListener('mouseenter', () => closeBtn.style.opacity = '1');
        closeBtn.addEventListener('mouseleave', () => closeBtn.style.opacity = '0.7');
        alert.appendChild(closeBtn);
        
        // Auto-dismiss after 5 seconds
        setTimeout(() => dismissAlert(alert), 5000);
    });
}

function dismissAlert(alert) {
    alert.style.transition = 'all 0.5s ease';
    alert.style.opacity = '0';
    alert.style.transform = 'translateY(-20px)';
    
    setTimeout(() => alert.remove(), 500);
}

/**
 * Show Alert Message
 */
function showAlert(type, message) {
    // Remove existing alerts
    document.querySelectorAll('.js-alert').forEach(a => a.remove());
    
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} js-alert`;
    
    const icons = {
        success: '✓',
        error: '✕',
        warning: '⚠',
        info: 'ℹ'
    };
    
    alert.innerHTML = `
        <span style="font-size: 1.2rem; margin-right: 8px;">${icons[type] || ''}</span>
        <span>${message}</span>
    `;
    
    alert.style.cssText = `
        position: fixed;
        top: 100px;
        left: 50%;
        transform: translateX(-50%) translateY(-20px);
        z-index: 9999;
        min-width: 300px;
        text-align: center;
        opacity: 0;
        animation: slideDown 0.5s ease forwards;
    `;
    
    document.body.appendChild(alert);
    
    // Add animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideDown {
            to {
                opacity: 1;
                transform: translateX(-50%) translateY(0);
            }
        }
    `;
    document.head.appendChild(style);
    
    // Auto-dismiss
    setTimeout(() => dismissAlert(alert), 4000);
}

/**
 * Quantity Controls with Animation
 */
function initQuantityControls() {
    const qtyInput = document.getElementById('quantity');
    if (!qtyInput) return;
    
    qtyInput.addEventListener('change', function() {
        let value = parseInt(this.value);
        if (isNaN(value) || value < 1) {
            this.value = 1;
        } else if (value > 10) {
            this.value = 10;
        }
        
        // Pulse animation
        this.style.animation = 'pulse-input 0.3s ease';
        setTimeout(() => this.style.animation = '', 300);
    });
}

// Global increment/decrement functions
function incrementQty() {
    const input = document.getElementById('quantity');
    if (input) {
        let value = parseInt(input.value);
        if (value < 10) {
            input.value = value + 1;
            animateValue(input);
        }
    }
}

function decrementQty() {
    const input = document.getElementById('quantity');
    if (input) {
        let value = parseInt(input.value);
        if (value > 1) {
            input.value = value - 1;
            animateValue(input);
        }
    }
}

function animateValue(element) {
    element.style.transform = 'scale(1.1)';
    setTimeout(() => {
        element.style.transform = 'scale(1)';
    }, 150);
}

/**
 * Loading States for Buttons
 */
function initLoadingStates() {
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function() {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn && !submitBtn.classList.contains('no-loading')) {
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = `<span class="spinner"></span> Processing...`;
                submitBtn.disabled = true;
                submitBtn.style.opacity = '0.8';
            }
        });
    });
    
    // Add spinner styles
    const style = document.createElement('style');
    style.textContent = `
        .spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255,255,255,0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin-right: 8px;
            vertical-align: middle;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        @keyframes pulse-input {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
    `;
    document.head.appendChild(style);
}

/**
 * Cart Functions
 */
function addToCart(productId, quantity = 1) {
    const formData = new FormData();
    formData.append('product_id', productId);
    formData.append('quantity', quantity);
    formData.append('add_to_cart', '1');
    
    fetch('ajax_cart.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            updateCartBadge(data.cart_count);
            
            // Animate cart badge
            const badge = document.querySelector('.cart-badge');
            if (badge) {
                badge.style.animation = 'bounce 0.5s ease';
                setTimeout(() => badge.style.animation = '', 500);
            }
        } else {
            showAlert('error', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'Failed to add item to cart');
    });
}

function updateCartBadge(count) {
    const badges = document.querySelectorAll('.cart-badge');
    badges.forEach(badge => {
        if (count > 0) {
            badge.textContent = count;
            badge.style.display = 'block';
        } else {
            badge.style.display = 'none';
        }
    });
}

// Add bounce animation
const bounceStyle = document.createElement('style');
bounceStyle.textContent = `
    @keyframes bounce {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.3); }
    }
`;
document.head.appendChild(bounceStyle);

/**
 * Modal Functions
 */
function showOrderDetails(orderId) {
    const modal = document.getElementById('orderModal');
    const detailsContainer = document.getElementById('orderDetails');
    
    if (!modal || !detailsContainer) return;
    
    detailsContainer.innerHTML = `
        <div style="text-align: center; padding: 2rem;">
            <span class="spinner" style="width: 40px; height: 40px;"></span>
            <p style="margin-top: 1rem; color: rgba(255,255,255,0.7);">Loading order details...</p>
        </div>
    `;
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
    
    fetch('get_order_details.php?id=' + orderId)
        .then(response => response.text())
        .then(html => {
            detailsContainer.innerHTML = html;
        })
        .catch(error => {
            detailsContainer.innerHTML = `
                <div style="text-align: center; padding: 2rem; color: #EF4444;">
                    <p>Error loading order details</p>
                </div>
            `;
            console.error('Error:', error);
        });
}

function closeModal() {
    const modal = document.getElementById('orderModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
}

// Close modal on outside click
window.addEventListener('click', function(event) {
    const modal = document.getElementById('orderModal');
    if (event.target === modal) {
        closeModal();
    }
});

// Close modal with Escape key
window.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeModal();
    }
});

/**
 * Utility Functions
 */
function formatPrice(price) {
    return 'Rs. ' + parseFloat(price).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

function confirmDelete(itemName) {
    return confirm('Are you sure you want to delete ' + itemName + '?');
}

function printElement(elementId) {
    const element = document.getElementById(elementId);
    if (!element) return;
    
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
        <head>
            <title>Print - Sekuwa Kerner</title>
            <link rel="stylesheet" href="assets/css/style.css">
            <style>
                body { 
                    padding: 20px; 
                    background: white !important; 
                    color: black !important; 
                }
                .no-print { display: none; }
            </style>
        </head>
        <body>
            ${element.innerHTML}
        </body>
        </html>
    `);
    printWindow.document.close();
    setTimeout(() => printWindow.print(), 500);
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Live Search
 */
const searchInput = document.querySelector('.search-form input');
if (searchInput) {
    searchInput.addEventListener('keyup', debounce(function(e) {
        if (e.key === 'Enter') {
            this.form.submit();
        }
    }, 500));
    
    // Add search icon animation
    searchInput.addEventListener('focus', () => {
        searchInput.parentElement.style.transform = 'scale(1.02)';
    });
    
    searchInput.addEventListener('blur', () => {
        searchInput.parentElement.style.transform = 'scale(1)';
    });
}

/**
 * Image Preview
 */
const imageInput = document.getElementById('image');
if (imageInput) {
    imageInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                let preview = document.querySelector('.image-preview');
                if (!preview) {
                    preview = document.createElement('div');
                    preview.className = 'image-preview';
                    preview.style.cssText = `
                        margin-top: 1rem;
                        padding: 1rem;
                        background: rgba(255,186,8,0.1);
                        border-radius: 12px;
                        border: 1px dashed rgba(255,186,8,0.3);
                    `;
                    preview.innerHTML = `
                        <p style="color: #FFBA08; margin-bottom: 0.5rem;">Preview:</p>
                        <img style="max-width: 200px; border-radius: 8px; margin-top: 0.5rem;">
                    `;
                    imageInput.parentNode.appendChild(preview);
                }
                preview.querySelector('img').src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    });
}

/**
 * Counter Animation
 */
function animateCounter(element, start, end, duration) {
    let startTimestamp = null;
    const step = (timestamp) => {
        if (!startTimestamp) startTimestamp = timestamp;
        const progress = Math.min((timestamp - startTimestamp) / duration, 1);
        const value = Math.floor(progress * (end - start) + start);
        element.textContent = value.toLocaleString();
        if (progress < 1) {
            window.requestAnimationFrame(step);
        }
    };
    window.requestAnimationFrame(step);
}

// Animate stat numbers on page load
document.querySelectorAll('.stat-info h3').forEach(stat => {
    const value = parseInt(stat.textContent.replace(/[^0-9]/g, ''));
    if (!isNaN(value) && value > 0) {
        stat.textContent = '0';
        setTimeout(() => animateCounter(stat, 0, value, 1500), 500);
    }
});

console.log('🔥 Sekuwa Kerner JavaScript initialized successfully');
