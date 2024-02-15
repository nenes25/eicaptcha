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

require_once dirname(__FILE__) . '/vendor/autoload.php';

use Eicaptcha\Module\ConfigForm;
use Eicaptcha\Module\Debugger;
use Eicaptcha\Module\Installer;
use ReCaptcha\ReCaptcha;

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

    /**
     * @var string (2 or 3 digits Language ISO code) Captcha language (default: en)
     */
    protected $captchaLang = 'en';

    public function __construct()
    {
        $this->author = 'hhennes';
        $this->name = 'eicaptcha';
        $this->tab = 'front_office_features';
        $this->version = '2.5.0';
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

        $this->captchaLang = $this->context->language->iso_code;
        $forceLang = Configuration::get('CAPTCHA_FORCE_LANG');
        if (!empty($forceLang) && Validate::isLanguageIsoCode($forceLang)) {
            $this->captchaLang = $forceLang;
        }
    }

    /**
     * Install Module
     *
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
     *
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
     * @return Debugger
     */
    public function getDebugger()
    {
        return $this->debugger;
    }

    /**
     * @return Context
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Module Configuration in Back Office
     *
     * @return string
     */
    public function getContent()
    {
        $configForm = new ConfigForm($this);
        $this->_html .= $this->debugger->checkComposer();
        $this->_html .= $configForm->postProcess();
        $this->_html .= $configForm->renderForm();

        return $this->_html;
    }

    /**
     * Hook Header
     *
     * @param array $params
     *
     * @return string|void
     */
    public function hookHeader(array $params)
    {
        if (!$this->shouldDisplayToCustomer()) {
            return;
        }

        $captchaVersion = Configuration::get('CAPTCHA_VERSION');
        //Add Content box to contact form page in order to display captcha
        if ($this->context->controller instanceof ContactController
            && Configuration::get('CAPTCHA_ENABLE_CONTACT') == 1
        ) {
            $this->context->controller->registerJavascript(
                'modules-eicaptcha-contact-form',
                'modules/' . $this->name . '/views/js/eicaptcha-contact-form-v' . $captchaVersion . '.js'
            );
        }

        if ($captchaVersion == 2) {
            return $this->renderHeaderV2();
        } else {
            return $this->renderHeaderV3();
        }
    }

    /**
     * Return content for (re)captcha v2
     *
     * @return string|void
     */
    protected function renderHeaderV2()
    {
        if ((
                (
                    $this->context->controller instanceof AuthController
                    || $this->context->controller instanceof RegistrationController
                )
                && Configuration::get('CAPTCHA_ENABLE_ACCOUNT') == 1
            )
            ||
            ($this->context->controller instanceof ContactController
                && Configuration::get('CAPTCHA_ENABLE_CONTACT') == 1
            )
            || Configuration::get('CAPTCHA_LOAD_EVERYWHERE') == 1
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
                $js .= '<script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit&hl=' . $this->captchaLang . '" async defer></script>';
            }

            return $js;
        }
    }

    /**
     * Return content for recaptcha v3
     *
     * @return string|void
     */
    public function renderHeaderV3()
    {
        if (
            ($this->context->controller instanceof ContactController
                && Configuration::get('CAPTCHA_ENABLE_CONTACT') == 1
            )
            || Configuration::get('CAPTCHA_LOAD_EVERYWHERE') == 1
        ) {
            $publicKey = Configuration::get('CAPTCHA_PUBLIC_KEY');
            $js = '
            <script src="https://www.google.com/recaptcha/api.js?render=' . $publicKey . '"></script>
            <script>
                grecaptcha.ready(function () {
                    grecaptcha.execute("' . $publicKey . '", {action: "contact"}).then(function (token) {
                        var recaptchaResponse = document.getElementById("captcha-box");
                        recaptchaResponse.value = token;
                        });
                    });
            </script>';

            return $js;
        }
    }

    /**
     * Add Captcha on the Customer Registration Form
     *
     * @param array $params
     *
     * @return string|void
     */
    public function hookDisplayCustomerAccountForm(array $params)
    {
        if ($this->context->controller->php_self != 'identity'
            && Configuration::get('CAPTCHA_ENABLE_ACCOUNT') == 1
        ) {
            $this->context->smarty->assign([
                'captchaVersion' => Configuration::get('CAPTCHA_VERSION'),
                'publicKey' => Configuration::get('CAPTCHA_PUBLIC_KEY'),
                'captchalang' => $this->captchaLang,
                'captchatheme' => $this->themes[Configuration::get('CAPTCHA_THEME')],
            ]);

            return $this->display(__FILE__, 'views/templates/hook/hookDisplayCustomerAccountForm.tpl');
        }
    }

    /**
     * Check captcha before submit account
     * Custom hook
     *
     * @param array $params
     *
     * @return bool|void
     *
     * @deprecated since 2.4.0
     */
    public function hookActionContactFormSubmitCaptcha(array $params)
    {
        if (Configuration::get('CAPTCHA_ENABLE_ACCOUNT') == 1) {
            $this->debugger->log('check customer registration by method ' . __METHOD__);

            return $this->_validateCaptcha();
        }
    }

    /**
     * Check captcha before submit account
     * Prestashop native hook
     *
     * @param array $params
     *
     * @return bool
     *
     * @since 2.5.0
     */
    public function hookActionSubmitAccountBefore(array $params)
    {
        if (Configuration::get('CAPTCHA_ENABLE_ACCOUNT') == 1
            && Configuration::get('CAPTCHA_USE_AUTHCONTROLLER_OVERRIDE') == 0) {
            $this->debugger->log('check customer registration by method ' . __METHOD__);

            return $this->_validateCaptcha();
        }

        return true;
    }

    /**
     * Check captcha before submit account
     * Custom hook
     *
     * @param array $params
     *
     * @return bool|void
     *
     * @since 2.4.0
     */
    public function hookActionCustomerRegisterSubmitCaptcha(array $params)
    {
        if (Configuration::get('CAPTCHA_ENABLE_ACCOUNT') == 1) {
            $this->debugger->log('check customer registration by method ' . __METHOD__);

            return $this->_validateCaptcha();
        }
    }

    /**
     * Check captcha before submit contact form
     * new custom hook
     *
     * @return bool|void
     */
    public function hookActionContactFormSubmitBefore()
    {
        if (Configuration::get('CAPTCHA_ENABLE_CONTACT') == 1) {
            return $this->_validateCaptcha();
        }
    }

    /**
     * Register media in back office
     *
     * @param array $params
     *
     * @return void
     *
     * @since 2.1.0
     */
    public function hookActionAdminControllerSetMedia(array $params)
    {
        if (
            $this->context->controller instanceof AdminModulesController
            && Tools::getValue('configure') == $this->name
            && Tools::getValue('display_debug') == 1
        ) {
            $this->context->controller->addJS(
                $this->_path . 'views/js/admin.js'
            );
        }
    }

    /**
     * New hook to display content for newsletter registration
     * ( Need to override theme template for themes/classic/modules/ps_emailsubscription/views/templates/hook/ps_emailsubscription.tpl )
     *
     * @param array $params
     *
     * @return string|void
     *
     * @since 2.1.0
     */
    public function hookDisplayNewsletterRegistration(array $params)
    {
        if (
            Configuration::get('CAPTCHA_ENABLE_NEWSLETTER') == 1
            && $this->canUseCaptchaOnNewsletter()
            && $this->shouldDisplayToCustomer()
        ) {
            $this->context->smarty->assign([
                'captchaVersion' => Configuration::get('CAPTCHA_VERSION'),
                'publicKey' => Configuration::get('CAPTCHA_PUBLIC_KEY'),
                'captchalang' => $this->captchaLang,
                'captchatheme' => $this->themes[Configuration::get('CAPTCHA_THEME')],
            ]);

            return $this->display(__FILE__, 'views/templates/hook/hookDisplayNewsletterRegistration.tpl');
        }
    }

    /**
     * New Hook to validate newsletter registration
     *
     * @param array $params
     *
     * @return void
     *
     * @since 2.1.0
     */
    public function hookActionNewsletterRegistrationBefore(array $params)
    {
        if (Configuration::get('CAPTCHA_ENABLE_NEWSLETTER') == 1
            && $this->canUseCaptchaOnNewsletter()
            && $this->shouldDisplayToCustomer()
        ) {
            if (!$this->_validateCaptcha()) {
                $params['hookError'] = $this->l('Please validate the captcha field before submitting your request');
            }
        }
    }

    /**
     * Validate Captcha
     *
     * @return bool
     */
    protected function _validateCaptcha()
    {
        if (!$this->shouldDisplayToCustomer()) {
            return true;
        }

        $context = Context::getContext();
        //Fix issue if allow_url_open is set to 0
        if (function_exists('ini_get') && !ini_get('allow_url_fopen')) {
            $recaptchaMethod = new \ReCaptcha\RequestMethod\CurlPost();
        } else {
            $recaptchaMethod = null;
        }
        $captcha = new ReCaptcha(Configuration::get('CAPTCHA_PRIVATE_KEY'), $recaptchaMethod);
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
     * Custom hook where you can get all the eicaptcha configuration to use the captcha in another module
     * And display with your own template
     * Or in a custom controller
     *
     * @param array $params
     *
     * @return array The eicaptcha configuration
     */
    public function hookActionGetEicaptchaParams(array $params)
    {
        return [
            'displayCaptcha' => $this->shouldDisplayToCustomer(),
            'captchaVersion' => Configuration::get('CAPTCHA_VERSION'),
            'publicKey' => Configuration::get('CAPTCHA_PUBLIC_KEY'),
            'captchaforcelang' => Configuration::get('CAPTCHA_FORCE_LANG'),
            'captchatheme' => $this->themes[Configuration::get('CAPTCHA_THEME')],
        ];
    }

    /**
     * Custom hook used to render the captcha form with the default template
     *
     * @param array $params
     *
     * @return string the rendered template for displaying the captcha ( if needed)
     */
    public function hookDisplayEicaptchaVerification(array $params)
    {
        $this->context->smarty->assign([
            'displayCaptcha' => $this->shouldDisplayToCustomer(),
            'captchaVersion' => Configuration::get('CAPTCHA_VERSION'),
            'publicKey' => Configuration::get('CAPTCHA_PUBLIC_KEY'),
            'captchalang' => $this->captchaLang,
            'captchatheme' => $this->themes[Configuration::get('CAPTCHA_THEME')],
        ]);

        return $this->display(__FILE__, 'views/templates/hook/hookDisplayEicaptchaVerification.tpl');
    }

    /**
     * Custom hook used to validate captcha response
     *
     * @param array $params
     *
     * @return bool
     */
    public function hookActionValidateCaptcha(array $params = [])
    {
        return $this->_validateCaptcha();
    }

    /**
     * Define if captcha can be use on newsletter form
     * Needs a recent version of ps_emailsubscription which implements new required hooks
     *
     * @return bool
     */
    public function canUseCaptchaOnNewsletter()
    {
        if (Module::isInstalled('ps_emailsubscription')) {
            $emailSubcription = Module::getInstanceByName('ps_emailsubscription');
            if (version_compare('2.6.0', $emailSubcription->version) <= 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Define if the captcha should be displayed to the customer
     *
     * @return bool
     */
    protected function shouldDisplayToCustomer()
    {
        if (
            Configuration::get('CAPTCHA_ENABLE_LOGGED_CUSTOMERS') == 0
            && $this->context->customer->id > 0
            && $this->context->customer->email != null
        ) {
            return false;
        }

        return true;
    }
}
