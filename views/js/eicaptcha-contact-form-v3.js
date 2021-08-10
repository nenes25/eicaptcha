/**
 *
 * Recaptcha V3 Only
 *
 * This script add dynamically a new input hidden at the end of the contact form
 * The captcha is render inside this div by Recaptcha V3
 * If needed you can change the selector by overriding this file in your theme
 */
$(document).ready(function () {
    $('.form-fields').append('<input type="hidden" id="captcha-box" value="" name="g-recaptcha-response">');
});