/**
 * TracAdemics Form Loading States System
 * Professional loading states for all forms with optimized performance
 */

class FormLoadingManager {
    constructor() {
        this.loadingClass = 'form-loading';
        this.buttonLoadingClass = 'btn-loading';
        this.overlayClass = 'form-loading-overlay';
        this.spinnerClass = 'loading-spinner';
        
        this.init();
    }

    init() {
        // Initialize loading states for all forms on page load
        this.setupFormListeners();
        this.injectLoadingStyles();
        this.setupGlobalLoadingStates();
    }

    setupFormListeners() {
        // Handle regular form submissions
        document.addEventListener('submit', (e) => {
            const form = e.target;
            if (form.tagName === 'FORM' && !form.dataset.noLoading && !this.isLoginForm(form)) {
                this.showFormLoading(form);
            }
        });

        // Handle modal form submissions
        document.addEventListener('click', (e) => {
            const button = e.target.closest('button[type="submit"]');
            if (button && button.form) {
                setTimeout(() => {
                    this.showFormLoading(button.form);
                }, 50);
            }
        });

        // Handle AJAX form submissions
        document.addEventListener('beforeunload', () => {
            this.hideAllLoadingStates();
        });
    }

    showFormLoading(form) {
        if (form.classList.contains(this.loadingClass)) return;

        // Add loading class to form
        form.classList.add(this.loadingClass);

        // Create and show overlay
        const overlay = this.createLoadingOverlay(form);
        form.style.position = 'relative';
        form.appendChild(overlay);

        // Update submit buttons
        this.updateSubmitButtons(form, true);

        // Disable form inputs
        this.toggleFormInputs(form, false);

        // Auto-hide after 30 seconds (safety timeout)
        setTimeout(() => {
            this.hideFormLoading(form);
        }, 30000);
    }

    hideFormLoading(form) {
        form.classList.remove(this.loadingClass);
        
        // Remove overlay
        const overlay = form.querySelector(`.${this.overlayClass}`);
        if (overlay) {
            overlay.remove();
        }

        // Reset submit buttons
        this.updateSubmitButtons(form, false);

        // Re-enable form inputs
        this.toggleFormInputs(form, true);
    }

    hideAllLoadingStates() {
        document.querySelectorAll(`.${this.loadingClass}`).forEach(form => {
            this.hideFormLoading(form);
        });
    }

    isLoginForm(form) {
        // Check if this is a login form that should not be disabled
        const action = form.getAttribute('action');
        const formContent = form.innerHTML;
        
        // Check for login-specific indicators
        return action && (action.includes('/login') || action.includes('login.submit')) ||
               formContent.includes('password') && formContent.includes('email') &&
               (formContent.includes('Login') || formContent.includes('Sign in'));
    }

    createLoadingOverlay(form) {
        const overlay = document.createElement('div');
        overlay.className = this.overlayClass;
        
        const spinner = document.createElement('div');
        spinner.className = this.spinnerClass;
        
        const message = document.createElement('div');
        message.className = 'loading-message';
        message.textContent = 'Processing...';
        
        overlay.appendChild(spinner);
        overlay.appendChild(message);
        
        return overlay;
    }

    updateSubmitButtons(form, loading) {
        const submitButtons = form.querySelectorAll('button[type="submit"], input[type="submit"]');
        
        submitButtons.forEach(button => {
            if (loading) {
                button.classList.add(this.buttonLoadingClass);
                button.disabled = true;
                
                // Store original content
                if (!button.dataset.originalContent) {
                    button.dataset.originalContent = button.innerHTML;
                }
                
                // Update button content
                if (button.tagName === 'BUTTON') {
                    button.innerHTML = `
                        <div class="inline-spinner"></div>
                        <span>Processing...</span>
                    `;
                } else {
                    button.value = 'Processing...';
                }
            } else {
                button.classList.remove(this.buttonLoadingClass);
                button.disabled = false;
                
                // Restore original content
                if (button.dataset.originalContent) {
                    if (button.tagName === 'BUTTON') {
                        button.innerHTML = button.dataset.originalContent;
                    } else {
                        button.value = button.dataset.originalContent;
                    }
                }
            }
        });
    }

    toggleFormInputs(form, enabled) {
        const inputs = form.querySelectorAll('input, select, textarea, button');
        inputs.forEach(input => {
            if (input.type !== 'submit') {
                input.disabled = !enabled;
            }
        });
    }

    // Special method for AJAX forms
    showAjaxLoading(formSelector) {
        const form = document.querySelector(formSelector);
        if (form) {
            this.showFormLoading(form);
        }
    }

    hideAjaxLoading(formSelector) {
        const form = document.querySelector(formSelector);
        if (form) {
            this.hideFormLoading(form);
        }
    }

