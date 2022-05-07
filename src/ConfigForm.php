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
use Context;
use EiCaptcha;
use HelperForm;
use Language;
use Tools;

class ConfigForm
{
    /**
     * @var EiCaptcha
     */
    private $module;

    /**
     * @var Context
     */
    private $context;

    /**
     * Installer constructor.
     *
     * @param EiCaptcha $module
     */
    public function __construct(EiCaptcha $module)
    {
        $this->module = $module;
        $this->context = $this->module->getContext();
    }

    /**
     * Admin Form for module Configuration
     *
     * @return string
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function renderForm()
    {
        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Eicaptcha Configuration'),
                    'icon' => 'icon-cogs',
                ],
                'tabs' => [
                    'general' => $this->l('General configuration'),
                    'advanced' => $this->l('Advanded parameters'),
                ],
                'description' => $this->l('To get your own public and private keys please click on the folowing link')
                    . '<br /><a href="https://www.google.com/recaptcha/intro/index.html" target="_blank">https://www.google.com/recaptcha/intro/index.html</a>',
                'input' => [
                    [
                        'type' => 'radio',
                        'label' => $this->l('Recaptcha Version'),
                        'name' => 'CAPTCHA_VERSION',
                        'required' => true,
                        'class' => 't',
                        'values' => [
                            [
                                'id' => 'v2',
                                'value' => 2,
                                'label' => $this->l('V2'),
                            ],
                            [
                                'id' => 'v3',
                                'value' => 3,
                                'label' => $this->l('V3'),
                            ],
                        ],
                        'tab' => 'general',
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Captcha public key (Site key)'),
                        'name' => 'CAPTCHA_PUBLIC_KEY',
                        'required' => true,
                        'empty_message' => $this->l('Please fill the captcha public key'),
                        'tab' => 'general',
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Captcha private key (Secret key)'),
                        'name' => 'CAPTCHA_PRIVATE_KEY',
                        'required' => true,
                        'empty_message' => $this->l('Please fill the captcha private key'),
                        'tab' => 'general',
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Enable Captcha for logged customers'),
                        'name' => 'CAPTCHA_ENABLE_LOGGED_CUSTOMERS',
                        'required' => true,
                        'class' => 't',
                        'is_bool' => true,
                        'hint' => $this->l('Define if logged customers need to use captcha or not'),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                        'tab' => 'general',
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Enable Captcha for contact form'),
                        'name' => 'CAPTCHA_ENABLE_CONTACT',
                        'required' => true,
                        'class' => 't',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                        'tab' => 'general',
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Enable Captcha for account creation'),
                        'name' => 'CAPTCHA_ENABLE_ACCOUNT',
                        'required' => true,
                        'class' => 't',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                        'tab' => 'general',
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Enable Captcha for newsletter registration'),
                        'hint' => $this->l('Only availaibles in certain conditions*'),
                        'name' => 'CAPTCHA_ENABLE_NEWSLETTER',
                        'required' => true,
                        'class' => 't',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                        'tab' => 'general',
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Force Captcha language'),
                        'hint' => $this->l('Language code ( en-GB | fr | de | de-AT | ... ) - Leave empty for autodetect'),
                        'desc' => $this->l('For available language codes see: https://developers.google.com/recaptcha/docs/language'),
                        'name' => 'CAPTCHA_FORCE_LANG',
                        'required' => false,
                        'tab' => 'general',
                    ],
                    [
                        'type' => 'radio',
                        'label' => $this->l('Theme'),
                        'name' => 'CAPTCHA_THEME',
                        'required' => true,
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'cdark',
                                'value' => 0,
                                'label' => $this->l('Dark'),
                            ],
                            [
                                'id' => 'clight',
                                'value' => 1,
                                'label' => $this->l('Light'),
                            ],
                        ],
                        'tab' => 'general',
                    ],
                    [
                        'type' => 'switch',
                        'name' => 'CAPTCHA_DEBUG',
                        'label' => $this->l('Enable Debug'),
                        'hint' => $this->l('Use only for debug'),
                        'desc' => sprintf(
                            $this->l('Enable loging for debuging module, see file %s'),
                            dirname(__FILE__) . '/logs/debug.log'
                        ),
                        'required' => false,
                        'class' => 't',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Enabled'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabled'),
                            ],
                        ],
                        'tab' => 'advanced',
                    ],
                    [
                        'type' => 'html',
                        'label' => $this->l('Check module installation'),
                        'name' => 'enable_debug_html',
                        'html_content' => '<a href="' . $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->module->name . '&tab_module=' . $this->module->tab . '&module_name=' . $this->module->name . '&display_debug=1&token=' . Tools::getAdminTokenLite('AdminModules') . '">' . $this->l('Check if module is well installed') . '</a>',
                        'desc' => $this->l('click on this link will reload the page, please go again in tab "advanced parameters" to see the results'),
                        'tab' => 'advanced',
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                    'class' => 'button btn btn-default pull-right',
                ],
            ],
        ];

        //Display debug data to help detect issues
        if (Tools::getValue('display_debug')) {
            $fields_form['form']['input'][] = [
                'type' => 'html',
                'name' => 'debug_html',
                'html_content' => $this->module->getDebugger()->debugModuleInstall(),
                'tab' => 'advanced',
            ];
        }

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ?
            Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->id = 'eicaptcha';
        $helper->submit_action = 'SubmitCaptchaConfiguration';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->module->name . '&tab_module=' . $this->module->tab . '&module_name=' . $this->module->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        ];

        return $helper->generateForm([$fields_form]);
    }

    /**
     * Post Process in back office
     *
     * @return string|void
     */
    public function postProcess()
    {
        if (Tools::isSubmit('SubmitCaptchaConfiguration')) {
            Configuration::updateValue('CAPTCHA_VERSION', Tools::getValue('CAPTCHA_VERSION'));
            Configuration::updateValue('CAPTCHA_PUBLIC_KEY', Tools::getValue('CAPTCHA_PUBLIC_KEY'));
            Configuration::updateValue('CAPTCHA_PRIVATE_KEY', Tools::getValue('CAPTCHA_PRIVATE_KEY'));
            Configuration::updateValue('CAPTCHA_ENABLE_LOGGED_CUSTOMERS', Tools::getValue('CAPTCHA_ENABLE_LOGGED_CUSTOMERS'));
            Configuration::updateValue('CAPTCHA_ENABLE_ACCOUNT', (int) Tools::getValue('CAPTCHA_ENABLE_ACCOUNT'));
            Configuration::updateValue('CAPTCHA_ENABLE_CONTACT', (int) Tools::getValue('CAPTCHA_ENABLE_CONTACT'));
            Configuration::updateValue('CAPTCHA_ENABLE_NEWSLETTER', (int) Tools::getValue('CAPTCHA_ENABLE_NEWSLETTER'));
            Configuration::updateValue('CAPTCHA_FORCE_LANG', Tools::getValue('CAPTCHA_FORCE_LANG'));
            Configuration::updateValue('CAPTCHA_THEME', (int) Tools::getValue('CAPTCHA_THEME'));
            Configuration::updateValue('CAPTCHA_DEBUG', (int) Tools::getValue('CAPTCHA_DEBUG'));

            return $this->module->displayConfirmation($this->l('Settings updated'));
        }
    }

