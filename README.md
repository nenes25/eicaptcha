[![GitHub stars](https://img.shields.io/github/stars/nenes25/eicaptcha)](https://github.com/nenes25/eicaptcha/stargazers) 
[![GitHub forks](https://img.shields.io/github/forks/nenes25/eicaptcha)](https://github.com/nenes25/eicaptcha/network) 
[![GitHub release](https://img.shields.io/github/v/release/nenes25/eicaptcha)](https://github.com/nenes25/eicaptcha/)
[![Github All Releases](https://img.shields.io/github/downloads/nenes25/eicaptcha/total.svg)]()
[![Github issues](https://img.shields.io/github/issues-raw/nenes25/eicaptcha)]()

# eicaptcha
Module EiCaptcha for prestashop 1.7 +

The module is also available for prestashop version 1.6.x see here : https://github.com/nenes25/eicaptcha/tree/master  
or download 0.4.x releases ( This version remains available but support and evolutions are stopped )   

This module display Google recaptcha on the following forms :
 - contact form
 - account creation form
 - newsletter subscription ( since 2.1.0)
 - custom module forms ( since 2.4.0 see : https://www.h-hennes.fr/blog/2022/08/22/prestashop-ajouter-un-captcha-sur-les-formulaires-de-vos-modules/ )

The module is compatible with both V2 and V3 recaptcha keys ( since v 2.3.0 )  

 This module relies upon the override of the following files :
 - AuthController
 - ContactForm Module

For newsletter subscription implementation it needs module **ps_emailsubscription at least  2.6.0**

 This module use composer to get recaptcha lib.  
 Don't forget to use `composer install` in order to download the necessary recaptcha composer package.   
 
 Otherwise you can go on the github release page https://github.com/nenes25/eicaptcha/releases and download the last 2.x version release to get the full package    
 
`Please do not use the github function "download as zip" which will not works`

 Screenshots with V2 keys
---

<p align="center">
	Captcha on contact form <br />
	<img src="https://www.h-hennes.fr/blog/wp-content/uploads/2017/07/eicaptcha-17-contact.jpg" alt="Captcha Contact Form" />
</p>

<p align="center">
	Captcha on account creation form <br />
	<img src="https://www.h-hennes.fr/blog/wp-content/uploads/2017/07/eicaptcha-17-account.jpg" alt="Captcha on account creation form" />
</p>

<p align="center">
	Captcha on newsletter form <br />
	<img src="https://www.h-hennes.fr/blog/wp-content/uploads/2021/03/captcha-newsletter.png" alt="Captcha on newsletter subscription form" />
</p>

Screenshots with V3 keys (invisible recaptcha)
---

With v3 keys you just need to check if the recaptcha box is present in the bottom right corner

<p align="center">
	V3 captcha <br />
	<img src="https://www.h-hennes.fr/blog/wp-content/uploads/2021/10/eicaptcha-v3.png" alt="Captcha V3" />
</p>

 Additionnal informations (French)
---

https://www.h-hennes.fr/blog/module-recaptcha-pour-le-formulaire-de-contact-prestashop/  
https://www.h-hennes.fr/blog/2017/07/11/module-catpcha-pour-prestashop-1-7/

 Compatibility
---

| Prestashop Version | Compatible |
|--------------------| -----------|
| 1.6.1.x and under  | :x: use version 0.4.x or 0.5.x instead |
| 1.7.0.x to 1.7.8.x | :heavy_check_mark: |
| 8.0.x              | :heavy_check_mark:|
