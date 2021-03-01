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

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once(dirname(__FILE__) . '/vendor/autoload.php');

use Eicaptcha\Module\Installer;
use Eicaptcha\Module\Debugger;

class EiCaptcha extends Module
{
    /** @var string */
    private $_html = '';

    /** @var array */
    protected $themes = [];
    /**
     * @var Debugger
     */
    protected $debugger;

    /**
     * @var Installer
     */
    protected $installer;

    public function __construct()
    {
        $this->author = 'hhennes';
        $this->name = 'eicaptcha';
        $this->tab = 'front_office_features';
        $this->version = '2.2.0';
        $this->need_instance = 1;

        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->l('Ei Captcha');
        $this->description = $this->l('Add a captcha to your website form');

        if (
            $this->active
            && (!Configuration::get('CAPTCHA_PUBLIC_KEY') || !Configuration::get('CAPTCHA_PRIVATE_KEY'))
        ) {
            $this->warning = $this->l('Captcha Module need to be configurated');
        }
        $this->themes = [0 => 'light', 1 => 'dark'];
        $this->dependencies = ['contactform'];
        $this->ps_versions_compliancy = ['min' => '1.7.0.0', 'max' => _PS_VERSION_];

        $this->debugger = new Debugger($this);
    }

    /**
     * Install Module
     * @return bool
     */
    public function install()
    {
        if (!parent::install()
            || !$this->_getInstaller()->install()
        ) {
            return false;
        }

        return true;
    }

    /**
     * Uninstall Module
     * @return bool
     */
    public function uninstall()
    {
        if (!parent::uninstall()
            || !$this->_getInstaller()->uninstall()
        ) {
            return false;
        }

        return true;
    }

    /**
     * @return Installer
     */
    protected function _getInstaller()
    {
        if (null === $this->installer) {
            $this->installer = new Installer($this);
        }
        return $this->installer;
    }

    /**
     * Post Process in back office
     * @return string|void
     */
    public function postProcess()
    {
        if (Tools::isSubmit('SubmitCaptchaConfiguration')) {
            Configuration::updateValue('CAPTCHA_PUBLIC_KEY', Tools::getValue('CAPTCHA_PUBLIC_KEY'));
            Configuration::updateValue('CAPTCHA_PRIVATE_KEY', Tools::getValue('CAPTCHA_PRIVATE_KEY'));
            Configuration::updateValue('CAPTCHA_ENABLE_ACCOUNT', (int)Tools::getValue('CAPTCHA_ENABLE_ACCOUNT'));
            Configuration::updateValue('CAPTCHA_ENABLE_CONTACT', (int)Tools::getValue('CAPTCHA_ENABLE_CONTACT'));
            Configuration::updateValue('CAPTCHA_ENABLE_NEWSLETTER', (int)Tools::getValue('CAPTCHA_ENABLE_NEWSLETTER'));
            Configuration::updateValue('CAPTCHA_FORCE_LANG', Tools::getValue('CAPTCHA_FORCE_LANG'));
            Configuration::updateValue('CAPTCHA_THEME', (int)Tools::getValue('CAPTCHA_THEME'));
            Configuration::updateValue('CAPTCHA_DEBUG', (int)Tools::getValue('CAPTCHA_DEBUG'));

            $this->_html .= $this->displayConfirmation($this->l('Settings updated'));
        }
    }

    /**
     * Module Configuration in Back Office
     * @return string
     */
    public function getContent()
    {
        $this->_html .= $this->debugger->checkComposer();
        $this->_html .= $this->postProcess();
        $this->_html .= $this->renderForm();

        return $this->_html;
    }

