
document.addEventListener('DOMContentLoaded', function() {
    initOTPInputs();
    initPasswordToggle();
    initFormValidation();
    initResendTimer();
});

function initOTPInputs() {
    const inputs = document.querySelectorAll('.otp-input');
    if (inputs.length === 0) return;

    inputs.forEach((input, i) => {
        input.addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '').slice(0, 1);
            if (this.value && i < inputs.length - 1) {
                inputs[i + 1].focus();
            }
            updateHiddenCode();
        });

        input.addEventListener('keydown', function(e) {
            if (e.key === 'Backspace' && !this.value && i > 0) {
                inputs[i - 1].focus();
                inputs[i - 1].value = '';
            }
        });

        input.addEventListener('paste', function(e) {
            e.preventDefault();
            const paste = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '');
            paste.split('').slice(0, inputs.length).forEach((char, j) => {
                inputs[j].value = char;
            });
            if (paste.length >= inputs.length) inputs[inputs.length - 1].focus();
            updateHiddenCode();
        });
    });

    function updateHiddenCode() {
        const hidden = document.getElementById('verification_code');
        if (hidden) {
            hidden.value = Array.from(inputs).map(i => i.value).join('');
        }
    }
}

function initPasswordToggle() {
    document.querySelectorAll('.toggle-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const input = this.parentElement.querySelector('input');
            if (input.type === 'password') {
                input.type = 'text';
                this.textContent = '🙈';
            } else {
                input.type = 'password';
                this.textContent = '👁️';
            }
        });
    });
}

function initFormValidation() {
    const forms = document.querySelectorAll('.auth-form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            let valid = true;
            
            this.querySelectorAll('[required]').forEach(field => {
                if (!field.value.trim()) {
                    field.style.borderColor = '#E63946';
                    valid = false;
                } else {
                    field.style.borderColor = '';
                }
            });

            const phone = this.querySelector('input[name="phone"]');
            if (phone && phone.value) {
                if (!/^\d{10}$/.test(phone.value)) {
                    phone.style.borderColor = '#E63946';
                    valid = false;
                    showToast('Phone must be 10 digits', 'error');
                } else if (/^(\d)\1{9}$/.test(phone.value)) {
                    phone.style.borderColor = '#E63946';
                    valid = false;
                    showToast('Phone number cannot have all same digits', 'error');
                }
            }

            const password = this.querySelector('input[name="password"]');
            const confirm = this.querySelector('input[name="confirm_password"]');
            if (password && password.value) {
                if (password.value.length < 8) {
                    password.style.borderColor = '#E63946';
                    valid = false;
                    showToast('Password must be at least 8 characters', 'error');
                } else if (!/[A-Za-z]/.test(password.value) || !/\d/.test(password.value)) {
                    password.style.borderColor = '#E63946';
                    valid = false;
                    showToast('Password must be alphanumeric (contain letters and numbers)', 'error');
                } else if (!/[^A-Za-z0-9]/.test(password.value)) {
                    password.style.borderColor = '#E63946';
                    valid = false;
                    showToast('Password must contain a special character', 'error');
                }
            }

            if (password && confirm && password.value !== confirm.value) {
                confirm.style.borderColor = '#E63946';
                valid = false;
                showToast('Passwords do not match', 'error');
            }

            if (!valid) e.preventDefault();
        });
    });
}

function initResendTimer() {
    const resendBtn = document.querySelector('.resend-btn');
    if (!resendBtn) return;

    let countdown = 60;
    const timer = document.querySelector('.resend-timer');
    
    function startTimer() {
        resendBtn.disabled = true;
        const interval = setInterval(() => {
            countdown--;
            if (timer) timer.textContent = `(${countdown}s)`;
            if (countdown <= 0) {
                clearInterval(interval);
                resendBtn.disabled = false;
                if (timer) timer.textContent = '';
                countdown = 60;
            }
        }, 1000);
    }

    startTimer();
    
    resendBtn.addEventListener('click', async function() {
        const baseUrl = document.querySelector('meta[name="base-url"]')?.content || '';
        const email = document.getElementById('verify-email')?.value;
        
        try {
            const res = await fetch(baseUrl + '/resend-code', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `email=${encodeURIComponent(email)}&_csrf_token=${getCSRF()}`
            });
            const data = await res.json();
            showToast(data.message, data.success ? 'success' : 'error');
            if (data.success) startTimer();
        } catch(e) {
            showToast('Failed to resend code', 'error');
        }
    });
}

function getCSRF() {
    return document.querySelector('meta[name="csrf-token"]')?.content || 
           document.querySelector('input[name="_csrf_token"]')?.value || '';
}

function showToast(msg, type) {
    if (typeof window.showToast === 'function' && window.showToast !== showToast) {
        window.showToast(msg, type);
        return;
    }
    let c = document.querySelector('.toast-container');
    if (!c) { c = document.createElement('div'); c.className = 'toast-container'; document.body.appendChild(c); }
    const t = document.createElement('div');
    t.className = `toast ${type}`;
    t.innerHTML = `<span>${msg}</span><button class="toast-close" onclick="this.parentElement.remove()">×</button>`;
    c.appendChild(t);
    setTimeout(() => t.remove(), 4000);
}
