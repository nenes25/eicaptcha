<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file docs/licenses/LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to contact@h-hennes.fr so we can send you a copy immediately.
 *
 * @author    Hervé HENNES <contact@h-hhennes.fr> and contributors / https://github.com/nenes25/eicaptcha
 * @copyright since 2013 Hervé HENNES
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License ("AFL") v. 3.0
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Upgrade module 2_5_0 Replace the override of the AuthController by using the hook actionSubmitAccountBefore
 *
 * @param Module $module
 *
 * @return bool
 */
function upgrade_module_2_5_0($module)
{
    //Add a new configuration for websites with elementor to allow to load the recaptcha library on all pages
    //But as it is unnecessary for most of the website it will be disabled by default
    Configuration::updateValue('CAPTCHA_LOAD_EVERYWHERE', '0');

    //For the version 8.0+ we need to use to hook as the controller has changed.
    //For the version under it we let the choice to the customer, but by default we keep the legacy behavior
    $enableOverride = version_compare(_PS_VERSION_, '8.0') < 0 ? '1' : '0';
    Configuration::updateValue('CAPTCHA_USE_AUTHCONTROLLER_OVERRIDE', $enableOverride);

    $module->uninstallOverrides();
    $module->installOverrides();

    return $module->registerHook('actionSubmitAccountBefore');
}
