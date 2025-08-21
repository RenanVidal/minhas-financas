// Advanced validation utilities

// Custom validation rules
export const validationRules = {
    // Brazilian CPF validation
    cpf: (value) => {
        const cpf = value.replace(/\D/g, '');
        if (cpf.length !== 11 || /^(\d)\1{10}$/.test(cpf)) return false;
        
        let sum = 0;
        for (let i = 0; i < 9; i++) {
            sum += parseInt(cpf.charAt(i)) * (10 - i);
        }
        let remainder = (sum * 10) % 11;
        if (remainder === 10 || remainder === 11) remainder = 0;
        if (remainder !== parseInt(cpf.charAt(9))) return false;
        
        sum = 0;
        for (let i = 0; i < 10; i++) {
            sum += parseInt(cpf.charAt(i)) * (11 - i);
        }
        remainder = (sum * 10) % 11;
        if (remainder === 10 || remainder === 11) remainder = 0;
        return remainder === parseInt(cpf.charAt(10));
    },
    
    // Strong password validation
    strongPassword: (value) => {
        const minLength = 8;
        const hasUpperCase = /[A-Z]/.test(value);
        const hasLowerCase = /[a-z]/.test(value);
        const hasNumbers = /\d/.test(value);
        const hasSpecialChar = /[!@#$%^&*(),.?":{}|<>]/.test(value);
        
        return value.length >= minLength && hasUpperCase && hasLowerCase && hasNumbers && hasSpecialChar;
    },
    
    // Future date validation
    futureDate: (value) => {
        const inputDate = new Date(value);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        return inputDate > today;
    },
    
    // Past or present date validation
    pastOrPresentDate: (value) => {
        const inputDate = new Date(value);
        const today = new Date();
        today.setHours(23, 59, 59, 999);
        return inputDate <= today;
    },
    
    // Positive number validation
    positiveNumber: (value) => {
        const num = parseFloat(value.replace(/\./g, '').replace(',', '.'));
        return !isNaN(num) && num > 0;
    },
    
    // Non-negative number validation
    nonNegativeNumber: (value) => {
        const num = parseFloat(value.replace(/\./g, '').replace(',', '.'));
        return !isNaN(num) && num >= 0;
    }
};

// Real-time validation with debouncing
export function initializeAdvancedValidation() {
    const inputs = document.querySelectorAll('[data-validation]');
    
    inputs.forEach(input => {
        let timeout;
        
        input.addEventListener('input', function() {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                validateWithCustomRules(this);
            }, 300); // 300ms debounce
        });
        
        input.addEventListener('blur', function() {
            clearTimeout(timeout);
            validateWithCustomRules(this);
        });
    });
}

// Validate field with custom rules
function validateWithCustomRules(field) {
    const rules = field.dataset.validation.split('|');
    let isValid = true;
    let errorMessage = '';
    
    for (const rule of rules) {
        const [ruleName, ruleParam] = rule.split(':');
        
        if (field.value === '' && !field.hasAttribute('required')) {
            continue; // Skip validation for empty optional fields
        }
        
        switch (ruleName) {
            case 'cpf':
                if (!validationRules.cpf(field.value)) {
                    isValid = false;
                    errorMessage = 'CPF inválido';
                }
                break;
                
            case 'strong-password':
                if (!validationRules.strongPassword(field.value)) {
                    isValid = false;
                    errorMessage = 'A senha deve ter pelo menos 8 caracteres, incluindo maiúscula, minúscula, número e caractere especial';
                }
                break;
                
            case 'future-date':
                if (!validationRules.futureDate(field.value)) {
                    isValid = false;
                    errorMessage = 'A data deve ser futura';
                }
                break;
                
            case 'past-or-present-date':
                if (!validationRules.pastOrPresentDate(field.value)) {
                    isValid = false;
                    errorMessage = 'A data não pode ser futura';
                }
                break;
                
            case 'positive-number':
                if (!validationRules.positiveNumber(field.value)) {
                    isValid = false;
                    errorMessage = 'O valor deve ser maior que zero';
                }
                break;
                
            case 'non-negative-number':
                if (!validationRules.nonNegativeNumber(field.value)) {
                    isValid = false;
                    errorMessage = 'O valor não pode ser negativo';
                }
                break;
                
            case 'min-length':
                if (field.value.length < parseInt(ruleParam)) {
                    isValid = false;
                    errorMessage = `Deve ter pelo menos ${ruleParam} caracteres`;
                }
                break;
                
            case 'max-length':
                if (field.value.length > parseInt(ruleParam)) {
                    isValid = false;
                    errorMessage = `Deve ter no máximo ${ruleParam} caracteres`;
                }
                break;
        }
        
        if (!isValid) break;
    }
    
    // Update field validation state
    field.classList.remove('is-valid', 'is-invalid');
    
    if (field.value !== '') {
        field.classList.add(isValid ? 'is-valid' : 'is-invalid');
        field.setCustomValidity(isValid ? '' : errorMessage);
        
        // Update custom error message display
        updateErrorMessage(field, errorMessage);
    }
    
    return isValid;
}

