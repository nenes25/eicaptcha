<?php
/**
 * 2007-2021 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    Hennes Hervé <contact@h-hennes.fr>
 * @copyright 2013-2021 Hennes Hervé
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  http://www.h-hennes.fr/blog/
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Update module in version 2.3.0
 *
 * @param EiCaptcha $module
 *
 * @return bool
 */
function upgrade_module_2_3_0($module)
{
    //Intialialize a configuration with recaptcha v2 ( as key was not existing and v3 was not implemented before )
    return Configuration::updateGlobalValue('CAPTCHA_VERSION', 2);
}
