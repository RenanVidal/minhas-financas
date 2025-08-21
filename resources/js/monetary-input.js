// Enhanced monetary input handling

export class MonetaryInput {
    constructor(element) {
        this.element = element;
        this.min = parseFloat(element.dataset.min || '0.01');
        this.step = parseFloat(element.dataset.step || '0.01');
        this.maxDigits = 12; // Maximum digits before decimal
        
        this.init();
    }
    
    init() {
        // Set initial formatting if value exists
        if (this.element.value) {
            this.element.value = this.formatValue(this.parseValue(this.element.value));
        }
        
        // Add event listeners
        this.element.addEventListener('input', (e) => this.handleInput(e));
        this.element.addEventListener('blur', (e) => this.handleBlur(e));
        this.element.addEventListener('focus', (e) => this.handleFocus(e));
        this.element.addEventListener('keydown', (e) => this.handleKeydown(e));
        this.element.addEventListener('paste', (e) => this.handlePaste(e));
    }
    
    handleInput(e) {
        const cursorPosition = e.target.selectionStart;
        const oldValue = e.target.value;
        
        // Remove all non-numeric characters except comma and dot
        let value = e.target.value.replace(/[^\d,.-]/g, '');
        
        // Handle multiple decimal separators
        const commaCount = (value.match(/,/g) || []).length;
        const dotCount = (value.match(/\./g) || []).length;
        
        if (commaCount > 1) {
            value = value.replace(/,/g, '').replace(/(\d+)/, '$1,');
        }
        
        // Convert dots to commas for decimal separator
        if (dotCount > 0 && commaCount === 0) {
            const lastDotIndex = value.lastIndexOf('.');
            if (value.length - lastDotIndex <= 3) {
                value = value.substring(0, lastDotIndex) + ',' + value.substring(lastDotIndex + 1);
            }
        }
        
        // Remove extra dots
        value = value.replace(/\./g, '');
        
        // Limit decimal places to 2
        const commaIndex = value.indexOf(',');
        if (commaIndex !== -1 && value.length - commaIndex > 3) {
            value = value.substring(0, commaIndex + 3);
        }
        
        // Limit total digits
        const numericValue = value.replace(/[^\d]/g, '');
        if (numericValue.length > this.maxDigits + 2) {
            return;
        }
        
        // Format the value
        const formattedValue = this.formatValue(this.parseValue(value));
        
        // Update input value
        e.target.value = formattedValue;
        
        // Restore cursor position
        this.setCursorPosition(e.target, cursorPosition, oldValue, formattedValue);
        
        // Validate
        this.validate();
    }
    
    handleBlur(e) {
        const value = this.parseValue(e.target.value);
        
        if (value === 0 && !this.element.hasAttribute('required')) {
            e.target.value = '';
        } else if (value > 0) {
            e.target.value = this.formatValue(value);
        }
        
        this.validate();
    }
    
    handleFocus(e) {
        // Select all text on focus for easy replacement
        setTimeout(() => {
            e.target.select();
        }, 0);
    }
    
    handleKeydown(e) {
        // Allow: backspace, delete, tab, escape, enter
        if ([8, 9, 27, 13, 46].indexOf(e.keyCode) !== -1 ||
            // Allow: Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X, Ctrl+Z
            (e.keyCode === 65 && e.ctrlKey === true) ||
            (e.keyCode === 67 && e.ctrlKey === true) ||
            (e.keyCode === 86 && e.ctrlKey === true) ||
            (e.keyCode === 88 && e.ctrlKey === true) ||
            (e.keyCode === 90 && e.ctrlKey === true) ||
            // Allow: home, end, left, right, down, up
            (e.keyCode >= 35 && e.keyCode <= 40)) {
            return;
        }
        
        // Ensure that it is a number and stop the keypress
        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && 
            (e.keyCode < 96 || e.keyCode > 105) && 
            e.keyCode !== 188 && e.keyCode !== 190) { // Allow comma and dot
            e.preventDefault();
        }
    }
    
    handlePaste(e) {
        e.preventDefault();
        
        const paste = (e.clipboardData || window.clipboardData).getData('text');
        const numericValue = this.parseValue(paste);
        
        if (numericValue >= 0) {
            e.target.value = this.formatValue(numericValue);
            this.validate();
        }
    }
    
    parseValue(value) {
        if (!value || value === '') return 0;
        
        // Remove currency symbols and spaces
        let cleanValue = value.toString()
            .replace(/[R$\s]/g, '')
            .replace(/\./g, '') // Remove thousand separators
            .replace(',', '.'); // Convert decimal separator
        
        const parsed = parseFloat(cleanValue);
        return isNaN(parsed) ? 0 : parsed;
    }
    
    formatValue(value) {
        if (value === 0 || isNaN(value)) return '';
        
        return value.toLocaleString('pt-BR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }
    
    setCursorPosition(input, position, oldValue, newValue) {
        // Calculate new cursor position based on formatting changes
        const oldLength = oldValue.length;
        const newLength = newValue.length;
        const lengthDiff = newLength - oldLength;
        
        let newPosition = position + lengthDiff;
        
        // Ensure position is within bounds
        newPosition = Math.max(0, Math.min(newPosition, newLength));
        
        setTimeout(() => {
            input.setSelectionRange(newPosition, newPosition);
        }, 0);
    }
    
    validate() {
        const value = this.parseValue(this.element.value);
        const isValid = value >= this.min || (value === 0 && !this.element.hasAttribute('required'));
        
        this.element.classList.remove('is-valid', 'is-invalid');
        
        if (this.element.value !== '') {
            this.element.classList.add(isValid ? 'is-valid' : 'is-invalid');
            
            if (!isValid) {
                const message = value < this.min ? 
                    `O valor deve ser pelo menos R$ ${this.formatValue(this.min)}` : 
                    'Valor inválido';
                this.element.setCustomValidity(message);
            } else {
                this.element.setCustomValidity('');
            }
        }
        
        return isValid;
    }
    
    getValue() {
        return this.parseValue(this.element.value);
    }
    
    setValue(value) {
        this.element.value = this.formatValue(value);
        this.validate();
    }
}

// Initialize all monetary inputs
export function initializeMonetaryInputs() {
    const monetaryInputs = document.querySelectorAll('.monetary-input');
    
    monetaryInputs.forEach(input => {
        if (!input.monetaryInputInstance) {
            input.monetaryInputInstance = new MonetaryInput(input);
        }
    });
}

// Auto-initialize on DOM content loaded
document.addEventListener('DOMContentLoaded', initializeMonetaryInputs);