<?php

//TMP load en dur
require_once '/home/herve/www/prestashop/tests/1786/config/config.inc.php';

//Check Eicaptcha version
$eicaptcha = Module::getInstanceByName('eicaptcha');
if ( version_compare('2.5.0',$eicaptcha->version) > 0) {
    echo '<div class="warning" style="border:1px solid red;color:red;font-weight:bold;padding:10px;margin-bottom: 20px">';
    echo 'Warning the tests are implemented only since version 2.5.0 of the module and you have '.$eicaptcha->version.'<br>';
    echo 'They may not work as expected<br>';
    echo '</div>';
}

//Recaptcha V2 Specific test keys (cf.https://developers.google.com/recaptcha/docs/faq?hl=fr )
$captchaV2SiteKey = '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI';
$captchaV2SecretKey = '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe';

//Recaptcha V3 Specific test keys for this tests
$captchaV3SiteKey = '6LfLDMceAAAAAEWfCM1_p3D2CkuPfDnC0R2DE3ZS';
$captchaV3SecretKey = '6LfLDMceAAAAANazDlF6_Ddhlj5odSwxyzSmdJxr';

$testCasesConfiguration = [
    //Contact Form
    'C_1' => [
        'CAPTCHA_VERSION' => 2,
        'CAPTCHA_PRIVATE_KEY' => $captchaV2SecretKey,
        'CAPTCHA_PUBLIC_KEY' => $captchaV2SiteKey,
        'CAPTCHA_ENABLE_CONTACT' => 0,
        'CAPTCHA_ENABLE_LOGGED_CUSTOMERS' => 1,
        'CAPTCHA_FORCE_LANG' => '',
        'CAPTCHA_THEME' => 0,
    ],
    'C_2' => [
        'CAPTCHA_VERSION' => 2,
        'CAPTCHA_PRIVATE_KEY' => $captchaV2SecretKey,
        'CAPTCHA_PUBLIC_KEY' => $captchaV2SiteKey,
        'CAPTCHA_ENABLE_CONTACT' => 1,
        'CAPTCHA_ENABLE_LOGGED_CUSTOMERS' => 1,
        'CAPTCHA_FORCE_LANG' => '',
        'CAPTCHA_THEME' => 0,
    ],
    'C_3' => [
        'CAPTCHA_VERSION' => 2,
        'CAPTCHA_PRIVATE_KEY' => $captchaV2SecretKey,
        'CAPTCHA_PUBLIC_KEY' => $captchaV2SiteKey,
        'CAPTCHA_ENABLE_CONTACT' => 1,
        'CAPTCHA_ENABLE_LOGGED_CUSTOMERS' => 0,
        'CAPTCHA_FORCE_LANG' => '',
        'CAPTCHA_THEME' => 0,
    ],
    'C_4' => [
        'CAPTCHA_VERSION' => 2,
        'CAPTCHA_PRIVATE_KEY' => $captchaV2SecretKey,
        'CAPTCHA_PUBLIC_KEY' => $captchaV2SiteKey,
        'CAPTCHA_ENABLE_CONTACT' => 1,
        'CAPTCHA_ENABLE_LOGGED_CUSTOMERS' => 1,
        'CAPTCHA_FORCE_LANG' => 'de',
        'CAPTCHA_THEME' => 0,
    ],
    'C_5' => [
        'CAPTCHA_VERSION' => 2,
        'CAPTCHA_PRIVATE_KEY' => $captchaV2SecretKey,
        'CAPTCHA_PUBLIC_KEY' => $captchaV2SiteKey,
        'CAPTCHA_ENABLE_CONTACT' => 1,
        'CAPTCHA_ENABLE_LOGGED_CUSTOMERS' => 1,
        'CAPTCHA_FORCE_LANG' => '',
        'CAPTCHA_THEME' => 1,
    ],
    'C_6' => [
        'CAPTCHA_VERSION' => 3,
        'CAPTCHA_PRIVATE_KEY' => $captchaV3SecretKey,
        'CAPTCHA_PUBLIC_KEY' => $captchaV3SiteKey,
        'CAPTCHA_ENABLE_CONTACT' => 1,
        'CAPTCHA_ENABLE_LOGGED_CUSTOMERS' => 1,
    ],
    'C_7' => [
        'CAPTCHA_VERSION' => 2,
        'CAPTCHA_PRIVATE_KEY' => $captchaV3SecretKey,
        'CAPTCHA_PUBLIC_KEY' => $captchaV3SiteKey,
        'CAPTCHA_ENABLE_CONTACT' => 1,
        'CAPTCHA_ENABLE_LOGGED_CUSTOMERS' => 0,
    ],
    //Customer Registration
    'CU_1' => [
        'CAPTCHA_VERSION' => 2,
        'CAPTCHA_PRIVATE_KEY' => $captchaV2SecretKey,
        'CAPTCHA_PUBLIC_KEY' => $captchaV2SiteKey,
        'CAPTCHA_ENABLE_ACCOUNT' => 0,
        'CAPTCHA_USE_AUTHCONTROLLER_OVERRIDE' => 1,
        'CAPTCHA_FORCE_LANG' => '',
        'CAPTCHA_THEME' => 0,
    ],
    'CU_2' => [
        'CAPTCHA_VERSION' => 2,
        'CAPTCHA_PRIVATE_KEY' => $captchaV2SecretKey,
        'CAPTCHA_PUBLIC_KEY' => $captchaV2SiteKey,
        'CAPTCHA_ENABLE_ACCOUNT' => 1,
        'CAPTCHA_USE_AUTHCONTROLLER_OVERRIDE' => 1,
        'CAPTCHA_FORCE_LANG' => '',
        'CAPTCHA_THEME' => 0,
    ],
    'CU_3' => [
        'CAPTCHA_VERSION' => 2,
        'CAPTCHA_PRIVATE_KEY' => $captchaV2SecretKey,
        'CAPTCHA_PUBLIC_KEY' => $captchaV2SiteKey,
        'CAPTCHA_ENABLE_ACCOUNT' => 1,
        'CAPTCHA_USE_AUTHCONTROLLER_OVERRIDE' => 1,
        'CAPTCHA_FORCE_LANG' => 'de',
        'CAPTCHA_THEME' => 0,
    ],
    'CU_4' => [
        'CAPTCHA_VERSION' => 2,
        'CAPTCHA_PRIVATE_KEY' => $captchaV2SecretKey,
        'CAPTCHA_PUBLIC_KEY' => $captchaV2SiteKey,
        'CAPTCHA_ENABLE_ACCOUNT' => 1,
        'CAPTCHA_USE_AUTHCONTROLLER_OVERRIDE' => 1,
        'CAPTCHA_FORCE_LANG' => '',
        'CAPTCHA_THEME' => 1,
    ],
    'CU_5' => [
        'CAPTCHA_VERSION' => 2,
        'CAPTCHA_PRIVATE_KEY' => $captchaV2SecretKey,
        'CAPTCHA_PUBLIC_KEY' => $captchaV2SiteKey,
        'CAPTCHA_ENABLE_ACCOUNT' => 1,
        'CAPTCHA_USE_AUTHCONTROLLER_OVERRIDE' => 0,
        'CAPTCHA_FORCE_LANG' => '',
        'CAPTCHA_THEME' => 1,
    ],
    'CU_6' => [
        'CAPTCHA_VERSION' => 3,
        'CAPTCHA_PRIVATE_KEY' => $captchaV3SecretKey,
        'CAPTCHA_PUBLIC_KEY' => $captchaV3SiteKey,
        'CAPTCHA_ENABLE_ACCOUNT' => 1,
        'CAPTCHA_USE_AUTHCONTROLLER_OVERRIDE' => 1,
        'CAPTCHA_FORCE_LANG' => '',
        'CAPTCHA_THEME' => 0,
    ],
    'CU_7' => [
        'CAPTCHA_VERSION' => 3,
        'CAPTCHA_PRIVATE_KEY' => $captchaV3SecretKey,
        'CAPTCHA_PUBLIC_KEY' => $captchaV3SiteKey,
        'CAPTCHA_ENABLE_ACCOUNT' => 1,
        'CAPTCHA_USE_AUTHCONTROLLER_OVERRIDE' => 0,
        'CAPTCHA_FORCE_LANG' => '',
        'CAPTCHA_THEME' => 0,
    ],
    //Newsletter
    'NL_1' => [
        'CAPTCHA_VERSION' => 2,
        'CAPTCHA_PRIVATE_KEY' => $captchaV2SecretKey,
        'CAPTCHA_PUBLIC_KEY' => $captchaV2SiteKey,
        'CAPTCHA_ENABLE_NEWSLETTER' => 0,
        'CAPTCHA_ENABLE_LOGGED_CUSTOMERS' => 1,
        'CAPTCHA_FORCE_LANG' => '',
        'CAPTCHA_THEME' => 0,
    ],
    'NL_2' => [
        'CAPTCHA_VERSION' => 2,
        'CAPTCHA_PRIVATE_KEY' => $captchaV2SecretKey,
        'CAPTCHA_PUBLIC_KEY' => $captchaV2SiteKey,
        'CAPTCHA_ENABLE_NEWSLETTER' => 1,
        'CAPTCHA_ENABLE_LOGGED_CUSTOMERS' => 1,
        'CAPTCHA_FORCE_LANG' => '',
        'CAPTCHA_THEME' => 0,
    ],
    'NL_3' => [
        'CAPTCHA_VERSION' => 2,
        'CAPTCHA_PRIVATE_KEY' => $captchaV2SecretKey,
        'CAPTCHA_PUBLIC_KEY' => $captchaV2SiteKey,
        'CAPTCHA_ENABLE_NEWSLETTER' => 1,
        'CAPTCHA_ENABLE_LOGGED_CUSTOMERS' => 0,
        'CAPTCHA_FORCE_LANG' => '',
        'CAPTCHA_THEME' => 0,
    ],
    'NL_4' => [
        'CAPTCHA_VERSION' => 2,
        'CAPTCHA_PRIVATE_KEY' => $captchaV2SecretKey,
        'CAPTCHA_PUBLIC_KEY' => $captchaV2SiteKey,
        'CAPTCHA_ENABLE_NEWSLETTER' => 1,
        'CAPTCHA_ENABLE_LOGGED_CUSTOMERS' => 1,
        'CAPTCHA_FORCE_LANG' => 'de',
        'CAPTCHA_THEME' => 0,
    ],
    'NL_5' => [
        'CAPTCHA_VERSION' => 2,
        'CAPTCHA_PRIVATE_KEY' => $captchaV2SecretKey,
        'CAPTCHA_PUBLIC_KEY' => $captchaV2SiteKey,
        'CAPTCHA_ENABLE_NEWSLETTER' => 1,
        'CAPTCHA_ENABLE_LOGGED_CUSTOMERS' => 1,
        'CAPTCHA_FORCE_LANG' => '',
        'CAPTCHA_THEME' => 1,
    ],
    'NL_6' => [
        'CAPTCHA_VERSION' => 3,
        'CAPTCHA_PRIVATE_KEY' => $captchaV3SecretKey,
        'CAPTCHA_PUBLIC_KEY' => $captchaV3SiteKey,
        'CAPTCHA_ENABLE_NEWSLETTER' => 1,
        'CAPTCHA_ENABLE_LOGGED_CUSTOMERS' => 1,
    ],
    'NL_7' => [
        'CAPTCHA_VERSION' => 3,
        'CAPTCHA_PRIVATE_KEY' => $captchaV3SecretKey,
        'CAPTCHA_PUBLIC_KEY' => $captchaV3SiteKey,
        'CAPTCHA_ENABLE_NEWSLETTER' => 1,
        'CAPTCHA_ENABLE_LOGGED_CUSTOMERS' => 0,
    ],
];

//Get the case to run
$testCase = strip_tags($_GET['test_case']);

if ( array_key_exists($testCase,$testCasesConfiguration)){
    $configurationToApply = $testCasesConfiguration[$testCase];
    //Apply require configuration
    foreach ( $configurationToApply as $key => $value){
        echo "Set Value <strong>".$value."</strong> for configuration key <i>".$key."</i><br />";
        Configuration::updateValue($key,$value);
    }
} else {
    echo '<div class="warning" style="border:1px solid red;color:red;font-weight:bold;padding:10px;margin-bottom: 20px">';
    echo 'Error : Unknow test' .$testCase;
    echo '</div>';
}