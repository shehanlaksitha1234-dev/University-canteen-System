/**
 * MAIN JAVASCRIPT FILE - assets/js/main.js
 * Handles frontend interactions: quantity buttons, form validation
 */

// ============================================
// QUANTITY CONTROLS
// ============================================
/**
 * Increase quantity in input field
 */
function increaseQty(button) {
    const input = button.parentElement.querySelector('.qty-field');
    let currentValue = parseInt(input.value) || 1;
    
    if (currentValue < 10) {
        input.value = currentValue + 1;
    }
}

/**
 * Decrease quantity in input field
 */
function decreaseQty(button) {
    const input = button.parentElement.querySelector('.qty-field');
    let currentValue = parseInt(input.value) || 1;
    
    if (currentValue > 1) {
        input.value = currentValue - 1;
    }
}

// ============================================
// FORM VALIDATION
// ============================================
/**
 * Validate email format
 */
function validateEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

/**
 * Validate password strength
 */
function validatePassword(password) {
    // At least 6 characters
    return password.length >= 6;
}

/**
 * Validate signup form
 */
function validateSignupForm() {
    const name = document.getElementById('name');
    const email = document.getElementById('email');
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');
    
    if (!name || !name.value.trim()) {
        alert('Please enter your name');
        return false;
    }
    
    if (!email || !validateEmail(email.value)) {
        alert('Please enter a valid email address');
        return false;
    }
    
    if (!password || !validatePassword(password.value)) {
        alert('Password must be at least 6 characters');
        return false;
    }
    
    if (confirmPassword && password.value !== confirmPassword.value) {
        alert('Passwords do not match');
        return false;
    }
    
    return true;
}

// ============================================
// CART FUNCTIONS
// ============================================
/**
 * Show notification when item added to cart
 */
function showCartNotification(message) {
    const notification = document.createElement('div');
    notification.className = 'notification notification-success';
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background-color: #27AE60;
        color: white;
        padding: 1rem;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        z-index: 1000;
        animation: slideIn 0.3s ease;
    `;
    
    document.body.appendChild(notification);
    
    // Remove after 3 seconds
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 3000);
}

/**
 * Add animation styles
 */
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

// ============================================
// CONFIRMATION DIALOGS
// ============================================
/**
 * Confirm before removing item from cart
 */
function confirmRemoveItem(itemName) {
    return confirm(`Are you sure you want to remove ${itemName} from your cart?`);
}

/**
 * Confirm before deleting menu item (admin)
 */
function confirmDeleteMenuItem(itemName) {
    return confirm(`Are you sure you want to delete "${itemName}"? This action cannot be undone.`);
}

// ============================================
// TABLE ENHANCEMENTS
// ============================================
/**
 * Highlight table rows on hover
 */
document.addEventListener('DOMContentLoaded', function() {
    // Add hover effects to admin tables
    const tables = document.querySelectorAll('.admin-table, .cart-table');
    
    tables.forEach(table => {
        const rows = table.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            row.addEventListener('mouseenter', function() {
                this.style.backgroundColor = 'rgba(139, 0, 0, 0.05)';
            });
            
            row.addEventListener('mouseleave', function() {
                this.style.backgroundColor = '';
            });
        });
    });
    
    // Add click to edit functionality for inventory forms
    const inventoryForms = document.querySelectorAll('.inventory-form');
    inventoryForms.forEach(form => {
        const input = form.querySelector('.qty-input-small');
        
        if (input) {
            input.addEventListener('change', function() {
                // Optional: auto-submit on change
                // form.submit();
            });
        }
    });
});

// ============================================
// UTILITY FUNCTIONS
// ============================================
/**
 * Format currency
 */
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-PK', {
        style: 'currency',
        currency: 'PKR'
    }).format(amount);
}

/**
 * Get current timestamp
 */
function getCurrentTime() {
    const now = new Date();
    return now.toLocaleString('en-PK');
}

/**
 * Check if canteen is open
 */
function isCanteenOpen() {
    const currentHour = new Date().getHours();
    // Canteen open 11 AM to 3 PM
    return currentHour >= 11 && currentHour < 15;
}

// ============================================
// LOCALSTORAGE FOR CART PERSISTENCE (Optional)
// ============================================
/**
 * Save cart to browser storage
 */
function saveCartToLocalStorage(cart) {
    localStorage.setItem('canteen_cart', JSON.stringify(cart));
}

/**
 * Load cart from browser storage
 */
function loadCartFromLocalStorage() {
    const cart = localStorage.getItem('canteen_cart');
    return cart ? JSON.parse(cart) : null;
}

/**
 * Clear cart from browser storage
 */
function clearCartFromLocalStorage() {
    localStorage.removeItem('canteen_cart');
}

// ============================================
// CONSOLE LOGGING FOR DEBUGGING
// ============================================
console.log('Canteen Management System - Frontend Ready');
console.log('Theme: Dark Red/Maroon University');
console.log('Current Time: ' + getCurrentTime());
console.log('Canteen Open: ' + (isCanteenOpen() ? 'Yes' : 'No'));
