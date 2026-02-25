/**
 *
 * Recaptcha V3 Only
 *
 * This script add dynamically a new input hidden at the end of the contact form
 * The captcha is render inside this div by Recaptcha V3
 * If needed you can change the selector by overriding this file in your theme
 */
document.addEventListener('DOMContentLoaded', function () {
    var formFields = document.querySelector('.form-fields');
    if (formFields) {
        var captchaInput = document.createElement('input');
        captchaInput.type = 'hidden';
        captchaInput.id = 'captcha-box';
        captchaInput.value = '';
        captchaInput.name = 'g-recaptcha-response';
        formFields.appendChild(captchaInput);
    }
});
