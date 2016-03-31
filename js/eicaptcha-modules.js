/**
 * EiCaptcha Gestion des captchas pour les modules sendtoAFriend
 * @param {type} param
 */  
var onloadCallback = function() {
    if ( document.getElementById('recaptchaSendToAFriend')) {
     grecaptcha.render("recaptchaSendToAFriend", {"sitekey" : RecaptachKey , "theme" : RecaptchaTheme});
    }
	if ( document.getElementById('recaptchaProductComments')) {
     recaptchaProductComment = grecaptcha.render("recaptchaProductComments", {"sitekey" : RecaptachKey, "theme" : RecaptchaTheme});
    }
};
