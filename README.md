[![GitHub stars](https://img.shields.io/github/stars/nenes25/eicaptcha)](https://github.com/nenes25/eicaptcha/stargazers) 
[![GitHub forks](https://img.shields.io/github/forks/nenes25/eicaptcha)](https://github.com/nenes25/eicaptcha/network) 
[![Github All Releases](https://img.shields.io/github/downloads/nenes25/eicaptcha/total.svg)]()

# eicaptcha
Module EiCaptcha for **prestashop 1.6 and under**  
A version for prestashop 1.7 is also available, please switch to branch 17 to get the code.

This module display Google recaptcha **( V2 Only )** on the following forms :
 - contact form
 - account creation form

 This module relies upon the override of the ContactController.

 Installation
 ----
 For non technicals users, go to <a href="https://github.com/nenes25/eicaptcha/releases">github release page</a> and download the last one with the tag 0.4.x  
 
 If you clone or download the package form this page, don't forget to use `composer install` in order to download the necessary recaptcha composer package.  
 

 
 For more details about this module  : 
 http://h-hennes.fr/blog/module-recaptcha-pour-le-formulaire-de-contact-prestashop/ (FR)
 
  Compatibility
 ---
 
 | Prestashop Version | Compatible |
 | ------------------ | -----------|
 | 1.5.x | :heavy_check_mark: |
 | 1.6.x | :heavy_check_mark:|
 | 1.6.1.x | :heavy_check_mark: |
 | 1.7.x.| :x: use version 2.0.x instead|
 
 Screenshots
--- 

<p align="center">
	Captcha on contact form <br />
	<img src="http://www.h-hennes.fr/blog/wp-content/uploads/2015/06/eicaptcha-v2-contact-form.jpg" alt="Captcha Contact Form" />
</p>

<p align="center">
	Captcha on account creation form <br />
	<img src="http://www.h-hennes.fr/blog/wp-content/uploads/2015/06/eicaptcha-v2-account.jpg" alt="Captcha on account creation form" />
</p>
