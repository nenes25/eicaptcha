<?php
/**
 * 2007-2018 PrestaShop
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
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    Hennes Hervé <contact@h-hennes.fr>
 *  @copyright 2013-2018 Hennes Hervé
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  http://www.h-hennes.fr/blog/
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class EiCaptcha extends Module
{
    private $_html = '';

    public function __construct()
    {
        $this->author = 'hhennes';
        $this->name = 'eicaptcha';
        $this->tab = 'front_office_features';
        $this->version = '0.4.15';
        $this->need_instance = 1;

        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->l('Ei Captcha');
        $this->description = $this->l('Add a captcha to your website form');

        if ($this->active && (!Configuration::get('CAPTCHA_PUBLIC_KEY') || !Configuration::get('CAPTCHA_PRIVATE_KEY'))) {
            $this->warning = $this->l('Captcha Module need to be configurated');
        }
        $this->themes = array( 0 => 'light', 1 => 'dark');
        $this->ps_versions_compliancy = array('min' => '1.5.0', 'max' => '1.7.0');
    }

    public function install()
    {
        if (!parent::install() 
                || !$this->registerHook('header') 
                || !$this->registerHook('displayCustomerAccountForm') 
                || !$this->registerHook('contactFormAccess') 
                || !Configuration::updateValue('CAPTCHA_ENABLE_ACCOUNT', 0) 
                || !Configuration::updateValue('CAPTCHA_ENABLE_CONTACT', 0) 
                || !Configuration::updateValue('CAPTCHA_THEME', 0)
                || !Configuration::updateValue('CAPTCHA_CONTACTF_BTN_SELECTOR', '#submitMessage')
                || !Configuration::updateValue('CAPTCHA_CONTACTF_INSERT_SELECTOR', '.submit')
        ) {
            return false;
        }

        return true;
    }

    public function uninstall()
    {
        if (!parent::uninstall()) {
            return false;
        }

        if (!Configuration::deleteByName('CAPTCHA_PUBLIC_KEY') 
                || !Configuration::deleteByName('CAPTCHA_PRIVATE_KEY') 
                || !Configuration::deleteByName('CAPTCHA_ENABLE_ACCOUNT')
                || !Configuration::deleteByName('CAPTCHA_ENABLE_CONTACT') 
                || !Configuration::deleteByName('CAPTCHA_FORCE_LANG') 
                || !Configuration::deleteByName('CAPTCHA_THEME')
                || !Configuration::deleteByName('CAPTCHA_CONTACTF_INSERT_SELECTOR')
                || !Configuration::deleteByName('CAPTCHA_CONTACTF_BTN_SELECTOR')
            ) {
            return false;
        }

        return true;
    }

    /**
     * Post Process in back office
     */
    public function postProcess()
    {
        if (Tools::isSubmit('SubmitCaptchaConfiguration')) {
            Configuration::updateValue('CAPTCHA_PUBLIC_KEY', Tools::getValue('CAPTCHA_PUBLIC_KEY'));
            Configuration::updateValue('CAPTCHA_PRIVATE_KEY', Tools::getValue('CAPTCHA_PRIVATE_KEY'));
            Configuration::updateValue('CAPTCHA_ENABLE_ACCOUNT', (int) Tools::getValue('CAPTCHA_ENABLE_ACCOUNT'));
            Configuration::updateValue('CAPTCHA_ENABLE_CONTACT', (int) Tools::getValue('CAPTCHA_ENABLE_CONTACT'));
            Configuration::updateValue('CAPTCHA_FORCE_LANG', Tools::getValue('CAPTCHA_FORCE_LANG'));
            Configuration::updateValue('CAPTCHA_THEME', (int)Tools::getValue('CAPTCHA_THEME'));
            Configuration::updateValue('CAPTCHA_CONTACTF_INSERT_SELECTOR', Tools::getValue('CAPTCHA_CONTACTF_INSERT_SELECTOR'));
            Configuration::updateValue('CAPTCHA_CONTACTF_BTN_SELECTOR', Tools::getValue('CAPTCHA_CONTACTF_BTN_SELECTOR'));
          
            $this->_html .= $this->displayConfirmation($this->l('Settings updated'));
        }
    }

    /**
     * Module Configuration in Back Office
     */
    public function getContent()
    {
        $this->_html .= $this->_checkComposer();
        $this->_html .=$this->postProcess();
        $this->_html .= $this->renderForm();

        return $this->_html;
    }

    /**
     * Admin Form for module Configuration
     */
    public function renderForm()
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Eicaptcha Configuration'),
                    'icon' => 'icon-cogs'
                ),
                'tabs' => array(
                    'general' => $this->l('General configuration'),
                    'advanced' => $this->l('Advanded parameters'),
                ),
                'description' => $this->l('To get your own public and private keys please click on the folowing link').'<br /><a href="https://www.google.com/recaptcha/intro/index.html" target="_blank">https://www.google.com/recaptcha/intro/index.html</a>',
                'input' => array(
		     array(
                        'type' => 'text',
                        'label' => $this->l('Captcha public key (Site key)'),
                        'name' => 'CAPTCHA_PUBLIC_KEY',
			'size'=> 70,
                        'required' => true,
                        'empty_message' => $this->l('Please fill the captcha public key'),
                        'tab' => 'general', 
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Captcha private key (Secret key)'),
                        'name' => 'CAPTCHA_PRIVATE_KEY',
			'size'=> 70,
                        'required' => true,
                        'empty_message' => $this->l('Please fill the captcha private key'),
                        'tab' => 'general',
                    ),
                    array(
                        'type' => 'radio',
                        'label' => $this->l('Enable Captcha for contact form'),
                        'name' => 'CAPTCHA_ENABLE_CONTACT',
                        'required' => true,
                        'class' => 't',
                        'tab' => 'general',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value'=> 1,
                                'label'=> $this->l('Enabled'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value'=> 0,
                                'label'=> $this->l('Disabled'),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'radio',
                        'label' => $this->l('Enable Captcha for account creation'),
                        'name' => 'CAPTCHA_ENABLE_ACCOUNT',
                        'required' => true,
                        'class' => 't',
                        'is_bool' => true,
                        'tab' => 'general',
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value'=> 1,
                                'label'=> $this->l('Enabled'),
                            ),
                            array(
                                'id' => 'active_off',
                                'value'=> 0,
                                'label'=> $this->l('Disabled'),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Force Captcha language'),
                        'hint' => $this->l('Language code ( en-GB | fr | de | de-AT | ... ) - Leave empty for using customers FO language selection'),
                        'desc' => $this->l('For available language codes see: https://developers.google.com/recaptcha/docs/language'),
                        'name' => 'CAPTCHA_FORCE_LANG',
                        'required' => false,
                        'tab' => 'general',
                    ),
                    array(
                        'type' => 'radio',
                        'label' => $this->l('Theme'),
                        'name' => 'CAPTCHA_THEME',
                        'required' => true,
                        'class' => 't',
                        'tab' => 'general',
                        'values' => array(
                            array(
                                'id' => 'clight',
                                'value' => 0,
                                'label' => $this->l('Light'),
                            ),
                            array(
                                'id' => 'cdark',
                                'value' => 1,
                                'label' => $this->l('Dark'),
                            ),
                        ),
                    ),
                     array(
                       'type' => 'html',
                       'name' => 'advanced-warning',
                       'html_content' => '<div class="alert alert-warning">'
                         .$this->l('Use with caution, invalid parameters may made the module not to works properly, check the blog article before (in french)')
                        .': <a href="https://www.h-hennes.fr/blog/2018/02/23/eicaptcha-ajout-dune-configuration-avancees-des-selecteurs" target="_blank">https://www.h-hennes.fr/blog/2018/02/23/eicaptcha-ajout-dune-configuration-avancees-des-selecteurs</a></div>',
                       'tab' => 'advanced' 
                    ),
                     array(
                       'type' => 'text',
                        'label' => $this->l('Contact Form submit button selector'),
                        'hint' => $this->l('Use with caution, invalid parameters may made the module not to works properly'),
                        'desc' => $this->l('For specific theme you can edit css selector of the captcha submit button'),
                        'name' => 'CAPTCHA_CONTACTF_BTN_SELECTOR',
                        'required' => false,
                        'tab' => 'advanced', 
                    ),
                    array(
                       'type' => 'text',
                        'label' => $this->l('Custom css selection contact form'),
                        'hint' => $this->l('Use with caution, invalid parameters may made the module not to works properly'),
                        'desc' => $this->l('For specific theme you can edit css selector where to add captcha container before'),
                        'name' => 'CAPTCHA_CONTACTF_INSERT_SELECTOR',
                        'required' => false,
                        'tab' => 'advanced', 
                    ),
                    array(
                        'type' => 'html',
                        'label' => $this->l('Check module installation'),
                        'name' => 'enable_debug_html',
                        'html_content' => '<a class="btn btn-info" href="'.$this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name.'&display_debug=1&token='.Tools::getAdminTokenLite('AdminModules').'">'.$this->l('Check if module is well installed').'</a>',
                        'desc' => $this->l('click on this link will reload the page, please go again in tab "advanced parameters" to see the results'),
                        'tab' => 'advanced'
                    )
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'class' => 'button btn btn-default pull-right',
                )
            ),
            );

        //Display debug data to help detect issues
        if ( Tools::getValue('display_debug')){
            $fields_form['form']['input'][] = array(
                'type' => 'html',
                'name' => 'debug_html',
                'html_content' => $this->_debugModuleInstall(),
                'tab' => 'advanced'
            );
        }
        
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table =  $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $this->fields_form = array();
        $helper->id = (int)Tools::getValue('id_carrier');
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'SubmitCaptchaConfiguration';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
			'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );

        return $helper->generateForm(array($fields_form));
    }
	
	/**
     * Get config values to hydrate the helperForm
     */
    public function getConfigFieldsValues()
    {
        return array(
            'CAPTCHA_PRIVATE_KEY' => Tools::getValue('CAPTCHA_PRIVATE_KEY', Configuration::get('CAPTCHA_PRIVATE_KEY')),
            'CAPTCHA_PUBLIC_KEY' => Tools::getValue('CAPTCHA_PUBLIC_KEY', Configuration::get('CAPTCHA_PUBLIC_KEY')),
            'CAPTCHA_ENABLE_ACCOUNT' => Tools::getValue('CAPTCHA_ENABLE_ACCOUNT', Configuration::get('CAPTCHA_ENABLE_ACCOUNT')),
            'CAPTCHA_ENABLE_CONTACT' => Tools::getValue('CAPTCHA_ENABLE_CONTACT', Configuration::get('CAPTCHA_ENABLE_CONTACT')),
            'CAPTCHA_FORCE_LANG' => Tools::getValue('CAPTCHA_FORCE_LANG', Configuration::get('CAPTCHA_FORCE_LANG')),
            'CAPTCHA_THEME' => Tools::getValue('CAPTCHA_THEME', Configuration::get('CAPTCHA_THEME')),
            'CAPTCHA_CONTACTF_INSERT_SELECTOR' => Tools::getValue('CAPTCHA_CONTACTF_INSERT_SELECTOR', Configuration::get('CAPTCHA_CONTACTF_INSERT_SELECTOR')),
            'CAPTCHA_CONTACTF_BTN_SELECTOR' => Tools::getValue('CAPTCHA_CONTACTF_BTN_SELECTOR', Configuration::get('CAPTCHA_CONTACTF_BTN_SELECTOR')),
        );
    }
	
    /**
     * Get lang settings
     * @return string
     */
    public function langSettings() {
        if (!Configuration::get('CAPTCHA_FORCE_LANG')) {
            return $iso_code = $this->context->language->iso_code;
        } else {
            return $iso_code = Configuration::get('CAPTCHA_FORCE_LANG');
        }
    }

    /**
     * Hook Header
     */
    public function hookHeader($params)
    {
	$iso_code = $this->langSettings();
        
        //Display the captcha on the contact page if it's enabled
        if ($this->context->controller instanceof ContactController && Configuration::get('CAPTCHA_ENABLE_CONTACT') == 1) {
            return $this->displayCaptchaContactForm();
        }

        //Add Javascript in product page in order to display the captcha for the module "sendToAFriend" and "ProductsComments"
        if ($this->context->controller instanceof ProductController) {
            $html = '<script type="text/javascript">
						var checkCaptchaUrl ="'._MODULE_DIR_.$this->name.'/eicaptcha-ajax.php";
						var RecaptachKey = "'.Configuration::get('CAPTCHA_PUBLIC_KEY').'";
						var RecaptchaTheme = "'.$this->themes[Configuration::get('CAPTCHA_THEME')].'";
					</script>
					<script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit&hl='.$iso_code.'" async defer></script>
					<script type="text/javascript" src="'.$this->_path.'/views/js/eicaptcha-modules.js"></script>';

            return $html;
        }
    }

    /**
     * Ajax Actions of the module
     * (Error displaying with smarty template)
     */
    public function hookAjaxCall()
    {
        $action = Tools::getValue('action');

        if ($action == 'display_captcha_error') {
            $this->context->smarty->assign('errors', array($this->l('Please validate the captcha field before submitting your request')));
            $error_block = trim(preg_replace("#\n#", '', $this->context->smarty->fetch(_PS_THEME_DIR_.'errors.tpl')));
            echo $error_block;
        }
    }

    /**
     * Add Captcha on the Customer Registration Form
     */
    public function hookDisplayCustomerAccountForm($params)
    {
	$iso_code = $this->langSettings();
        
        if (Configuration::get('CAPTCHA_ENABLE_ACCOUNT') == 1) {
            $publickey = Configuration::get('CAPTCHA_PUBLIC_KEY');

            if (_PS_VERSION_ > '1.6') {
                $error_selector = '.alert';
                $form_selector = '#account-creation_form';
                $prestashop_version = '16';
            } else {
                $this->context->controller->addCSS($this->_path.'/views/css/eicaptcha.css');
                $error_selector = '.error';
                $form_selector = '#account-creation_form';
                $prestashop_version = '15';
            }

            $this->context->controller->addJS($this->_path.'views/js/eicaptcha.js');
			
            $this->context->smarty->assign('publicKey', $publickey);
            $this->context->smarty->assign('waiting_message', $this->l('Please wait during captcha check'));
            $this->context->smarty->assign('checkCaptchaUrl', _MODULE_DIR_.$this->name.'/eicaptcha-ajax.php');
            $this->context->smarty->assign('errorSelector', $error_selector);
            $this->context->smarty->assign('formSelector', $form_selector);
            $this->context->smarty->assign('prestashopVersion', $prestashop_version);
            $this->context->smarty->assign('captchaforcelang', $iso_code);
            $this->context->smarty->assign('captchatheme', $this->themes[Configuration::get('CAPTCHA_THEME')]);

            return $this->display(__FILE__, 'hookDisplayCustomerAccountForm.tpl');
        }
    }

    /**
     * Display Captcha on the contact Form Page
     */
    private function displayCaptchaContactForm()
    {
	$iso_code = $this->langSettings();
        //Css class depends from Prestashop version
        if (_PS_VERSION_ > '1.6') {
            $error_class = 'alert';
            $form_class = 'contact-form-box';
        } else {
            $error_class = 'error';
            $form_class = 'std';
        }
        
        //Dynamic insertion of the content
        $js = '<script type="text/javascript">

            $(document).ready(function(){

               //Add div where the captcha will be displayed
               $("'.Configuration::get('CAPTCHA_CONTACTF_INSERT_SELECTOR').'").before("<div id=\"captcha-box\"></div>");

               //Manage form submit
                $("'.Configuration::get('CAPTCHA_CONTACTF_BTN_SELECTOR').'").click(function(){
                    //If no response we display an error
                    if ( ! grecaptcha.getResponse() ) {
					    $.ajax({
								method : "POST",
								url : "'._MODULE_DIR_.$this->name.'/eicaptcha-ajax.php",
								data : "action=display_captcha_error",
								success : function(msg){
									$(".'.$error_class.'").remove();
									$("form.'.$form_class.'").before(msg);
								}
							});

                        return false;
                    }
                });
            });

            //Recaptcha CallBack Function
            var onloadCallback = function() {grecaptcha.render("captcha-box", {"theme" : "'.$this->themes[Configuration::get('CAPTCHA_THEME')].'", "sitekey" : "'.Configuration::get('CAPTCHA_PUBLIC_KEY').'"});};
            </script>';

        $js .= '<script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit&hl='.$iso_code.'" async defer></script>';

        return $js;
    }

    /* return true|false : whether to allow access (before postProcess()) */
    public function hookContactFormAccess() {
        if (! Tools::isSubmit('submitMessage')) {
            // postProcess will take care of this
            return 1;
        }

        // no restriction
        if (! Configuration::get('CAPTCHA_ENABLE_CONTACT', 0)) {
            return 1;
        }

        require_once(__DIR__ . '/vendor/autoload.php');
        $captcha = new \ReCaptcha\ReCaptcha(Configuration::get('CAPTCHA_PRIVATE_KEY'));
        $result = $captcha->verify(Tools::getValue('g-recaptcha-response'),
                                   Tools::getRemoteAddr());

        if (! $result->isSuccess()) {
            $this->context->controller->errors[] = Tools::displayError('incorrect response to CAPTCHA challenge. Please try again.');
            return 0;
        }

        return 1;
    }


    /**
     * Check if needed composer directory is present
     */
    protected function _checkComposer()
    {
        if (!is_dir(dirname(__FILE__).'/vendor')) {
            $errorMessage = $this->l('This module need composer to work, please go into module directory %s and run composer install or dowload and install latest release from %s');
            return $this->displayError(
                    sprintf($errorMessage, dirname(__FILE__),
                        'https://github.com/nenes25/eicaptcha/releases')
            );
        }

        return '';
    }

    /**
     * Debug module installation
     */
    protected function _debugModuleInstall()
    {
        $errors  = array();
        $success = array();
        //Check if module version is compatible with current PS version
        if (!$this->checkCompliancy()) {
            $errors[] = 'the module is not compatible with your version';
        } else {
            $success[] = 'the module is compatible with your version';
        }
        //Check if module is well hooked on all necessary hooks
        $modulesHooks = array('header', 'displayCustomerAccountForm', 'contactFormAccess');
        foreach ($modulesHooks as $hook) {
            if (!$this->isRegisteredInHook($hook)) {
                $errors[] = 'the module is not registered in hook '.$hook;
            } else {
                $success[] = 'the module is well registered in hook '.$hook;
            }
        }
        
        //Check if override are disabled in configuration
        if (Configuration::get('PS_DISABLE_OVERRIDES') == 1) {
            $errors[] = 'Overrides are disable on your website';
        } else {
            $success[] = 'Overrides are enabled on your website';
        }
        //Check if file overrides exists
        if (!file_exists(_PS_OVERRIDE_DIR_.'controllers/front/ContactController.php')) {
            $errors[] = 'ContactController.php override does not exists';
        } else {
            $success[] = 'ContactController.php override exists';
        }
        //@Todo : check the content of the override file
        //Check if file override is written in class_index.php files
        if (file_exists(_PS_CACHE_DIR_.'class_index.php')) {
            $classesArray = (include _PS_CACHE_DIR_.'class_index.php' );
            if ($classesArray['ContactController']['path'] != 'override/controllers/front/ContactController.php') {
                $errors[] = 'ContactController.php override is not present in class_index.php <br />'
                    . 'Please remove file cache/class_index.php in order to fix the issue';
            } else {
                $success[] = 'ContactController.php override is present in class_index.php';
            }
        } else {
            $errors[] = 'no class_index.php found';
        }
        //@Todo Check if log file can be filled
        //Display errors
        if (sizeof($errors)) {
            $errorsHtml = '<div class="alert alert-warning"> Errors <br />'
                .'<ul>';
            foreach ($errors as $error) {
                $errorsHtml .= '<li>'.$error.'</li>';
            }
            $errorsHtml .= '</ul></div>';
        }
        //Display success
        if (sizeof($success)) {
            $successHtml = '<div class="alert alert-success"> Success <br />'
                .'<ul>';
            foreach ($success as $msg) {
                $successHtml .= '<li>'.$msg.'</li>';
            }
            $successHtml .= '</ul></div>';
        }
        //Additionnal informations
        $informations = '<div class="alert alert-info">Aditionnal informations <br />'
            .'<ul>';
        //PS version
        $informations .= '<li>Prestashop version <strong>'._PS_VERSION_.'</strong></li>';
        //Theme
        $informations .= '<li>Theme name <strong>'._THEME_NAME_.'</strong></li>';
        //Check php version
        $informations .= '<li>Php version <strong>'.phpversion().'</strong></li>';
        $informations .= '</ul></div>';
        return $errorsHtml.' '.$successHtml.' '.$informations;
    }
}