// Update error message display
function updateErrorMessage(field, message) {
    const errorElement = field.parentNode.querySelector('.invalid-feedback');
    
    if (errorElement && message) {
        errorElement.textContent = message;
    }
}

// Password strength indicator
export function initializePasswordStrength() {
    const passwordInputs = document.querySelectorAll('input[type="password"][data-strength]');
    
    passwordInputs.forEach(input => {
        const strengthIndicator = createStrengthIndicator();
        input.parentNode.appendChild(strengthIndicator);
        
        input.addEventListener('input', function() {
            updatePasswordStrength(this, strengthIndicator);
        });
    });
}

function createStrengthIndicator() {
    const container = document.createElement('div');
    container.className = 'password-strength mt-2';
    container.innerHTML = `
        <div class="progress" style="height: 5px;">
            <div class="progress-bar" role="progressbar" style="width: 0%"></div>
        </div>
        <small class="text-muted strength-text">Digite uma senha</small>
    `;
    return container;
}

function updatePasswordStrength(input, indicator) {
    const password = input.value;
    const progressBar = indicator.querySelector('.progress-bar');
    const strengthText = indicator.querySelector('.strength-text');
    
    let strength = 0;
    let text = 'Muito fraca';
    let colorClass = 'bg-danger';
    
    if (password.length >= 8) strength += 20;
    if (/[a-z]/.test(password)) strength += 20;
    if (/[A-Z]/.test(password)) strength += 20;
    if (/\d/.test(password)) strength += 20;
    if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) strength += 20;
    
    if (strength >= 80) {
        text = 'Muito forte';
        colorClass = 'bg-success';
    } else if (strength >= 60) {
        text = 'Forte';
        colorClass = 'bg-info';
    } else if (strength >= 40) {
        text = 'Média';
        colorClass = 'bg-warning';
    } else if (strength >= 20) {
        text = 'Fraca';
        colorClass = 'bg-danger';
    }
    
    progressBar.style.width = `${strength}%`;
    progressBar.className = `progress-bar ${colorClass}`;
    strengthText.textContent = text;
}

// Form field masking
export function initializeFieldMasks() {
    // CPF mask
    const cpfInputs = document.querySelectorAll('input[data-mask="cpf"]');
    cpfInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            e.target.value = value;
        });
    });
    
    // Phone mask
    const phoneInputs = document.querySelectorAll('input[data-mask="phone"]');
    phoneInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length <= 10) {
                value = value.replace(/(\d{2})(\d)/, '($1) $2');
                value = value.replace(/(\d{4})(\d)/, '$1-$2');
            } else {
                value = value.replace(/(\d{2})(\d)/, '($1) $2');
                value = value.replace(/(\d{5})(\d)/, '$1-$2');
            }
            e.target.value = value;
        });
    });
    
    // CEP mask
    const cepInputs = document.querySelectorAll('input[data-mask="cep"]');
    cepInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            value = value.replace(/(\d{5})(\d)/, '$1-$2');
            e.target.value = value;
        });
    });
}

// Async validation (for checking unique values)
export function initializeAsyncValidation() {
    const asyncFields = document.querySelectorAll('[data-async-validation]');
    
    asyncFields.forEach(field => {
        let timeout;
        
        field.addEventListener('input', function() {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                performAsyncValidation(this);
            }, 500);
        });
    });
}

async function performAsyncValidation(field) {
    const validationType = field.dataset.asyncValidation;
    const value = field.value.trim();
    
    if (value === '') return;
    
    // Show loading state
    field.classList.add('loading');
    
    try {
        const response = await fetch(`/api/validate/${validationType}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ value, field: field.name })
        });
        
        const result = await response.json();
        
        field.classList.remove('is-valid', 'is-invalid', 'loading');
        field.classList.add(result.valid ? 'is-valid' : 'is-invalid');
        
        if (!result.valid) {
            field.setCustomValidity(result.message);
            updateErrorMessage(field, result.message);
        } else {
            field.setCustomValidity('');
        }
    } catch (error) {
        console.error('Async validation error:', error);
        field.classList.remove('loading');
    }
}

// Initialize all advanced validations
export function initializeAllAdvancedValidations() {
    initializeAdvancedValidation();
    initializePasswordStrength();
    initializeFieldMasks();
    initializeAsyncValidation();
}