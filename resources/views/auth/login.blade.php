@extends('layouts.guest')

@section('title','Login')
@section('meta_title','Login - Tracademics')
@section('meta_description','Sign in to access Tracademics. Only @brokenshire.edu.ph emails are allowed.')
@section('meta_robots','noindex,nofollow')
@section('canonical', url()->current())

@section('content')
<div class="login-card">
    <div class="login-left">
        <div class="mb-8">
            <div class="flex items-center gap-2 mb-6">
                <img src="{{ asset('favicon.ico') }}" class="h-12 w-12" alt="Logo">
                <span class="text-3xl font-bold">Tracademics</span>
            </div>
            <p class="text-white text-opacity-90 text-lg mb-4">Academic Compliance Monitoring System</p>
            <p class="text-white text-opacity-75">Streamlining and organizing faculty requirements and document submissions.</p>
        </div>
    </div>
    
    <div class="login-right">
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-2">Welcome Back</h2>
            <p class="text-gray-600">Please sign in to your account</p>
        </div>
        
        <div class="text-center mb-8">
            <div class="inline-flex justify-center">
                <div class="p-3 rounded-full bg-blue-50 mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-500" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('login.submit') }}">
            @csrf
            <div class="input-container">
                <span class="input-icon">
                    <i class="bi bi-person"></i>
                </span>
                <div class="email-input-group">
                    <input 
                        type="text" 
                        id="username-input"
                        value="{{ old('email') ? str_replace('@brokenshire.edu.ph', '', old('email')) : '' }}" 
                        class="login-input email-username-input" 
                        placeholder="Enter your username" 
                        required 
                        autocomplete="username"
                    />
                    <div class="email-domain-display">@brokenshire.edu.ph</div>
                </div>
                <input type="hidden" name="email" id="email-hidden" value="{{ old('email') }}">
            </div>
            
            <div class="input-container">
                <span class="input-icon">
                    <i class="bi bi-lock"></i>
                </span>
                <input 
                    type="password" 
                    name="password" 
                    class="login-input" 
                    placeholder="Password"
                    required 
                />
            </div>
            
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center">
                    <input id="remember" type="checkbox" name="remember" class="h-4 w-4 text-blue-600 border-gray-300 rounded" />
                    <label for="remember" class="ml-2 text-sm text-gray-700">Remember me</label>
                </div>
                <div>
                    <a href="#" class="text-sm text-blue-600 hover:text-blue-800">Forgot Password?</a>
                </div>
            </div>
            
            <button class="login-button" type="submit">
                <i class="bi bi-box-arrow-in-right mr-2"></i>LOGIN
            </button>
            
            @if($errors->any())
                <div class="mt-4 p-3 bg-red-100 text-red-700 rounded-md">
                    <p class="flex items-center"><i class="bi bi-exclamation-triangle mr-2"></i> {{ $errors->first() }}</p>
                </div>
            @endif
            
            <div class="mt-6 text-center text-sm text-gray-500">
                <p>Only emails ending with @brokenshire.edu.ph are allowed.</p>
            </div>
        </form>
    </div>
</div>

<style>
.email-input-group {
    position: relative;
    width: 100%;
    background: white;
    border: 2px solid #d1d5db;
    border-radius: 8px;
    transition: all 0.3s ease;
    overflow: hidden;
}

.email-input-group:focus-within {
    border-color: #10b981;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
}

.email-username-input {
    border: none !important;
    outline: none !important;
    box-shadow: none !important;
    background: transparent !important;
    padding: 12px 15px !important;
    padding-right: 200px !important; /* Make space for domain */
    font-size: 16px;
    width: 100%;
    border-radius: 0 !important;
}

.email-username-input:focus {
    border: none !important;
    outline: none !important;
    box-shadow: none !important;
}

.email-domain-display {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
    font-size: 16px;
    pointer-events: none;
    user-select: none;
    background: white;
    padding: 2px 4px;
    font-weight: 400;
    transition: color 0.3s ease;
}