    /**
     * Admin Form for module Configuration
     * @return string
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function renderForm()
    {
        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Eicaptcha Configuration'),
                    'icon' => 'icon-cogs'
                ],
                'tabs' => [
                    'general' => $this->l('General configuration'),
                    'advanced' => $this->l('Advanded parameters'),
                ],
                'description' => $this->l('To get your own public and private keys please click on the folowing link')
                    . '<br /><a href="https://www.google.com/recaptcha/intro/index.html" target="_blank">https://www.google.com/recaptcha/intro/index.html</a>',
                'input' => [
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
                                'id' => 'clight',
                                'value' => 0,
                                'label' => $this->l('Light'),
                            ],
                            [
                                'id' => 'cdark',
                                'value' => 1,
                                'label' => $this->l('Dark'),
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
                        'html_content' => '<a href="' . $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name . '&display_debug=1&token=' . Tools::getAdminTokenLite('AdminModules') . '">' . $this->l('Check if module is well installed') . '</a>',
                        'desc' => $this->l('click on this link will reload the page, please go again in tab "advanced parameters" to see the results'),
                        'tab' => 'advanced'
                    ]
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                    'class' => 'button btn btn-default pull-right',
                ]
            ],
        ];

        //Display debug data to help detect issues
        if (Tools::getValue('display_debug')) {
            $fields_form['form']['input'][] = [
                'type' => 'html',
                'name' => 'debug_html',
                'html_content' => $this->debugger->debugModuleInstall(),
                'tab' => 'advanced'
            ];
        }

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ?
            Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->id = 'eicaptcha';
        $helper->submit_action = 'SubmitCaptchaConfiguration';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        ];

        return $helper->generateForm([$fields_form]);
    }

    /**
     * Get config values to hydrate the helperForm
     * @return array
     */
    public function getConfigFieldsValues()
    {
        return [
            'CAPTCHA_PRIVATE_KEY' => Tools::getValue('CAPTCHA_PRIVATE_KEY', Configuration::get('CAPTCHA_PRIVATE_KEY')),
            'CAPTCHA_PUBLIC_KEY' => Tools::getValue('CAPTCHA_PUBLIC_KEY', Configuration::get('CAPTCHA_PUBLIC_KEY')),
            'CAPTCHA_ENABLE_ACCOUNT' => Tools::getValue('CAPTCHA_ENABLE_ACCOUNT', Configuration::get('CAPTCHA_ENABLE_ACCOUNT')),
            'CAPTCHA_ENABLE_CONTACT' => Tools::getValue('CAPTCHA_ENABLE_CONTACT', Configuration::get('CAPTCHA_ENABLE_CONTACT')),
            'CAPTCHA_ENABLE_NEWSLETTER' => Tools::getValue('CAPTCHA_ENABLE_NEWSLETTER', Configuration::get('CAPTCHA_ENABLE_NEWSLETTER')),
            'CAPTCHA_FORCE_LANG' => Tools::getValue('CAPTCHA_FORCE_LANG', Configuration::get('CAPTCHA_FORCE_LANG')),
            'CAPTCHA_THEME' => Tools::getValue('CAPTCHA_THEME', Configuration::get('CAPTCHA_THEME')),
            'CAPTCHA_DEBUG' => Tools::getValue('CAPTCHA_DEBUG', Configuration::get('CAPTCHA_DEBUG')),
        ];
    }

    /**
     * Hook Header
     * @param array $params
     * @return string|void
     */
    public function hookHeader($params)
    {
        //Add Content box to contact form page in order to display captcha
        if ($this->context->controller instanceof ContactController
            && Configuration::get('CAPTCHA_ENABLE_CONTACT') == 1
        ) {

            $this->context->controller->registerJavascript(
                'modules-eicaptcha-contact-form',
                'modules/' . $this->name . '/views/js/eicaptcha-contact-form.js'
            );
        }

        if (($this->context->controller instanceof AuthController && Configuration::get('CAPTCHA_ENABLE_ACCOUNT') == 1) ||
            ($this->context->controller instanceof ContactController && Configuration::get('CAPTCHA_ENABLE_CONTACT') == 1)
        ) {
            $this->context->controller->registerStylesheet(
                'module-eicaptcha',
                'modules/' . $this->name . '/views/css/eicaptcha.css'
            );
            //Dynamic insertion of the content
            $js = '<script type="text/javascript">
            //Recaptcha CallBack Function
            var onloadCallback = function() {
                //Fix captcha box issue in ps 1.7.7
                if ( ! document.getElementById("captcha-box")){
                        var container = document.createElement("div");
                        container.setAttribute("id","captcha-box");
                        if ( null !== document.querySelector(".form-fields") ){
                             document.querySelector(".form-fields").appendChild(container);
                        }
                }
                if ( document.getElementById("captcha-box")){
                    grecaptcha.render("captcha-box", {"theme" : "' . $this->themes[Configuration::get('CAPTCHA_THEME')] . '", "sitekey" : "' . Configuration::get('CAPTCHA_PUBLIC_KEY') . '"});
                } else {
                    console.warn("eicaptcha: unable to add captcha-box placeholder to display captcha ( not an error when form is submited sucessfully )");
                }
            };
            </script>';

            if (($this->context->controller instanceof ContactController && Configuration::get('CAPTCHA_ENABLE_CONTACT') == 1)) {
                $js .= '<script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit&hl=' . Configuration::get('CAPTCHA_FORCE_LANG') . '" async defer></script>';
            }
            return $js;
        }
    }

