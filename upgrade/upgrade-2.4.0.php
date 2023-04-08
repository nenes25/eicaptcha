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
 * Upgrade module 2_4_0 Add new options to manage new forms and to disallow captcha for logged users
 *
 * @param Module $module
 *
 * @return bool
 */
function upgrade_module_2_4_0($module)
{
    return
        Configuration::updateGlobalValue('CAPTCHA_ENABLE_LOGGED_CUSTOMERS', 1)
        && $module->registerHook('actionCustomerRegisterSubmitCaptcha');
}
