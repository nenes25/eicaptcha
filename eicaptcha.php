<?php
/**
* 2007-2014 PrestaShop
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

if (!defined('_PS_VERSION_'))
	exit;

class EiCaptcha extends Module
{

	private $html = '';
        
	public function __construct()
	{
		$this->author = 'hhennes';
		$this->name = 'eicaptcha';
		$this->tab = 'front_office_features';
		$this->version = '0.3.1';
		$this->need_instance = 1;

		parent::__construct();

		$this->displayName = $this->l('Ei Captcha');
		$this->description = $this->l('Add a captcha to your website form');

		if ($this->active && (!Configuration::get('CAPTCHA_PUBLIC_KEY') || !Configuration::get('CAPTCHA_PRIVATE_KEY') ))
			$this->warning = $this->l('Captcha Module need to be configurated');

		/* Backward compatibility */
		if (_PS_VERSION_ < '1.5')
			require(_PS_MODULE_DIR_.$this->name.'/backward_compatibility/backward.php');
	}

	public function install()
	{
		if (!parent::install() || !$this->registerHook('header') || !$this->registerHook('displayCustomerAccountForm')
				|| !Configuration::updateValue('CAPTCHA_ENABLE_ACCOUNT', 0) || !Configuration::updateValue('CAPTCHA_API_VERSION', 1))
			return false;

		return true;
	}

	public function uninstall()
	{
		if (!parent::uninstall())
			return false;

		if (!Configuration::deleteByName('CAPTCHA_PUBLIC_KEY') || !Configuration::deleteByName('CAPTCHA_PRIVATE_KEY')
				|| !Configuration::deleteByName('CAPTCHA_ENABLE_ACCOUNT') || !Configuration::deleteByName('CAPTCHA_API_VERSION'))
			return false;

		return true;
	}

	public function postProcess()
	{
		if (Tools::isSubmit('SubmitCaptchaConfiguration'))
		{
			Configuration::updateValue('CAPTCHA_PUBLIC_KEY', Tools::getValue('captcha_public_key'));
			Configuration::updateValue('CAPTCHA_PRIVATE_KEY', Tools::getValue('captcha_private_key'));
			Configuration::updateValue('CAPTCHA_ENABLE_ACCOUNT', (int)Tools::getValue('captcha_enable_account'));
            Configuration::updateValue('CAPTCHA_API_VERSION', (int)Tools::getValue('captcha_api_version'));
		}
	}

	public function getContent()
	{
		$this->postProcess();
		$this->_html .= '<fieldset>
					<legend>'.$this->l('Captcha Configuration').'</legend>
					<h2>'.$this->l('Captcha Configuration').'</h2>
					<p>'.$this->l('To get your own public and private keys please click on the folowing link').'<br />
					<a href="https://www.google.com/recaptcha/admin#whyrecaptcha" target="_blank">https://www.google.com/recaptcha/admin#whyrecaptcha</a>
					</p>
					<br />
					<form name="captcha_configuration" method="post" action="'.$_SERVER['REQUEST_URI'].'">
					<br />
                    <label for="captcha_enable_account">'.$this->l('Captcha Api Version').'</label>
					 <br class="clear" />
					 <div>
					 <label>1</label>
					 <input type="radio" name="captcha_api_version" value="1" ';
                                        if (Configuration::get('CAPTCHA_API_VERSION') == 1)
                                                $this->_html .= 'checked="checked"';
                                        $this->_html .= '/>	
					 <br class="clear" />
					 <label>2</label>
					 <input type="radio" name="captcha_api_version" value="2" ';
                                        if (Configuration::get('CAPTCHA_API_VERSION') == 2)
                                                $this->_html .= 'checked="checked"';
                                        $this->_html .= '/>
					 </div>
					<br />
					<br class="clear" />
					 <label for="captcha_private_key">'.$this->l('Private Key').'</label>
					 <input type="text" name="captcha_private_key" value="'.Configuration::get('CAPTCHA_PRIVATE_KEY').'" />
					 <br />
					 <br class="clear" />
					 <label for="captcha_public_key">'.$this->l('public Key').'</label>
					 <input type="text" name="captcha_public_key" value="'.Configuration::get('CAPTCHA_PUBLIC_KEY').'" />
					 <br class="clear" />
					 <label for="captcha_enable_account">'.$this->l('Enable Captcha for account creation').'</label>
					 <br class="clear" />
					 <div>
					 <label>'.$this->l('Yes').'</label>
					 <input type="radio" name="captcha_enable_account" value="1" ';
                                        if (Configuration::get('CAPTCHA_ENABLE_ACCOUNT') == 1)
                                                $this->_html .= 'checked="checked"';
                                        $this->_html .= '/>	
					 <br class="clear" />
					 <label>'.$this->l('No').'</label>
					 <input type="radio" name="captcha_enable_account" value="0" ';
                                        if (Configuration::get('CAPTCHA_ENABLE_ACCOUNT') == 0)
                                                $this->_html .= 'checked="checked"';
                                        $this->_html .= '/>
					 </div>
					<br />
					<br class="clear" />
					<input type="submit" name="SubmitCaptchaConfiguration" class="button" value="'.$this->l('Send').'" style="margin-left:200px"/>
					</form>
			</fieldset>';

		return $this->_html;
	}

	/**
	 * Hook Header pour le formulaire de contact
	 */
	public function hookHeader($params)
	{
		//Récupération de la page//controller courant
		$contact_form_controller = false;
		$page_name = false;

		//Version 1.5
		if (_PS_VERSION_ > '1.5')
		{
			if ($this->context->controller instanceof ContactController)
				$contact_form_controller = true;
		}
		//Versions 1.4
		else
		{
			/* Version >= 1.4.9 */
			if (version_compare(_PS_VERSION_, '1.4.9.0', '>='))
				$page_name = $this->context->smarty->getTemplateVars('page_name');
			else
			/* Version < 1.4.9 */
				$page_name = $this->context->smarty->get_template_vars('page_name');
		}

		if ($page_name == 'contact-form' || $contact_form_controller) {
                    if ( Configuration::get('CAPTCHA_API_VERSION') == 1 )
			return $this->displayCaptchaContactForm();
                    else
                        return $this->displayCaptachContactFormApiV2 ();
                }
	}

	/**
	 * Actions ajax du module ( Prestashop v 1.4 )
	 */
	public function hookAjaxCall()
	{
		$action = Tools::getValue('action');
		require_once(dirname(__FILE__).'/lib/recaptchalib.php');

		if ($action == 'validate_captcha')
		{

			$recaptcha_challenge_field = Tools::getValue('recaptcha_challenge_field');
			$recaptcha_response_field = Tools::getValue('recaptcha_response_field');

			$captcha_check = recaptcha_check_answer(
					Configuration::get('CAPTCHA_PRIVATE_KEY'), $_SERVER['REMOTE_ADDR'], $recaptcha_challenge_field, $recaptcha_response_field
			);

			if ($captcha_check->is_valid != 1)
			{
				//Assignation de l'erreur au template
				$this->context->smarty->assign('errors', array($this->l('Please validate the captcha field before submitting your request')));
				$error_block = trim(preg_replace("#\n#", '', $this->context->smarty->fetch(_PS_THEME_DIR_.'errors.tpl')));
				echo $error_block;
			}
			else
				return '';
		}
                
		//Fonction d'Affichage de l'erreur pour l'API V2
		if ( $action == 'display_captcha_error') {
			//Assignation de l'erreur au template
			$this->context->smarty->assign('errors', array($this->l('Please validate the captcha field before submitting your request')));
			$error_block = trim(preg_replace("#\n#", '', $this->context->smarty->fetch(_PS_THEME_DIR_.'errors.tpl')));
			echo $error_block;
		}
                
	}

	/**
	 * Rajout des champs au formulaire de création de compte
	 */
	public function hookDisplayCustomerAccountForm($params)
	{
		if (Configuration::get('CAPTCHA_ENABLE_ACCOUNT') == 1)
		{
			//Récupération de la clé publique
			$publickey = Configuration::get('CAPTCHA_PUBLIC_KEY');
			
			if (_PS_VERSION_ > '1.6')
			{
				$error_selector = '.alert';
				$form_selector = '#account-creation_form';
				$prestashop_version = '16';
			}
			else
			{
				$this->context->controller->addCSS($this->_path.'/css/eicaptcha.css');
				$error_selector = '.error';
				$form_selector = '#account-creation_form';
				$prestashop_version = '15';
			}
			
			//Assignation des variables nécessaires au bon fonctionnement du module
			$this->context->smarty->assign('publicKey', $publickey);
			$this->context->smarty->assign('waiting_message', $this->l('Please wait during captcha check'));
			$this->context->smarty->assign('checkCaptchaUrl', _MODULE_DIR_.$this->name.'/eicaptcha-ajax.php');
			$this->context->smarty->assign('errorSelector', $error_selector);
			$this->context->smarty->assign('formSelector', $form_selector);
			$this->context->smarty->assign('prestashopVersion', $prestashop_version);
			
			//Version API 1
			if ( Configuration::get('CAPTCHA_API_VERSION') == 1 )
			{
				
				//Génération du template
				require_once(dirname(__FILE__).'/lib/recaptchalib.php');

				//Recaptcha n'est pas compatible ajax à cause du document.write, on va donc créer un fichier qui corrige cette erreur
				if (!is_file(dirname(__FILE__).'/js/google_recaptcha_ajax.js'))
				{
					$script = Tools::file_get_contents('http://www.google.com/recaptcha/api/js/recaptcha_ajax.js');
					$script = str_replace('document.write', '$("body").append', $script);
					$file = fopen(dirname(__FILE__).'/js/google_recaptcha_ajax.js', 'w+') or die('Impossible ouvrir le fichier');
					fputs($file, $script);
					fclose($file);
				}
				
				//Ajout des js globaux
				$this->context->controller->addJS($this->_path.'/js/google_recaptcha_ajax.js');
				$this->context->controller->addJS($this->_path.'/js/eicaptcha.js');

				return $this->display(__FILE__, 'hookDisplayCustomerAccountForm.tpl');
			}
			//API V2 
			else
			{
				//Insertion du nouveau js
				$this->context->controller->addJS($this->_path.'/js/eicaptcha2.js');
				
				return $this->display(__FILE__, 'hookDisplayCustomerAccountForm2.tpl');
				
			}
		}
	}

	/**
	 * Affichage du captcha sur la page du formulaire de contact
         * (Avec l'API V 1 )
	 */
	private function displayCaptchaContactForm()
	{
		//Génération du template
		require_once(dirname(__FILE__).'/lib/recaptchalib.php');

		$publickey = Configuration::get('CAPTCHA_PUBLIC_KEY');
		$move_defer = '';

		//Si le https est actif sur le site il faut également l'activer dans le captcha
		if (Configuration::get('PS_SSL_ENABLED') == 1)
			$ssl_mode = true;
		else
			$ssl_mode = false;

		//Récupération du code captcha et assignation de la variable au template
		$this->context->smarty->assign('captcha', recaptcha_get_html($publickey, null, $ssl_mode));

		/**
		 * Version 1.6 (Thème default-bootstrap )
		 * Les noms des classes des éléments par défaut sont différents
		 * Gestion des nom des classes pour que le module soit compatible avec l'ensemble des versions
		 */
		if (_PS_VERSION_ > '1.6')
		{
			$error_class = 'alert';
			$form_class = 'contact-form-box';

			//Avec la fonctionnalité js_defer qui place tout le javascript inline en fin de page le captcha ne s'affiche pas au bon endroit
			//On laisse le temps au captcha de se charger et on le place à l'endroit souhaité dans le DOM
			if (Configuration::get('PS_JS_DEFER') == 1)
			{
				$move_defer = "	
						 setTimeout(function(){
							captchaContent = $('#recaptcha_widget_div').clone();
							$('#recaptcha_widget_div').remove();
							$('#captcha-box').append(captchaContent);
						 },1000);
						";
			}
		}
		else
		{
			$error_class = 'error';
			$form_class = 'std';
		}

		return '
				<script type="text/javascript">
					$(document).ready(function(){
		
						var validationPassed = false;
						var test=0;
						//Pour lancer la validation du formulaire on lance la vérification du captcha
						$("#submitMessage").click(function(){
						
							if ( validationPassed )
								$("form.'.$form_class.'").submit();
						
							var waiting_message = "'.$this->l('Please wait during captcha check').'";
							if ( $(".load").length )
								$(".load").html("").html(waiting_message);
							else	
								$(this).after("<div class=\"load\">"+waiting_message+"</div>");
							
							$.ajax({
								method : "POST",
								url : "'._MODULE_DIR_.$this->name.'/eicaptcha-ajax.php",
								data : "action=validate_captcha&
								recaptcha_challenge_field="+$("#recaptcha_challenge_field").val()+"&recaptcha_response_field="+$("#recaptcha_response_field").val(),
								success : function(msg){
									$(".load").html("");
									if ( msg != "") {
									  $(".'.$error_class.'").remove();	
									  $("form.'.$form_class.'").before(msg);
									  if ( $(".'.$error_class.'").length )
										$("html, body").animate({scrollTop:$(".'.$error_class.'").offset().top  },"slow");  
									}
									else {
									 if ( $(".'.$error_class.'").length )
										$(".'.$error_class.'").remove();
										validationPassed = true;
										//Bug dans la soumission du formulaire on insère la valeur du submit
										$("form.'.$form_class.'").append("<input type=\'hidden\' name=\'submitMessage_x\' value=\'send\' />");
										$("form.'.$form_class.'").submit();
									}
								}
							});
							return false;
						});
					
					'.$move_defer.'
					});
				</script>
				';
	}

        
    /**
	 * Affichage du captcha sur la page du formulaire de contact
	 * ( Avec l'API V2 )
	 */
	private function displayCaptachContactFormApiV2()
	{
		//Gestion des classes en fonction de la version de prestashop
		if (_PS_VERSION_ > '1.6')
		{
			$error_class = 'alert';
			$form_class = 'contact-form-box';
		}
		else
		{
			$error_class = 'error';
			$form_class = 'std';
		}

		//Insertion dynamique du contenu
		$js = '<script type="text/javascript">
            
            $(document).ready(function(){
            
               //Insertion de la div qui va contenir le captcha dans la page 
               $(".submit").before("<div id=\"captcha-box\"></div>");
               
               //Gestion de la soumission du formulaire
                $("#submitMessage").click(function(){
                    //Si pas de réponse du formulaire on affiche un message d\'erreur :
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
            
            //Fonction de callBack de recaptcha une fois que la page est chargée
            var onloadCallback = function() {grecaptcha.render("captcha-box", {"sitekey" : "'.Configuration::get('CAPTCHA_PUBLIC_KEY').'"});};
            </script>';

		$js .= '<script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit" async defer></script>';

		return $js;
	}

}
