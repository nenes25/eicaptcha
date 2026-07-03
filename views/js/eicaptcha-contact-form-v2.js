/**
 *
 * Recaptcha V2 Only
 *
 * This script add dynamically a new div at the end of the contact form
 * The captcha is render inside this div by Recaptcha V2
 * If needed you can change the selector by overriding this file in your theme
 */
document.addEventListener('DOMContentLoaded', function () {
    var formFields = document.querySelector('.form-fields');
    if (formFields) {
        formFields.insertAdjacentHTML('beforeend', '<div id="captcha-box"></div>');
    }
});