.email-input-group:focus-within .email-domain-display {
    color: #10b981;
}

/* Error state */
.input-container.error .email-input-group {
    border-color: #ef4444;
}

.input-container.error .email-input-group:focus-within {
    border-color: #ef4444;
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
}

.input-container.error .email-domain-display {
    color: #ef4444;
}

/* Responsive adjustments */
@media (max-width: 640px) {
    .email-username-input {
        padding-right: 180px !important;
    }
    
    .email-domain-display {
        font-size: 14px;
        right: 10px;
    }
}

/* Floating label effect when user starts typing */
.email-input-group.has-content .email-domain-display {
    color: #6b7280;
    font-weight: 500;
}

/* Animation for a smooth user experience */
.email-username-input::placeholder {
    color: #9ca3af;
    transition: opacity 0.3s ease;
}

.email-username-input:focus::placeholder {
    opacity: 0.7;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const usernameInput = document.getElementById('username-input');
    const emailHidden = document.getElementById('email-hidden');
    const emailGroup = usernameInput.closest('.email-input-group');
    const inputContainer = usernameInput.closest('.input-container');
    
    function updateEmailField() {
        const username = usernameInput.value.trim();
        const fullEmail = username ? username + '@brokenshire.edu.ph' : '';
        emailHidden.value = fullEmail;
        
        // Add visual feedback for content
        if (username) {
            emailGroup.classList.add('has-content');
        } else {
            emailGroup.classList.remove('has-content');
        }
    }
    
    function validateUsername(username) {
        // Basic validation for username format
        const usernameRegex = /^[a-zA-Z0-9._-]+$/;
        return usernameRegex.test(username) && username.length >= 2;
    }
    
    function showError(message) {
        inputContainer.classList.add('error');
        
        // Remove existing error message
        const existingError = inputContainer.querySelector('.username-error');
        if (existingError) {
            existingError.remove();
        }
        
        // Add new error message
        const errorDiv = document.createElement('div');
        errorDiv.className = 'username-error';
        errorDiv.style.cssText = 'color: #ef4444; font-size: 14px; margin-top: 5px; padding-left: 40px;';
        errorDiv.textContent = message;
        inputContainer.appendChild(errorDiv);
    }
    
    function clearError() {
        inputContainer.classList.remove('error');
        const existingError = inputContainer.querySelector('.username-error');
        if (existingError) {
            existingError.remove();
        }
    }
    
    // Real-time validation and updates
    usernameInput.addEventListener('input', function() {
        updateEmailField();
        clearError();
        
        const username = usernameInput.value.trim();
        if (username && !validateUsername(username)) {
            showError('Username can only contain letters, numbers, dots, hyphens, and underscores');
        }
    });
    
    // Focus and blur effects
    usernameInput.addEventListener('focus', function() {
        clearError();
    });
    
    // Initialize on page load
    updateEmailField();
    
    // Enhanced form submission with better validation
    const form = usernameInput.closest('form');
    form.addEventListener('submit', function(e) {
        updateEmailField();
        clearError();
        
        const username = usernameInput.value.trim();
        
        // Validate that username is not empty
        if (!username) {
            e.preventDefault();
            showError('Please enter your username');
            usernameInput.focus();
            return false;
        }
        
        // Validate username format
        if (!validateUsername(username)) {
            e.preventDefault();
            showError('Please enter a valid username (letters, numbers, dots, hyphens, underscores only)');
            usernameInput.focus();
            return false;
        }
        
        // Show loading state
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="bi bi-hourglass-split mr-2"></i>Signing in...';
        }
    });
    
    // Add helpful tooltips
    usernameInput.addEventListener('focus', function() {
        if (!usernameInput.value) {
            usernameInput.placeholder = 'e.g., john.doe, mary.smith, or jdoe';
        }
    });
    
    usernameInput.addEventListener('blur', function() {
        if (!usernameInput.value) {
            usernameInput.placeholder = 'Enter your username';
        }
    });
});
</script>
@endsection


