<?php require_once SRC_PATH . '/views/layouts/header.php'; ?>

<div class="auth-container">
    <div class="auth-card">
        
        <?php if (isset($_SESSION['flash_message'])): ?>
            <div class="alert alert-<?= $_SESSION['flash_type'] ?? 'info' ?>">
                <?= $_SESSION['flash_message'] ?>
                <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
            </div>
        <?php endif; ?>
        
        <form id="loginForm" action="<?= APP_URL ?>/auth/processLogin" method="post" class="auth-form">
            <div class="form-group">
                <label for="email" class="form-label">Email Address</label>
                <div class="input-icon">
                    <i class="fas fa-envelope"></i>
                    <input type="email" id="email" name="email" class="form-control" required placeholder="Enter your email">
                </div>
            </div>
            
            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <div class="input-icon">
                    <i class="fas fa-lock"></i>
                    <input type="password" id="password" name="password" class="form-control" required placeholder="Enter your password">
                    <button type="button" class="password-toggle" aria-label="Toggle password visibility">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            
            <div class="form-options">
                <div class="form-check">
                    <input type="checkbox" id="remember" name="remember" class="form-check-input">
                    <label for="remember" class="form-check-label">Remember me</label>
                </div>
                <a href="<?= APP_URL ?>/auth/forgot-password" class="forgot-link">Forgot password?</a>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary btn-block">Sign In</button>
            </div>
        </form>

        <div class="auth-divider">
            <span>OR</span>
        </div>

        <!-- Google Sign-In Button -->
        <div class="google-signin-container">
            <div id="g_id_onload"
                data-client_id="<?= GOOGLE_CLIENT_ID ?>"
                data-context="signin"
                data-ux_mode="popup"
                data-callback="handleCredentialResponse"
                data-auto_prompt="false">
            </div>
            <div class="g_id_signin"
                data-type="standard"
                data-size="large"
                data-theme="outline"
                data-text="sign_in_with"
                data-shape="rectangular"
                data-logo_alignment="left"
                data-width="100%">
            </div>
        </div>
        
        <div class="auth-footer">
            <div class="create-account-section">
                <span>New to HomeSwift?</span>
                <a href="<?= APP_URL ?>/auth/register" class="btn btn-link">Create Account</a>
            </div>
        </div>
    </div>
</div>

<!-- Add SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://accounts.google.com/gsi/client" async defer></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Password toggle visibility
    const toggleButtons = document.querySelectorAll('.password-toggle');
    toggleButtons.forEach(button => {
        button.addEventListener('click', function() {
            const input = this.previousElementSibling;
            const icon = this.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });

    // Handle form submission
    const loginForm = document.getElementById('loginForm');
    loginForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        try {
            const response = await fetch(this.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new FormData(this),
                credentials: 'same-origin'
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new TypeError("Oops, we haven't got JSON!");
            }
            
            const data = await response.json();
            
            if (data.success) {
                await Swal.fire({
                    icon: 'success',
                    title: 'Welcome back!',
                    text: data.message,
                    showConfirmButton: false,
                    timer: 1500
                });
                window.location.href = data.redirect;
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Login Failed',
                    text: data.message || 'Invalid email or password'
                });
            }
        } catch (error) {
            console.error('Login error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Login Error',
                text: 'Unable to process your login request. Please try again.'
            });
        }
    });
});

// Handle Google Sign-In
function handleCredentialResponse(response) {
    fetch('<?= APP_URL ?>/auth/google-handler', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: 'credential=' + encodeURIComponent(response.credential)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Redirect immediately to dashboard
            window.location.href = data.redirect;
        } else {
            throw new Error(data.message || 'Failed to sign in with Google');
        }
    })
    .catch(error => {
        console.error('Google Sign-In error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Login Error',
            text: error.message || 'Unable to process Google Sign-In. Please try again.'
        });
    });
}
</script>

