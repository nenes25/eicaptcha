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
use Module;

class Debugger
{
    /** @var string Url of the module relases on github */
    public const URL_MODULE_RELEASES = 'https://github.com/nenes25/eicaptcha/releases';

    /** @var string Url of the wiki for the hook issue */
    public const URL_WIKI_DISPLAYCUSTOMERACCOUNTFORM = 'https://github.com/nenes25/eicaptcha/wiki/Issue-:-Unable-to-find-the-hook-displayCustomerAccountForm-in-the-default-template';

    /**
     * @var EiCaptcha
     */
    private $module;

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
     * Check if needed composer directory is present
     *
     * @return string
     */
    public function checkComposer()
    {
        if (!is_dir(_PS_MODULE_DIR_ . $this->module->name . '/vendor')) {
            $errorMessage = $this->l('This module need composer to work, please go into module directory %s and run composer install or dowload and install latest release from %s');

            return $this->module->displayError(
                sprintf(
                    $errorMessage,
                    _PS_MODULE_DIR_ . $this->module->name,
                    self::URL_MODULE_RELEASES
                )
            );
        }

        return '';
    }

    /**
     * Check if debug mode is enabled
     *
     * @return bool
     */
    public function isDebugEnabled()
    {
        return (bool) Configuration::get('CAPTCHA_DEBUG');
    }

    /**
     * Debug module installation
     *
     * @return string
     */
    public function debugModuleInstall()
    {
        $errors = [];
        $success = [];

        $modulesChecks = $this->checkModules();
        $hookChecks = $this->checkModuleHooks();
        $overridesChecks = $this->checkOverrides();
        $newsletterChecks = $this->checkNewsletter();
        $hookHookDisplayCustomerAccountForm = $this->checkHookDisplayCustomerAccountForm();

        $errors = array_merge(
            $errors,
            $modulesChecks['errors'],
            $hookChecks['errors'],
            $overridesChecks['errors'],
            $hookHookDisplayCustomerAccountForm['errors'],
            $newsletterChecks['errors']
        );

        $success = array_merge(
            $success,
            $modulesChecks['success'],
            $hookChecks['success'],
            $overridesChecks['success'],
            $hookHookDisplayCustomerAccountForm['success'],
            $newsletterChecks['success']
        );

        $this->module->getContext()->smarty->assign([
            'errors' => $errors,
            'success' => $success,
            'recaptchaVersion' => Configuration::get('CAPTCHA_VERSION'),
            'prestashopVersion' => _PS_VERSION_,
            'themeName' => _THEME_NAME_,
            'phpVersion' => phpversion(),
        ]);

        return $this->module->fetch('module:eicaptcha/views/templates/admin/debug.tpl');
    }

    /**
     * Check modules necessary for the module to work
     *
     * @return array
     */
    protected function checkModules()
    {
        $errors = $success = [];
        //Check if module version is compatible with current PS version
        if (!$this->module->checkCompliancy()) {
            $errors[] = $this->l('the module is not compatible with your version');
        } else {
            $success[] = $this->l('the module is compatible with your version');
        }

        //Check if module contactform is installed
        if (!Module::isInstalled('contactform')) {
            $errors[] = $this->l('the module contatcform is not installed');
        } else {
            $success[] = $this->l('the module contactform is installed');
        }

        return [
            'errors' => $errors,
            'success' => $success,
        ];
    }

    /**
     * Check if module is well hooked on all necessary hooks
     *
     * @return array
     */
    protected function checkModuleHooks()
    {
        $errors = $success = [];
        $modulesHooks = [
            'displayHeader',
            'displayCustomerAccountForm',
            'displayNewsletterRegistration',
            'actionCustomerRegisterSubmitCaptcha',
            'actionContactFormSubmitBefore',
            'actionNewsletterRegistrationBefore',
            'actionAdminControllerSetMedia',
        ];
        foreach ($modulesHooks as $hook) {
            if (!$this->module->isRegisteredInHook($hook)) {
                $errors[] = sprintf(
                    $this->l('the module is not registered in hook %s'),
                    '<strong>' . $hook . '</strong>'
                );
            } else {
                $success[] = sprintf(
                    $this->l('the module well registered in hook %s'),
                    '<strong>' . $hook . '</strong>'
                );
            }
        }

        return [
            'errors' => $errors,
            'success' => $success,
        ];
    }

