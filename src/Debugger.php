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
 *
 * @author    Hennes Hervé <contact@h-hennes.fr>
 * @copyright 2013-2021 Hennes Hervé
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  http://www.h-hennes.fr/blog/
 */

namespace Eicaptcha\Module;

use EiCaptcha;
use Module;
use Configuration;

class Debugger
{

    /**
     * @var EiCaptcha
     */
    private $module;

    /**
     * Installer constructor.
     * @param EiCaptcha $module
     */
    public function __construct(EiCaptcha $module)
    {
        $this->module = $module;
    }

    /**
     * Check if needed composer directory is present
     * @return string
     */
    public function checkComposer()
    {
        if (!is_dir(_PS_MODULE_DIR_.$this->module->name . '/vendor')) {
            $errorMessage = $this->l('This module need composer to work, please go into module directory %s and run composer install or dowload and install latest release from %s');
            return $this->displayError(
                sprintf(
                    $errorMessage,
                    _PS_MODULE_DIR_.$this->module->name,
                    'https://github.com/nenes25/eicaptcha/releases'
                )
            );
        }
        return '';
    }

    /**
     * Check if debug mode is enabled
     * @return boolean
     */
    public function isDebugEnabled()
    {
        return (bool)Configuration::get('CAPTCHA_DEBUG');
    }


    /**
     * Debug module installation
     * @return string
     */
    public function debugModuleInstall()
    {
        $errors = [];
        $success = [];

        //Check if module version is compatible with current PS version
        if (!$this->module->checkCompliancy()) {
            $errors[] = 'the module is not compatible with your version';
        } else {
            $success[] = 'the module is compatible with your version';
        }

        //Check if module is well hooked on all necessary hooks
        $modulesHooks = [
            'header', 'displayCustomerAccountForm', 'actionContactFormSubmitCaptcha',
            'actionContactFormSubmitBefore'
        ];
        foreach ($modulesHooks as $hook) {
            if (!$this->module->isRegisteredInHook($hook)) {
                $errors[] = 'the module is not registered in hook ' . $hook;
            } else {
                $success[] = 'the module is well registered in hook ' . $hook;
            }
        }

        //Check if module contactform is installed
        if (!Module::isInstalled('contactform')) {
            $errors[] = 'the module contatcform is not installed';
        } else {
            $success[] = 'the module contactform is installed';
        }

        //Check if override are disabled in configuration
        if (Configuration::get('PS_DISABLE_OVERRIDES') == 1) {
            $errors[] = 'Overrides are disabled on your website';
        } else {
            $success[] = 'Overrides are enabled on your website';
        }

        //Check if file overrides exists
        if (!file_exists(_PS_OVERRIDE_DIR_ . 'controllers/front/AuthController.php')) {
            $errors[] = 'AuthController.php override does not exists';
        } else {
            $success[] = 'AuthController.php override exists';
        }

        if (!file_exists(_PS_OVERRIDE_DIR_ . 'modules/contactform/contactform.php')) {
            $errors[] = 'contactform.php override does not exists';
        } else {
            $success[] = 'contactform.php override exists';
        }

        //Check if file override is written in class_index.php files
        if (file_exists(_PS_CACHE_DIR_ . '/class_index.php')) {
            $classesArray = (include _PS_CACHE_DIR_ . '/class_index.php');
            if ($classesArray['AuthController']['path'] != 'override/controllers/front/AuthController.php') {
                $errors[] = 'Authcontroller override is not present in class_index.php';
            } else {
                $success[] = 'Authcontroller override is present in class_index.php';
            }
        } else {
            $errors[] = 'no class_index.php found';
        }

        //Display errors
        $errorsHtml = '';
        if (sizeof($errors)) {
            $errorsHtml .= '<div class="alert alert-warning"> Errors <br />'
                . '<ul>';
            foreach ($errors as $error) {
                $errorsHtml .= '<li>' . $error . '</li>';
            }
            $errorsHtml .= '</ul></div>';
        }

        //Display success
        $successHtml = '';
        if (sizeof($success)) {
            $successHtml .= '<div class="alert alert-success"> Success <br />'
                . '<ul>';
            foreach ($success as $msg) {
                $successHtml .= '<li>' . $msg . '</li>';
            }
            $successHtml .= '</ul></div>';
        }

        //Additionnal informations
        $informations = '<div class="alert alert-info">Aditionnal informations <br />'
            . '<ul>';
        //PS version
        $informations .= '<li>Prestashop version <strong>' . _PS_VERSION_ . '</strong></li>';
        //Theme
        $informations .= '<li>Theme name <strong>' . _THEME_NAME_ . '</strong></li>';
        //Check php version
        $informations .= '<li>Php version <strong>' . phpversion() . '</strong></li>';

        $informations .= '</ul></div>';

        return $errorsHtml . ' ' . $successHtml . ' ' . $informations;
    }

    /**
     * Log debug messages
     * @param string $message
     * @return void
     */
    public function log($message)
    {
        if ($this->isDebugEnabled()) {
            file_put_contents(
                dirname(__FILE__) . '/logs/debug.log',
                date('Y-m-d H:i:s') . ': ' . $message . "\n",
                FILE_APPEND
            );
        }
    }
}
