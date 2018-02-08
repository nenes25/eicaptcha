<?php
/**
 * 2007-2016 PrestaShop
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
 *  @copyright 2013-2015 Hennes Hervé
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
        $this->version = '0.5.0';
        $this->need_instance = 1;

        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->l('Ei Captcha');
        $this->description = $this->l('Add a captcha to your website form');

        if ($this->active && (!Configuration::get('CAPTCHA_PUBLIC_KEY') || !Configuration::get('CAPTCHA_PRIVATE_KEY'))) {
            $this->warning = $this->l('Captcha Module need to be configurated');
        }
        $this->themes = array( 0 => 'light', 1 => 'dark');
        $this->ps_versions_compliancy = array('min' => '1.6.1', 'max' => '1.7.0');
    }

    public function install()
    {
        if (!parent::install() || !$this->registerHook('header') 
                || !$this->registerHook('displayCustomerAccountForm') 
                || !$this->registerHook('contactFormAccess') 
                || !Configuration::updateValue('CAPTCHA_ENABLE_ACCOUNT', 0) 
                || !Configuration::updateValue('CAPTCHA_ENABLE_CONTACT', 0)
                || !Configuration::updateValue('CAPTCHA_THEME', 0)
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

        if (!Configuration::deleteByName('CAPTCHA_PUBLIC_KEY') || !Configuration::deleteByName('CAPTCHA_PRIVATE_KEY') || !Configuration::deleteByName('CAPTCHA_ENABLE_ACCOUNT')
            || !Configuration::deleteByName('CAPTCHA_ENABLE_CONTACT') || !Configuration::deleteByName('CAPTCHA_FORCE_LANG') || !Configuration::deleteByName('CAPTCHA_THEME')
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
            Configuration::updateValue('CAPTCHA_ENABLE_PRODUCTCOMMENTS', (int) Tools::getValue('CAPTCHA_ENABLE_PRODUCTCOMMENTS'));
            Configuration::updateValue('CAPTCHA_ENABLE_SENDTOAFRIEND', (int) Tools::getValue('CAPTCHA_ENABLE_SENDTOAFRIEND'));
            Configuration::updateValue('CAPTCHA_FORCE_LANG', Tools::getValue('CAPTCHA_FORCE_LANG'));
            Configuration::updateValue('CAPTCHA_THEME', (int)Tools::getValue('CAPTCHA_THEME'));

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
                'description' => $this->l('To get your own public and private keys please click on the folowing link').'<br /><a href="https://www.google.com/recaptcha/intro/index.html" target="_blank">https://www.google.com/recaptcha/intro/index.html</a>',
                'input' => array(
					 array(
                        'type' => 'text',
                        'label' => $this->l('Captcha public key (Site key)'),
                        'name' => 'CAPTCHA_PUBLIC_KEY',
                        'required' => true,
                        'empty_message' => $this->l('Please fill the captcha public key'),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Captcha private key (Secret key)'),
                        'name' => 'CAPTCHA_PRIVATE_KEY',
                        'required' => true,
                        'empty_message' => $this->l('Please fill the captcha private key'),
                    ),
                    array(
                        'type' => 'radio',
                        'label' => $this->l('Enable Captcha for contact form'),
                        'name' => 'CAPTCHA_ENABLE_CONTACT',
                        'required' => true,
                        'class' => 't',
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
                    ),
                    array(
                        'type' => 'radio',
                        'label' => $this->l('Theme'),
                        'name' => 'CAPTCHA_THEME',
                        'required' => true,
                        'is_bool' => true,
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
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'class' => 'button btn btn-default pull-right',
                )
            ),
            );
        
            //Sendtoafriend module
            if ( Module::isInstalled('sendtoafriend') && Module::isEnabled('sendtoafriend')) {

                $fields_form['form']['input'][] =  array(
                            'type' => 'radio',
                            'label' => $this->l('Enable Captcha for sendtoafriend module'),
                            'name' => 'CAPTCHA_ENABLE_SENDTOAFRIEND',
                            'required' => true,
                            'class' => 't',
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
                        );
            }
            
            //Productcomments module
            if ( Module::isInstalled('productcomments') && Module::isEnabled('productcomments')) {

                $fields_form['form']['input'][] =  array(
                        'type' => 'radio',
                        'label' => $this->l('Enable Captcha for productcomment module'),
                        'name' => 'CAPTCHA_ENABLE_PRODUCTCOMMENTS',
                        'required' => true,
                        'class' => 't',
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
                    );
            }
             
            //Warning for productcomment or sendtoafriend and theme
            if ( 
                  ( ( Module::isInstalled('productcomments') && Module::isEnabled('productcomments') )
                    ||  ( Module::isInstalled('sendtoafriend') && Module::isEnabled('sendtoafriend') )
                  )
                  && _THEME_NAME_ != 'default-bootstrap'
               ){
                $instructionsUrl = '<a href="https://github.com/nenes25/eicaptcha/blob/master/install-sendtoafriend.md" target="_blank">github</a>';
                $fields_form['form']['input'][] =  array(
                    'type' => 'html',
                    'name' => 'theme_warning',
                    'html_content' => '<div class="alert alert-warning">'
                    .sprintf($this->l('Warning : productscomments and/or sendtoafriend module captcha is only tested with default theme, please read instructions on %s'),$instructionsUrl).
                    '</div>',
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
            'CAPTCHA_ENABLE_PRODUCTCOMMENTS' => Tools::getValue('CAPTCHA_ENABLE_PRODUCTCOMMENTS', Configuration::get('CAPTCHA_ENABLE_PRODUCTCOMMENTS')),
            'CAPTCHA_ENABLE_SENDTOAFRIEND' => Tools::getValue('CAPTCHA_ENABLE_SENDTOAFRIEND', Configuration::get('CAPTCHA_ENABLE_SENDTOAFRIEND')),
            'CAPTCHA_FORCE_LANG' => Tools::getValue('CAPTCHA_FORCE_LANG', Configuration::get('CAPTCHA_FORCE_LANG')),
            'CAPTCHA_THEME' => Tools::getValue('CAPTCHA_THEME', Configuration::get('CAPTCHA_THEME')),
        );
    }
	
    /**
     * Get lang settings
     * @return string
     */
    public function langSettings() {
        if (empty(Configuration::get('CAPTCHA_FORCE_LANG'))) {
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

            //Send to a friend functionnality
            if ( Configuration::get('CAPTCHA_ENABLE_SENDTOAFRIEND')) {                
                //Remove initial js of module sendtoafriend
                $this->context->controller->removeJS(_THEME_JS_DIR_.'modules/sendtoafriend/sendtoafriend.js');
                //Replace by a new specific one + new css file
                $this->context->controller->addJs($this->_path.'views/js/sendtoafriend.js');
                $this->context->controller->addCss($this->_path.'views/css/sendtoafriend.css');
            }

            //ProductComment functionnality
            if ( Configuration::get('CAPTCHA_ENABLE_PRODUCTCOMMENTS')) {
                //Remove initial js of module sendtoafriend
                $this->context->controller->removeJS(_THEME_JS_DIR_.'modules/productcomments/productcomments.js');
                //Replace by a new specific one + new css file
                $this->context->controller->addJs($this->_path.'views/js/productcomments.js');
                $this->context->controller->addCss($this->_path.'views/css/productcomments.css');
            }

            
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
        require_once(__DIR__.'/vendor/autoload.php');

        //Display error messsage
        if ($action == 'display_captcha_error') {
            $this->context->smarty->assign('errors',
                array($this->l('Please validate the captcha field before submitting your request')));
            $error_block = trim(preg_replace("#\n#", '',
                    $this->context->smarty->fetch(_PS_THEME_DIR_.'errors.tpl')));
            echo $error_block;
        }

        //Send message to friend, check captcha before sending request to module
        if ($action == 'sendToMyFriend') {
            $captcha = new \ReCaptcha\ReCaptcha(Configuration::get('CAPTCHA_PRIVATE_KEY'));
            $result  = $captcha->verify(Tools::getValue('g-recaptcha-response'),
                Tools::getRemoteAddr());

            if (!$result->isSuccess()) {
                return '0';
            } else {
                $this->_sendToAFriendMessage();
            }
        }

        if ( $action == 'add_comment'){
               $captcha = new \ReCaptcha\ReCaptcha(Configuration::get('CAPTCHA_PRIVATE_KEY'));
            $result  = $captcha->verify(Tools::getValue('g-recaptcha-response'),
                Tools::getRemoteAddr());

            if (!$result->isSuccess()) {
                return '0';
            } else {
                $this->_AddComment();
            }
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
               $(".submit").before("<div id=\"captcha-box\"></div>");

               //Manage form submit
                $("#submitMessage").click(function(){
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
            $this->errors[] = Tools::displayError('incorrect response to CAPTCHA challenge. Please try again.');
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
     * Send Message if Captcha is filled
     */
    protected function _sendToAFriendMessage()
    {
        include_once(_PS_MODULE_DIR_.'sendtoafriend/sendtoafriend.php');

        $module = new SendToAFriend();

        if (Module::isEnabled('sendtoafriend') && Tools::getValue('action') == 'sendToMyFriend'
            && Tools::getValue('secure_key') == $module->secure_key) {
            // Retrocompatibilty with old theme
            if ($friend = Tools::getValue('friend')) {
                $friend = Tools::jsonDecode($friend, true);

                foreach ($friend as $key => $value) {
                    if ($value['key'] == 'friend_name')
                            $friendName = $value['value'];
                    elseif ($value['key'] == 'friend_email')
                            $friendMail = $value['value'];
                    elseif ($value['key'] == 'id_product')
                            $id_product = $value['value'];
                }
            }
            else {
                $friendName = Tools::getValue('name');
                $friendMail = Tools::getValue('email');
                $id_product = Tools::getValue('id_product');
            }

            if (!$friendName || !$friendMail || !$id_product) die('0');

            $isValidEmail = Validate::isEmail($friendMail);
            $isValidName  = $module->isValidName($friendName);

            if (false === $isValidName || false === $isValidEmail) {
                die('0');
            }

            /* Email generation */
            $product     = new Product((int) $id_product, false,
                $module->context->language->id);
            $productLink = $module->context->link->getProductLink($product);
            $customer    = $module->context->cookie->customer_firstname ? $module->context->cookie->customer_firstname.' '.$module->context->cookie->customer_lastname
                    : $module->l('A friend', 'sendtoafriend_ajax');

            $templateVars = array(
                '{product}' => $product->name,
                '{product_link}' => $productLink,
                '{customer}' => $customer,
                '{name}' => Tools::safeOutput($friendName)
            );

            /* Email sending */
            if (!Mail::Send((int) $module->context->cookie->id_lang,
                    'send_to_a_friend',
                    sprintf(Mail::l('%1$s sent you a link to %2$s',
                            (int) $module->context->cookie->id_lang), $customer,
                        $product->name), $templateVars, $friendMail, null,
                    ($module->context->cookie->email ? $module->context->cookie->email
                            : null),
                    ($module->context->cookie->customer_firstname ? $module->context->cookie->customer_firstname.' '.$module->context->cookie->customer_lastname
                            : null), null, null,
                    _PS_MODULE_DIR_.'sendtoafriend/mails/')) die('0');
            die('1');
        }
        die('0');
    }

    /**
     * Add Comment if captcha is filled
     */
    protected function _AddComment()
	{
		$module_instance = new ProductComments();

		$result = true;
		$id_guest = 0;
		$id_customer = $this->context->customer->id;
		if (!$id_customer)
			$id_guest = $this->context->cookie->id_guest;

		$errors = array();
		// Validation
		if (!Validate::isInt(Tools::getValue('id_product')))
			$errors[] = $module_instance->l('Product ID is incorrect', 'default');
		if (!Tools::getValue('title') || !Validate::isGenericName(Tools::getValue('title')))
			$errors[] = $module_instance->l('Title is incorrect', 'default');
		if (!Tools::getValue('content') || !Validate::isMessage(Tools::getValue('content')))
			$errors[] = $module_instance->l('Comment is incorrect', 'default');
		if (!$id_customer && (!Tools::isSubmit('customer_name') || !Tools::getValue('customer_name') || !Validate::isGenericName(Tools::getValue('customer_name'))))
			$errors[] = $module_instance->l('Customer name is incorrect', 'default');
		if (!$this->context->customer->id && !Configuration::get('PRODUCT_COMMENTS_ALLOW_GUESTS'))
			$errors[] = $module_instance->l('You must be connected in order to send a comment', 'default');
		if (!count(Tools::getValue('criterion')))
			$errors[] = $module_instance->l('You must give a rating', 'default');

		$product = new Product(Tools::getValue('id_product'));
		if (!$product->id)
			$errors[] = $module_instance->l('Product not found', 'default');

		if (!count($errors))
		{
			$customer_comment = ProductComment::getByCustomer(Tools::getValue('id_product'), $id_customer, true, $id_guest);
			if (!$customer_comment || ($customer_comment && (strtotime($customer_comment['date_add']) + (int)Configuration::get('PRODUCT_COMMENTS_MINIMAL_TIME')) < time()))
			{

				$comment = new ProductComment();
				$comment->content = strip_tags(Tools::getValue('content'));
				$comment->id_product = (int)Tools::getValue('id_product');
				$comment->id_customer = (int)$id_customer;
				$comment->id_guest = $id_guest;
				$comment->customer_name = Tools::getValue('customer_name');
				if (!$comment->customer_name)
					$comment->customer_name = pSQL($this->context->customer->firstname.' '.$this->context->customer->lastname);
				$comment->title = Tools::getValue('title');
				$comment->grade = 0;
				$comment->validate = 0;
				$comment->save();

				$grade_sum = 0;
				foreach(Tools::getValue('criterion') as $id_product_comment_criterion => $grade)
				{
					$grade_sum += $grade;
					$product_comment_criterion = new ProductCommentCriterion($id_product_comment_criterion);
					if ($product_comment_criterion->id)
						$product_comment_criterion->addGrade($comment->id, $grade);
				}

				if (count(Tools::getValue('criterion')) >= 1)
				{
					$comment->grade = $grade_sum / count(Tools::getValue('criterion'));
					// Update Grade average of comment
					$comment->save();
				}
				$result = true;
				Tools::clearCache(Context::getContext()->smarty, $this->getTemplatePath('productcomments-reviews.tpl'));
			}
			else
			{
				$result = false;
				$errors[] = $module_instance->l('Please wait before posting another comment', 'default').' '.Configuration::get('PRODUCT_COMMENTS_MINIMAL_TIME').' '.$module_instance->l('seconds before posting a new comment', 'default');
			}
		}
		else
			$result = false;

		die(Tools::jsonEncode(array(
			'result' => $result,
			'errors' => $errors
		)));
	}
}