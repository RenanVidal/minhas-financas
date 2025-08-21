import './bootstrap';

// Import Bootstrap JavaScript
import 'bootstrap/dist/js/bootstrap.bundle.min.js';

// Import Chart.js
import Chart from 'chart.js/auto';

// Import form enhancements
import { initializeAllEnhancements } from './form-enhancements.js';

// Import advanced validation
import { initializeAllAdvancedValidations } from './advanced-validation.js';

// Import monetary input handling
import { initializeMonetaryInputs } from './monetary-input.js';

// Make Chart.js available globally
window.Chart = Chart;

// Form validation and enhancements
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all form validations
    initializeFormValidation();
    
    // Initialize monetary masks (legacy - will be replaced by monetary-input.js)
    initializeMonetaryMasks();
    
    // Initialize date validations
    initializeDateValidations();
    
    // Initialize real-time feedback
    initializeRealTimeFeedback();
    
    // Initialize form submission handling
    initializeFormSubmission();
    
    // Initialize form enhancements
    initializeAllEnhancements();
    
    // Initialize advanced validations
    initializeAllAdvancedValidations();
    
    // Initialize enhanced monetary inputs
    initializeMonetaryInputs();
});

// Form validation initialization
function initializeFormValidation() {
    const forms = document.querySelectorAll('.needs-validation');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
                
                // Focus on first invalid field
                const firstInvalid = form.querySelector(':invalid');
                if (firstInvalid) {
                    firstInvalid.focus();
                    firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
            
            form.classList.add('was-validated');
        });
    });
}

// Monetary mask initialization
function initializeMonetaryMasks() {
    const monetaryInputs = document.querySelectorAll('.monetary-input');
    
    monetaryInputs.forEach(input => {
        // Set initial formatting if value exists
        if (input.value) {
            input.value = formatCurrency(parseFloat(input.value.replace(',', '.')));
        }
        
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            
            if (value === '') {
                e.target.value = '';
                return;
            }
            
            // Convert to decimal
            value = (parseInt(value) / 100).toFixed(2);
            
            // Format as currency
            e.target.value = formatCurrency(parseFloat(value));
            
            // Trigger validation
            validateMonetaryInput(e.target);
        });
        
        input.addEventListener('blur', function(e) {
            if (e.target.value === '') {
                e.target.value = '';
            }
        });
    });
}

// Date validation initialization
function initializeDateValidations() {
    const dateInputs = document.querySelectorAll('input[type="date"]');
    
    dateInputs.forEach(input => {
        input.addEventListener('change', function(e) {
            validateDateInput(e.target);
        });
    });
}

// Real-time feedback initialization
function initializeRealTimeFeedback() {
    const inputs = document.querySelectorAll('input, select, textarea');
    
    inputs.forEach(input => {
        input.addEventListener('blur', function(e) {
            validateField(e.target);
        });
        
        input.addEventListener('input', function(e) {
            // Clear previous validation state on input
            e.target.classList.remove('is-valid', 'is-invalid');
            
            // Real-time validation for specific fields
            if (e.target.type === 'email') {
                validateEmailField(e.target);
            } else if (e.target.name === 'password_confirmation') {
                validatePasswordConfirmation(e.target);
            }
        });
    });
}

// Form submission handling
function initializeFormSubmission() {
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const submitBtn = form.querySelector('button[type="submit"]');
            
            if (submitBtn && form.checkValidity()) {
                // Show loading state
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Processando...';
                submitBtn.disabled = true;
                
                // Re-enable after 5 seconds as fallback
                setTimeout(() => {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }, 5000);
            }
        });
    });
}

// Utility functions
function formatCurrency(value) {
    return value.toLocaleString('pt-BR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

function validateField(field) {
    const isValid = field.checkValidity();
    
    field.classList.remove('is-valid', 'is-invalid');
    field.classList.add(isValid ? 'is-valid' : 'is-invalid');
    
    return isValid;
}

function validateMonetaryInput(input) {
    const value = parseFloat(input.value.replace(/\./g, '').replace(',', '.'));
    const isValid = !isNaN(value) && value > 0;
    
    input.classList.remove('is-valid', 'is-invalid');
    
    if (input.value !== '') {
        input.classList.add(isValid ? 'is-valid' : 'is-invalid');
        
        // Update custom validation message
        if (!isValid) {
            input.setCustomValidity('O valor deve ser maior que zero');
        } else {
            input.setCustomValidity('');
        }
    }
    
    return isValid;
}

function validateDateInput(input) {
    const inputDate = new Date(input.value);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    
    let isValid = true;
    let message = '';
    
    // Check if it's a goal deadline (should be future)
    if (input.name === 'deadline') {
        isValid = inputDate > today;
        message = isValid ? '' : 'A data deve ser futura';
    }
    
    input.classList.remove('is-valid', 'is-invalid');
    
    if (input.value !== '') {
        input.classList.add(isValid ? 'is-valid' : 'is-invalid');
        input.setCustomValidity(message);
    }
    
    return isValid;
}

function validateEmailField(input) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const isValid = emailRegex.test(input.value);
    
    input.classList.remove('is-valid', 'is-invalid');
    
    if (input.value !== '') {
        input.classList.add(isValid ? 'is-valid' : 'is-invalid');
    }
    
    return isValid;
}

function validatePasswordConfirmation(input) {
    const password = document.querySelector('input[name="password"]');
    const isValid = password && input.value === password.value;
    
    input.classList.remove('is-valid', 'is-invalid');
    
    if (input.value !== '') {
        input.classList.add(isValid ? 'is-valid' : 'is-invalid');
        input.setCustomValidity(isValid ? '' : 'As senhas não coincidem');
    }
    
    return isValid;
}

// Export functions for global use
window.formatCurrency = formatCurrency;
window.validateField = validateField;