    // Method for custom loading states
    showCustomLoading(element, message = 'Loading...') {
        element.classList.add('custom-loading');
        
        const overlay = document.createElement('div');
        overlay.className = 'custom-loading-overlay';
        overlay.innerHTML = `
            <div class="${this.spinnerClass}"></div>
            <div class="loading-message">${message}</div>
        `;
        
        element.style.position = 'relative';
        element.appendChild(overlay);
    }

    hideCustomLoading(element) {
        element.classList.remove('custom-loading');
        const overlay = element.querySelector('.custom-loading-overlay');
        if (overlay) {
            overlay.remove();
        }
    }

    injectLoadingStyles() {
        const styles = `
            <style id="form-loading-styles">
                /* Form Loading Overlay */
                .form-loading-overlay {
                    position: absolute;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background: rgba(255, 255, 255, 0.95);
                    backdrop-filter: blur(2px);
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    justify-content: center;
                    z-index: 1000;
                    border-radius: inherit;
                }

                .custom-loading-overlay {
                    position: absolute;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background: rgba(255, 255, 255, 0.9);
                    backdrop-filter: blur(1px);
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    justify-content: center;
                    z-index: 999;
                    border-radius: inherit;
                }

                /* Loading Spinners */
                .loading-spinner {
                    width: 40px;
                    height: 40px;
                    border: 3px solid #e2e8f0;
                    border-top: 3px solid #3b82f6;
                    border-radius: 50%;
                    animation: spin 1s linear infinite;
                    margin-bottom: 12px;
                }

                .inline-spinner {
                    width: 16px;
                    height: 16px;
                    border: 2px solid rgba(255, 255, 255, 0.3);
                    border-top: 2px solid #ffffff;
                    border-radius: 50%;
                    animation: spin 1s linear infinite;
                    display: inline-block;
                    margin-right: 8px;
                    vertical-align: middle;
                }

                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }

                /* Loading Messages */
                .loading-message {
                    color: #4b5563;
                    font-size: 14px;
                    font-weight: 500;
                    text-align: center;
                }

                /* Button Loading States */
                .btn-loading {
                    position: relative;
                    pointer-events: none;
                    opacity: 0.8;
                }

                .btn-loading:hover {
                    transform: none !important;
                    box-shadow: none !important;
                }

                /* Form Loading States */
                .form-loading {
                    pointer-events: none;
                    overflow: hidden;
                }

                .form-loading input,
                .form-loading select,
                .form-loading textarea {
                    opacity: 0.6;
                }

                /* Loading Skeleton for Tables */
                .table-loading {
                    position: relative;
                }

                .table-loading::after {
                    content: '';
                    position: absolute;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.8), transparent);
                    animation: shimmer 1.5s infinite;
                }

                @keyframes shimmer {
                    0% { transform: translateX(-100%); }
                    100% { transform: translateX(100%); }
                }

                /* Page Loading Indicator */
                .page-loading {
                    position: fixed;
                    top: 0;
                    left: 0;
                    right: 0;
                    height: 3px;
                    background: linear-gradient(90deg, #3b82f6, #8b5cf6, #ec4899);
                    background-size: 200% 100%;
                    animation: pageLoad 2s infinite;
                    z-index: 9999;
                }

                @keyframes pageLoad {
                    0% { background-position: 200% 0; }
                    100% { background-position: -200% 0; }
                }

                /* Responsive Loading States */
                @media (max-width: 768px) {
                    .loading-spinner {
                        width: 32px;
                        height: 32px;
                        border-width: 2px;
                    }
                    
                    .loading-message {
                        font-size: 13px;
                    }
                }

                /* Dark mode support */
                @media (prefers-color-scheme: dark) {
                    .form-loading-overlay,
                    .custom-loading-overlay {
                        background: rgba(17, 24, 39, 0.95);
                    }
                    
                    .loading-message {
                        color: #d1d5db;
                    }
                    
                    .loading-spinner {
                        border-color: #374151;
                        border-top-color: #60a5fa;
                    }
                }
            </style>
        `;
        
        if (!document.getElementById('form-loading-styles')) {
            document.head.insertAdjacentHTML('beforeend', styles);
        }
    }

    setupGlobalLoadingStates() {
        // Show page loading indicator for navigation
        if (window.performance && window.performance.navigation.type === 1) {
            this.showPageLoading();
            window.addEventListener('load', () => {
                this.hidePageLoading();
            });
        }

        // Handle browser back/forward navigation
        window.addEventListener('pageshow', (e) => {
            if (e.persisted) {
                this.hideAllLoadingStates();
            }
        });
    }

