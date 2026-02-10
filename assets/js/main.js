/**
 * Figura Site - Main JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    // Image preview for file uploads
    initImagePreview();

    // Gallery functionality
    initGallery();

    // Confirm delete
    initDeleteConfirm();

    // Registration form validation
    initRegisterValidation();
});

/**
 * Image preview for file inputs
 */
function initImagePreview() {
    const imageInputs = document.querySelectorAll('input[type="file"][data-preview]');

    imageInputs.forEach(input => {
        input.addEventListener('change', function() {
            const previewId = this.dataset.preview;
            const preview = document.getElementById(previewId);

            if (preview && this.files && this.files[0]) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };

                reader.readAsDataURL(this.files[0]);
            }
        });
    });
}

/**
 * Gallery thumbnail click
 */
function initGallery() {
    const thumbs = document.querySelectorAll('.gallery-thumb');
    const mainImage = document.getElementById('gallery-main');

    if (mainImage) {
        thumbs.forEach(thumb => {
            thumb.addEventListener('click', function() {
                mainImage.src = this.dataset.full || this.src;

                // Update active state
                thumbs.forEach(t => t.classList.remove('border-primary', 'border-2'));
                this.classList.add('border-primary', 'border-2');
            });
        });
    }
}

/**
 * Confirm delete actions
 */
function initDeleteConfirm() {
    const deleteButtons = document.querySelectorAll('[data-confirm]');

    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            const message = this.dataset.confirm || 'Вы уверены, что хотите удалить?';

            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });
}

/**
 * Form validation helper
 */
function validateForm(form) {
    let isValid = true;
    const required = form.querySelectorAll('[required]');

    required.forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('is-invalid');
            isValid = false;
        } else {
            field.classList.remove('is-invalid');
        }
    });

    return isValid;
}

/**
 * Registration form password match validation
 */
function initRegisterValidation() {
    const form = document.getElementById('register-form');
    if (!form) return;

    form.addEventListener('submit', function(e) {
        const password = form.querySelector('#password');
        const confirm = form.querySelector('#password_confirm');

        if (password && confirm && password.value !== confirm.value) {
            e.preventDefault();
            confirm.classList.add('is-invalid');

            let feedback = confirm.nextElementSibling;
            if (!feedback || !feedback.classList.contains('invalid-feedback')) {
                feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                confirm.parentNode.appendChild(feedback);
            }
            feedback.textContent = 'Пароли не совпадают';
        }
    });
}
