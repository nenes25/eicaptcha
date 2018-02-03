# How to install the captcha for the module "Send To Friend"

Prestashop 1.7
---
Not available

Prestashop 1.6
---

If you use the default theme, you just have to activate the module in administration and it should works.

For specific themes please override js file views/js/sendtoafriend.js in your theme

You have to add a div with id "recaptchaSendToAFriend" in your form template, it could be done directly in the js file as in the default theme with code like folowing :

//Append a div at end of the form to hold the captcha
    $('#send_friend_form_content .form_container')
            .after('<div class="captcha-content"><label for="captcha">Captcha</label><div id="recaptchaSendToAFriend"></div>');

//Or you can just add it in the form template like this
<div id="recaptchaSendToAFriend"></div>

//You also need to change the default form url

$.ajax({
//replace module original link by eicaptcha link
url: checkCaptchaUrl+'?rand=' + new Date().getTime(),

Prestashop 1.5
---
No more available since version 0.5.0