    showPageLoading() {
        const loader = document.createElement('div');
        loader.className = 'page-loading';
        loader.id = 'page-loader';
        document.body.appendChild(loader);
    }

    hidePageLoading() {
        const loader = document.getElementById('page-loader');
        if (loader) {
            loader.remove();
        }
    }
}

// Enhanced form handlers for specific TracAdemics forms
class TracAdemicsFormHandlers extends FormLoadingManager {
    constructor() {
        super();
        this.setupSpecificHandlers();
    }

    setupSpecificHandlers() {
        // Compliance form handlers
        this.setupComplianceHandlers();
        
        // MIS form handlers
        this.setupMISHandlers();
        
        // Assignment form handlers
        this.setupAssignmentHandlers();
        
        // Profile form handlers
        this.setupProfileHandlers();
    }

    setupComplianceHandlers() {
        // Compliance link submission
        document.addEventListener('submit', (e) => {
            if (e.target.id === 'submitForm') {
                e.target.addEventListener('submit', () => {
                    this.showCustomLoading(
                        document.querySelector('#submitModal .bg-white'),
                        'Submitting compliance document...'
                    );
                });
            }
        });

        // Compliance deletion
        window.deleteLink = (linkId) => {
            if (confirm('Are you sure you want to delete this link?')) {
                const deleteButtons = document.querySelectorAll(`button[onclick="deleteLink(${linkId})"]`);
                deleteButtons.forEach(btn => {
                    btn.disabled = true;
                    btn.innerHTML = '<div class="inline-spinner"></div> Deleting...';
                });
                
                const form = document.getElementById('deleteForm');
                form.action = `/compliance/link/${linkId}`;
                this.showFormLoading(form);
                form.submit();
            }
        };
    }

    setupMISHandlers() {
        // User management forms
        const userModal = document.getElementById('addUserModal');
        if (userModal) {
            const userForm = userModal.querySelector('form');
            if (userForm) {
                userForm.addEventListener('submit', () => {
                    this.showCustomLoading(userModal, 'Creating user account...');
                });
            }
        }

        // Department/Program forms
        document.querySelectorAll('form[action*="/mis/"]').forEach(form => {
            form.addEventListener('submit', () => {
                const action = form.action;
                let message = 'Processing...';
                
                if (action.includes('departments')) {
                    message = 'Managing department...';
                } else if (action.includes('programs')) {
                    message = 'Managing program...';
                } else if (action.includes('users')) {
                    message = 'Managing user...';
                } else if (action.includes('semesters')) {
                    message = 'Managing semester...';
                }
                
                this.showCustomLoading(form.closest('.bg-white') || form, message);
            });
        });
    }

    setupAssignmentHandlers() {
        // Faculty assignment forms
        document.querySelectorAll('form[action*="assignments"]').forEach(form => {
            form.addEventListener('submit', () => {
                this.showCustomLoading(
                    form.closest('.bg-white') || form,
                    'Creating faculty assignment...'
                );
            });
        });
    }

    setupProfileHandlers() {
        // Profile update forms
        document.querySelectorAll('form[action*="profile"]').forEach(form => {
            form.addEventListener('submit', () => {
                this.showCustomLoading(
                    form.closest('.bg-white') || form,
                    'Updating profile information...'
                );
            });
        });
    }

    // Enhanced method for table loading states
    showTableLoading(tableSelector) {
        const table = document.querySelector(tableSelector);
        if (table) {
            table.classList.add('table-loading');
            
            // Disable table controls
            const tableControls = table.closest('.bg-white').querySelectorAll('button, select, input');
            tableControls.forEach(control => {
                control.disabled = true;
            });
        }
    }

    hideTableLoading(tableSelector) {
        const table = document.querySelector(tableSelector);
        if (table) {
            table.classList.remove('table-loading');
            
            // Re-enable table controls
            const tableControls = table.closest('.bg-white').querySelectorAll('button, select, input');
            tableControls.forEach(control => {
                control.disabled = false;
            });
        }
    }
}

// Initialize the form loading system
document.addEventListener('DOMContentLoaded', () => {
    window.formLoadingManager = new TracAdemicsFormHandlers();
    
    // Expose useful methods globally
    window.showFormLoading = (selector) => window.formLoadingManager.showAjaxLoading(selector);
    window.hideFormLoading = (selector) => window.formLoadingManager.hideAjaxLoading(selector);
    window.showTableLoading = (selector) => window.formLoadingManager.showTableLoading(selector);
    window.hideTableLoading = (selector) => window.formLoadingManager.hideTableLoading(selector);
});

// Handle session timeout and errors
window.addEventListener('error', () => {
    if (window.formLoadingManager) {
        window.formLoadingManager.hideAllLoadingStates();
    }
});