    /**
     * Check that all overrides behaviors are good
     *
     * @return array
     */
    protected function checkOverrides()
    {
        $errors = $success = [];

        //Check if override are disabled in configuration
        if (Configuration::get('PS_DISABLE_OVERRIDES') == 1) {
            $errors[] = $this->l('Overrides are disabled on your website');
        } else {
            $success[] = $this->l('Overrides are enabled on your website');
        }

        //Check if file overrides exists
        if (!file_exists(_PS_OVERRIDE_DIR_ . 'controllers/front/AuthController.php')) {
            $errors[] = $this->l('AuthController.php override does not exists');
        } else {
            $success[] = $this->l('AuthController.php override exists');
        }

        if (!file_exists(_PS_OVERRIDE_DIR_ . 'modules/contactform/contactform.php')) {
            $errors[] = $this->l('contactform.php override does not exists');
        } else {
            $success[] = $this->l('contactform.php override exists');
        }

        //Check if file override is written in class_index.php files
        if (file_exists(_PS_CACHE_DIR_ . '/class_index.php')) {
            $classesArray = (include _PS_CACHE_DIR_ . '/class_index.php');
            if ($classesArray['AuthController']['path'] != 'override/controllers/front/AuthController.php') {
                $errors[] = $this->l('Authcontroller override is not present in class_index.php');
            } else {
                $success[] = $this->l('Authcontroller override is present in class_index.php');
            }
        } else {
            $errors[] = $this->l('no class_index.php found');
        }

        return [
            'errors' => $errors,
            'success' => $success,
        ];
    }

    /**
     * Check the newsletter configuration
     *
     * @return array
     */
    protected function checkNewsletter()
    {
        $errors = $success = [];

        //Check if we can display the captcha in the newsletter
        if (!Module::isInstalled('ps_emailsubscription')) {
            $errors[] = $this->l('the module ps_emailsubscription is not installed you will not be able to use captcha on newslettter');
        } else {
            if ($this->module->canUseCaptchaOnNewsletter()) {
                $success[] = $this->l('Module ps_emailsubscription version allow to use captcha on newsletter');

                $newsletterTemplatesFiles = [
                    _PS_THEME_DIR_ . 'modules/ps_emailsubscription/views/templates/hook/ps_emailsubscription.tpl',
                    _PS_PARENT_THEME_DIR_ . 'modules/ps_emailsubscription/views/templates/hook/ps_emailsubscription.tpl',
                ];
                $hookFound = $hasThemeFile = false;
                foreach ($newsletterTemplatesFiles as $newsletterTemplateFile) {
                    if (is_file($newsletterTemplateFile)) {
                        $hasThemeFile = true;
                        $newsletterTemplateContent = file_get_contents($newsletterTemplateFile);
                        if (preg_match('#displayNewsletterRegistration#', $newsletterTemplateContent)) {
                            $hookFound = true;
                        }
                        break;
                    }
                }
                if (false === $hookFound && true === $hasThemeFile) {
                    $moduleDefaultFile = _PS_MODULE_DIR_ . 'ps_emailsubscription/views/templates/hook/ps_emailsubscription.tpl';
                    $errors[] = sprintf(
                        $this->l(
                            'Missing hook %s in template %s , Please check in original module file to adapt : %s'
                        ),
                        '<strong>displayNewsletterRegistration</strong>',
                        '<i>' . $newsletterTemplateFile . '</i>',
                        '<i>' . $moduleDefaultFile . '</i>'
                    );
                }
                //@Todo manage multi-shop configuration
            } else {
                $errors[] = $this->l('Module ps_emailsubscription version do not allow to use captcha on newsletter');
            }
        }

        return [
            'errors' => $errors,
            'success' => $success,
        ];
    }

    /**
     * In some theme the hooks displayCustomerAccountForm is missing
     * We try to detect if by searching the string {$hook_create_account_form nofilter}
     * in template themes/THEMENAME/templates/customer/_partials/customer-form.tpl
     *
     * @return array
     */
    protected function checkHookDisplayCustomerAccountForm()
    {
        $errors = $success = [];
        $templateFiles = [
            _PS_THEME_DIR_ . 'templates/customer/_partials/customer-form.tpl',
            _PS_PARENT_THEME_DIR_ . 'templates/customer/_partials/customer-form.tpl',
        ];
        foreach ($templateFiles as $templateFile) {
            if (is_file($templateFile)) {
                $templateContent = file_get_contents($templateFile);
                if (preg_match('#\{\$hook_create_account_form nofilter\}#', $templateContent)) {
                    $success[] = $this->l('The hook displayCustomerAccountForm is present in the default template');
                } else {
                    $errors[] = sprintf(
                        $this->l('Unable to find the hook displayCustomerAccountForm in the default template, you can read more about it %s'),
                        '<a href="' . self::URL_WIKI_DISPLAYCUSTOMERACCOUNTFORM . '" target="_blank">' . $this->l('here') . '</a>'
                    );
                }
                break;
            }
        }

        return [
            'errors' => $errors,
            'success' => $success,
        ];
    }

    /**
     * Log debug messages
     *
     * @param string $message
     *
     * @return void
     */
    public function log($message): void
    {
        if ($this->isDebugEnabled()) {
            file_put_contents(
                dirname(__FILE__) . '/logs/debug.log',
                date('Y-m-d H:i:s') . ': ' . $message . "\n",
                FILE_APPEND
            );
        }
    }

    /**
     * Alias of l function with specific context
     *
     * @param string $trans
     *
     * @return string
     */
    public function l($trans)
    {
        return $this->module->l($trans, 'debugger');
    }
}