    /**
     * Add Captcha on the Customer Registration Form
     * @param array $params
     * @return string|void
     */
    public function hookDisplayCustomerAccountForm($params)
    {
        if (Configuration::get('CAPTCHA_ENABLE_ACCOUNT') == 1) {
            $this->context->smarty->assign([
                'publicKey' => Configuration::get('CAPTCHA_PUBLIC_KEY'),
                'captchaforcelang' => Configuration::get('CAPTCHA_FORCE_LANG'),
                'captchatheme' => $this->themes[Configuration::get('CAPTCHA_THEME')]
            ]);
            return $this->display(__FILE__, 'views/templates/hook/hookDisplayCustomerAccountForm.tpl');
        }
    }

    /**
     * Check captcha before submit account
     * Custom hook
     * @param array $params
     * @return boolean|void
     */
    public function hookActionContactFormSubmitCaptcha($params)
    {
        if (Configuration::get('CAPTCHA_ENABLE_ACCOUNT') == 1) {
            return $this->_validateCaptcha();
        }
    }

    /**
     * Check captcha before submit contact form
     * new custom hook
     * @return bool|void
     */
    public function hookActionContactFormSubmitBefore()
    {
        if (Configuration::get('CAPTCHA_ENABLE_CONTACT') == 1) {
            return $this->_validateCaptcha();
        }
    }

    /**
     * New hook to display content for newsletter registration
     * ( Need to override theme template for themes/classic/modules/ps_emailsubscription/views/templates/hook/ps_emailsubscription.tpl
     * @param array $params
     * @return string|void
     * @since 2.1.0
     */
    public function hookDisplayNewsletterRegistration($params)
    {
        if (Configuration::get('CAPTCHA_ENABLE_NEWSLETTER') == 1 && $this->_canUseCaptchaOnNewsletter()) {
            $this->context->smarty->assign('publicKey', Configuration::get('CAPTCHA_PUBLIC_KEY'));
            $this->context->smarty->assign('captchaforcelang', Configuration::get('CAPTCHA_FORCE_LANG'));
            $this->context->smarty->assign('captchatheme', $this->themes[Configuration::get('CAPTCHA_THEME')]);
            return $this->display(__FILE__, 'views/templates/hook/hookDisplayNewsletterRegistration.tpl');
        }
    }

    /**
     * New Hook to validate newsletter registration
     * @param array $params
     * @return void
     * @since 2.1.0
     */
    public function hookActionNewsletterRegistrationBefore($params)
    {
        if (Configuration::get('CAPTCHA_ENABLE_NEWSLETTER') == 1 && $this->_canUseCaptchaOnNewsletter()) {
            if (!$this->_validateCaptcha()) {
                $params['hookError'] = $this->l('Please validate the captcha field before submitting your request');
            }
        }
    }

    /**
     * Validate Captcha
     * @return bool
     */
    protected function _validateCaptcha()
    {
        $context = Context::getContext();
        $captcha = new \ReCaptcha\ReCaptcha(Configuration::get('CAPTCHA_PRIVATE_KEY'));
        $result = $captcha->verify(
            Tools::getValue('g-recaptcha-response'),
            Tools::getRemoteAddr()
        );

        if (!$result->isSuccess()) {
            $errorMessage = $this->l('Please validate the captcha field before submitting your request');
            $this->debugger->log($errorMessage);
            $this->debugger->log(sprintf($this->l('Recaptcha response %s'), print_r($result->getErrorCodes(), true)));
            $context->controller->errors[] = $errorMessage;
            return false;
        }

        $this->debugger->log($this->l('Captcha submited with success'));

        return true;
    }


    /**
     * Define if captcha can be use on newsletter form
     * Needs a recent version of ps_emailsubscription which implements new hooks required
     * @return bool
     */
    protected function _canUseCaptchaOnNewsletter()
    {
        if (Module::isInstalled('ps_emailsubscription')) {
            $emailSubcription = Module::getInstanceByName('ps_emailsubscription');
            if (version_compare('2.6.0', $emailSubcription->version) >= 0) {
                return true;
            }
        }
        return false;
    }
}
