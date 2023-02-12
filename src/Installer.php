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

namespace Eicaptcha\Module;

use Configuration;
use EiCaptcha;

class Installer
{
    /**
     * @var EiCaptcha
     */
    private $module;

    /**
     * @var array
     */
    protected $_hooks = [
        'displayHeader',
        'displayCustomerAccountForm',
        'displayNewsletterRegistration',
        'actionCustomerRegisterSubmitCaptcha',
        'actionContactFormSubmitBefore',
        'actionNewsletterRegistrationBefore',
        'actionAdminControllerSetMedia',
        'displayEicaptchaVerification',
    ];

    /**
     * Installer constructor.
     *
     * @param EiCaptcha $module
     */
    public function __construct(EiCaptcha $module)
    {
        $this->module = $module;
    }

    /**
     * Eicaptcha Installer
     *
     * @return bool
     */
    public function install()
    {
        return $this->installHooks() && $this->installConfigurations();
    }

    /**
     * Eicaptcha Uninstaller
     *
     * @return bool
     */
    public function uninstall()
    {
        return $this->uninstallConfigurations();
    }

    /**
     * Install Hooks
     *
     * @return bool
     */
    protected function installHooks()
    {
        return $this->module->registerHook($this->_hooks);
    }

    /**
     * Install Configurations
     *
     * @return bool
     */
    protected function installConfigurations()
    {
        return Configuration::updateGlobalValue('CAPTCHA_ENABLE_ACCOUNT', 0)
        && Configuration::updateValue('CAPTCHA_ENABLE_CONTACT', 0)
        && Configuration::updateValue('CAPTCHA_ENABLE_NEWSLETTER', 0)
        && Configuration::updateValue('CAPTCHA_THEME', 0)
        && Configuration::updateValue('CAPTCHA_DEBUG', 0)
        && Configuration::updateValue('CAPTCHA_ENABLE_LOGGED_CUSTOMERS', 1);
    }

    /**
     * @return bool
     */
    protected function uninstallConfigurations()
    {
        return Configuration::deleteByName('CAPTCHA_ENABLE_ACCOUNT')
        && Configuration::deleteByName('CAPTCHA_ENABLE_CONTACT')
        && Configuration::deleteByName('CAPTCHA_ENABLE_NEWSLETTER')
        && Configuration::deleteByName('CAPTCHA_THEME')
        && Configuration::deleteByName('CAPTCHA_DEBUG')
        && Configuration::deleteByName('CAPTCHA_VERSION')
        && Configuration::deleteByName('CAPTCHA_ENABLE_LOGGED_CUSTOMERS');
    }
}
