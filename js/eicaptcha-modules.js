/**
 * EiCaptcha Gestion des captchas pour les modules sendtoAFriend
 * @param {type} param
 */  
var onloadCallback = function() {
    if ( document.getElementById('recaptchaSendToAFriend')) {
     grecaptcha.render("recaptchaSendToAFriend", {"sitekey" : RecaptachKey});
    }
	if ( document.getElementById('recaptchaProductComments')) {
     grecaptcha.render("recaptchaProductComments", {"sitekey" : RecaptachKey});
    }
};
