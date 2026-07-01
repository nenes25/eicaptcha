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
 * Upgrade to version 3.0.0
 * Migrates from single captcha system to multi-provider architecture
 *
 * @param EiCaptcha $module
 *
 * @return bool
 */
function upgrade_module_3_0_0($module)
{
    $result = true;

    // Set default provider to google_recaptcha for backward compatibility
    if (!Configuration::get('CAPTCHA_PROVIDER')) {
        $result = Configuration::updateValue('CAPTCHA_PROVIDER', 'google_recaptcha');
    }

    // Ensure CAPTCHA_VERSION is set (default to V2 if not set)
    if (!Configuration::get('CAPTCHA_VERSION')) {
        $result = $result && Configuration::updateValue('CAPTCHA_VERSION', 2);
    }

    // Ensure CAPTCHA_V3_MINIMAL_SCORE is set
    if (!Configuration::get('CAPTCHA_V3_MINIMAL_SCORE')) {
        $result = $result && Configuration::updateValue('CAPTCHA_V3_MINIMAL_SCORE', 0.5);
    }

    // The AuthController override is no longer needed, the native
    // actionSubmitAccountBefore hook is used instead (see #335)
    $result = $result && $module->unregisterHook('actionCustomerRegisterSubmitCaptcha');
    $result = $result && Configuration::deleteByName('CAPTCHA_USE_AUTHCONTROLLER_OVERRIDE');

    // Log migration if debug is enabled
    if (Configuration::get('CAPTCHA_DEBUG')) {
        $debugger = $module->getDebugger();
        $debugger->log('Module upgraded to version 3.0.0 - Multi-provider architecture enabled');
        $debugger->log('Current provider: ' . Configuration::get('CAPTCHA_PROVIDER'));
    }

    return $result;
}