<style>
.auth-container {
    min-height: calc(100vh - 80px);
    padding: 2rem;
    background-color: #f8f9fa;
}

.auth-card {
    background: #fff;
    border-radius: 15px;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    padding: 1.5rem;
    width: 100%;
    max-width: 400px; /* Reduced width for vertical layout */
    margin: 0 auto;
}

.auth-form {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.form-group {
    margin-bottom: 1rem;
}

.input-icon {
    position: relative;
    display: flex;
    align-items: center;
}

.input-icon .form-control {
    padding-right: 2.5rem; /* Make space for the eye icon */
}

.password-toggle {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #6c757d;
    cursor: pointer;
    padding: 0;
    width: 24px; /* Fixed width */
    height: 24px; /* Fixed height */
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 2;
}

/* Adjust form elements to span full width */
.form-options, 
.form-group:last-child, 
.auth-divider,
.google-signin-container,
.auth-footer {
    grid-column: 1 / -1;
}

@media (max-width: 768px) {
    .auth-form {
        grid-template-columns: 1fr;
    }
    
    .auth-card {
        max-width: 400px;
    }
}

.auth-header {
    text-align: center;
    margin-bottom: 1.5rem;
}

.auth-logo {
    height: 50px;
    margin-bottom: 1.5rem;
}

.auth-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: #333;
    margin-bottom: 0.25rem;
}

.auth-subtitle {
    font-size: 0.9rem;
    color: #6c757d;
    margin-bottom: 0;
}

.auth-form .form-group {
    margin-bottom: 1rem;
}

.form-label {
    font-weight: 500;
    margin-bottom: 0.5rem;
    color: #333;
}

.input-icon {
    position: relative;
}

.input-icon i {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: #6c757d;
}

.input-icon .form-control {
    padding-left: 2.75rem;
    border-radius: 8px;
    border: 1px solid #e3e6f0;
    padding: 0.5rem 1rem 0.5rem 2.5rem;
}

.input-icon .form-control:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
}

.password-toggle {
    position: absolute;
    right: 1rem;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #6c757d;
    cursor: pointer;
    padding: 0;
}

.form-options {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.form-check-label {
    color: #6c757d;
    cursor: pointer;
}

.forgot-link {
    color: var(--primary-color);
    text-decoration: none;
    font-weight: 500;
}

.forgot-link:hover {
    text-decoration: underline;
}

.btn-block {
    width: 100%;
    padding: 0.75rem;
    font-weight: 500;
    font-size: 1rem;
}

.auth-divider {
    text-align: center;
    margin: 1.5rem 0;
    position: relative;
}

.auth-divider::before,
.auth-divider::after {
    content: '';
    position: absolute;
    top: 50%;
    width: calc(50% - 30px);
    height: 1px;
    background-color: #e3e6f0;
}

.auth-divider::before {
    left: 0;
}

.auth-divider::after {
    right: 0;
}

.auth-divider span {
    background-color: #fff;
    padding: 0 1rem;
    color: #6c757d;
    font-size: 0.875rem;
}

.google-signin-container {
    margin-bottom: 1.5rem;
    display: flex;
    justify-content: center;
}

.auth-footer {
    text-align: center;
    color: #6c757d;
    margin-top: 1.5rem;
}

.auth-footer a {
    color: var(--primary-color);
    text-decoration: none;
    font-weight: 500;
}

.auth-footer a:hover {
    text-decoration: underline;
}

.create-account-section {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding-top: 0.5rem;
}

.btn-link {
    padding: 0;
    font-weight: 500;
    text-decoration: none;
    color: var(--primary-color);
}

.btn-link:hover {
    text-decoration: underline;
}

@media (max-width: 576px) {
    .auth-container {
        padding: 0.5rem;
    }
    
    .auth-card {
        padding: 1rem;
    }
    
    .form-options {
        flex-direction: column;
        gap: 1rem;
        align-items: flex-start;
    }
}
</style>