    /**
     * Get config values to hydrate the helperForm
     *
     * @return array
     */
    public function getConfigFieldsValues()
    {
        return [
            'CAPTCHA_VERSION' => Tools::getValue('CAPTCHA_VERSION', Configuration::get('CAPTCHA_VERSION')),
            'CAPTCHA_PRIVATE_KEY' => Tools::getValue('CAPTCHA_PRIVATE_KEY', Configuration::get('CAPTCHA_PRIVATE_KEY')),
            'CAPTCHA_PUBLIC_KEY' => Tools::getValue('CAPTCHA_PUBLIC_KEY', Configuration::get('CAPTCHA_PUBLIC_KEY')),
            'CAPTCHA_ENABLE_LOGGED_CUSTOMERS' => Tools::getValue('CAPTCHA_ENABLE_LOGGED_CUSTOMERS', Configuration::get('CAPTCHA_ENABLE_LOGGED_CUSTOMERS')),
            'CAPTCHA_ENABLE_ACCOUNT' => Tools::getValue('CAPTCHA_ENABLE_ACCOUNT', Configuration::get('CAPTCHA_ENABLE_ACCOUNT')),
            'CAPTCHA_ENABLE_CONTACT' => Tools::getValue('CAPTCHA_ENABLE_CONTACT', Configuration::get('CAPTCHA_ENABLE_CONTACT')),
            'CAPTCHA_ENABLE_NEWSLETTER' => Tools::getValue('CAPTCHA_ENABLE_NEWSLETTER', Configuration::get('CAPTCHA_ENABLE_NEWSLETTER')),
            'CAPTCHA_FORCE_LANG' => Tools::getValue('CAPTCHA_FORCE_LANG', Configuration::get('CAPTCHA_FORCE_LANG')),
            'CAPTCHA_THEME' => Tools::getValue('CAPTCHA_THEME', Configuration::get('CAPTCHA_THEME')),
            'CAPTCHA_DEBUG' => Tools::getValue('CAPTCHA_DEBUG', Configuration::get('CAPTCHA_DEBUG')),
        ];
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
        return $this->module->l($trans, 'configform');
    }
}
