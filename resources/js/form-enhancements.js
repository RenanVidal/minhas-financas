// Form enhancement utilities

// Auto-save functionality for forms
export function initializeAutoSave(formSelector, storageKey) {
    const form = document.querySelector(formSelector);
    if (!form) return;
    
    const inputs = form.querySelectorAll('input, select, textarea');
    
    // Load saved data
    const savedData = localStorage.getItem(storageKey);
    if (savedData) {
        const data = JSON.parse(savedData);
        inputs.forEach(input => {
            if (data[input.name] && input.type !== 'password') {
                input.value = data[input.name];
            }
        });
    }
    
    // Save data on input
    inputs.forEach(input => {
        input.addEventListener('input', () => {
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());
            localStorage.setItem(storageKey, JSON.stringify(data));
        });
    });
    
    // Clear saved data on successful submit
    form.addEventListener('submit', () => {
        localStorage.removeItem(storageKey);
    });
}

// Dynamic form field dependencies
export function initializeFieldDependencies() {
    // Category type affects transaction type
    const categorySelect = document.querySelector('select[name="category_id"]');
    const typeSelect = document.querySelector('select[name="type"]');
    
    if (categorySelect && typeSelect) {
        categorySelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const categoryType = selectedOption.dataset.type;
            
            if (categoryType) {
                typeSelect.value = categoryType;
                typeSelect.dispatchEvent(new Event('change'));
            }
        });
    }
}

// Smart form suggestions
export function initializeSmartSuggestions() {
    const descriptionInput = document.querySelector('input[name="description"]');
    const categorySelect = document.querySelector('select[name="category_id"]');
    
    if (descriptionInput && categorySelect) {
        const suggestions = {
            'supermercado': 'alimentacao',
            'gasolina': 'transporte',
            'uber': 'transporte',
            'netflix': 'entretenimento',
            'salario': 'salario',
            'freelance': 'freelance'
        };
        
        descriptionInput.addEventListener('input', function() {
            const description = this.value.toLowerCase();
            
            for (const [keyword, categoryName] of Object.entries(suggestions)) {
                if (description.includes(keyword)) {
                    const option = categorySelect.querySelector(`option[data-name="${categoryName}"]`);
                    if (option) {
                        categorySelect.value = option.value;
                        categorySelect.dispatchEvent(new Event('change'));
                        break;
                    }
                }
            }
        });
    }
}

// Form progress indicator
export function initializeFormProgress(formSelector) {
    const form = document.querySelector(formSelector);
    if (!form) return;
    
    const requiredFields = form.querySelectorAll('[required]');
    const progressBar = document.createElement('div');
    progressBar.className = 'progress mb-3';
    progressBar.innerHTML = '<div class="progress-bar" role="progressbar"></div>';
    
    form.insertBefore(progressBar, form.firstChild);
    
    const updateProgress = () => {
        const filledFields = Array.from(requiredFields).filter(field => {
            return field.value.trim() !== '' && field.checkValidity();
        });
        
        const progress = (filledFields.length / requiredFields.length) * 100;
        const progressBarElement = progressBar.querySelector('.progress-bar');
        
        progressBarElement.style.width = `${progress}%`;
        progressBarElement.setAttribute('aria-valuenow', progress);
        
        if (progress < 33) {
            progressBarElement.className = 'progress-bar bg-danger';
        } else if (progress < 66) {
            progressBarElement.className = 'progress-bar bg-warning';
        } else if (progress < 100) {
            progressBarElement.className = 'progress-bar bg-info';
        } else {
            progressBarElement.className = 'progress-bar bg-success';
        }
    };
    
    requiredFields.forEach(field => {
        field.addEventListener('input', updateProgress);
        field.addEventListener('change', updateProgress);
    });
    
    updateProgress();
}

// Confirmation dialogs for destructive actions
export function initializeConfirmationDialogs() {
    const deleteButtons = document.querySelectorAll('.btn-danger[data-confirm]');
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            const message = this.dataset.confirm || 'Tem certeza que deseja excluir este item?';
            
            if (!confirm(message)) {
                e.preventDefault();
                return false;
            }
        });
    });
}

// Keyboard shortcuts
export function initializeKeyboardShortcuts() {
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + S to save form
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            const submitButton = document.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.click();
            }
        }
        
        // Escape to cancel/go back
        if (e.key === 'Escape') {
            const cancelButton = document.querySelector('.btn-secondary[onclick*="history.back"]');
            if (cancelButton) {
                cancelButton.click();
            }
        }
    });
}

// Initialize all enhancements
export function initializeAllEnhancements() {
    initializeFieldDependencies();
    initializeSmartSuggestions();
    initializeConfirmationDialogs();
    initializeKeyboardShortcuts();
    
    // Initialize auto-save for transaction forms
    if (window.location.pathname.includes('/transactions/create') || 
        window.location.pathname.includes('/transactions/edit')) {
        initializeAutoSave('form', 'transaction-form-data');
        initializeFormProgress('form');
    }
    
    // Initialize auto-save for category forms
    if (window.location.pathname.includes('/categories/create') || 
        window.location.pathname.includes('/categories/edit')) {
        initializeAutoSave('form', 'category-form-data');
    }
    
    // Initialize auto-save for goal forms
    if (window.location.pathname.includes('/goals/create') || 
        window.location.pathname.includes('/goals/edit')) {
        initializeAutoSave('form', 'goal-form-data');
        initializeFormProgress('form');
    }
}