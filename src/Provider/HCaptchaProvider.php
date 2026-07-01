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

namespace Eicaptcha\Module\Provider;

use Configuration;
use ContactController;

/**
 * Class HCaptchaProvider
 *
 * Provider for hCaptcha (privacy-focused alternative to reCAPTCHA)
 *
 * @since 3.0.0
 */
class HCaptchaProvider extends AbstractCaptchaProvider
{
    /**
     * hCaptcha API verification endpoint
     */
    const VERIFY_URL = 'https://hcaptcha.com/siteverify';

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'hcaptcha';
    }

    /**
     * {@inheritDoc}
     */
    public function getDisplayName()
    {
        return 'hCaptcha';
    }

    /**
     * {@inheritDoc}
     */
    public function validate($response, $remoteIp)
    {
        if (!$this->shouldDisplayToCustomer()) {
            return true;
        }

        if (empty($response)) {
            $this->setLastError($this->l('Please validate the captcha field before submitting your request'));

            return false;
        }

        $secretKey = $this->getConfig('CAPTCHA_HCAPTCHA_SECRET_KEY');

        if (empty($secretKey)) {
            $this->setLastError($this->l('hCaptcha secret key is not configured'));

            return false;
        }

        // Prepare POST data
        $data = [
            'secret' => $secretKey,
            'response' => $response,
            'remoteip' => $remoteIp,
        ];

        // Make API request
        $options = [
            'http' => [
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($data),
            ],
        ];

        $context = stream_context_create($options);
        $verify = file_get_contents(self::VERIFY_URL, false, $context);

        if ($verify === false) {
            $this->setLastError($this->l('Unable to verify captcha response with hCaptcha API'));

            return false;
        }

        $verifyResponse = json_decode($verify, true);

        if (!isset($verifyResponse['success']) || $verifyResponse['success'] !== true) {
            $errorMessage = $this->l('Please validate the captcha field before submitting your request');
            $this->setLastError($errorMessage);

            if (isset($verifyResponse['error-codes'])) {
                $this->log(sprintf($this->l('hCaptcha response errors: %s'), print_r($verifyResponse['error-codes'], true)));
            }

            return false;
        }

        $this->log($this->l('Captcha submitted with success'));

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function getConfigFields()
    {
        return [
            [
                'type' => 'text',
                'label' => $this->l('hCaptcha Site Key'),
                'name' => 'CAPTCHA_HCAPTCHA_SITE_KEY',
                'required' => true,
                'desc' => $this->l('Get your site key from https://dashboard.hcaptcha.com/'),
                'empty_message' => $this->l('Please fill the hCaptcha site key'),
                'tab' => 'general',
            ],
            [
                'type' => 'text',
                'label' => $this->l('hCaptcha Secret Key'),
                'name' => 'CAPTCHA_HCAPTCHA_SECRET_KEY',
                'required' => true,
                'desc' => $this->l('Get your secret key from https://dashboard.hcaptcha.com/'),
                'empty_message' => $this->l('Please fill the hCaptcha secret key'),
                'tab' => 'general',
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function renderHeader(array $context = [])
    {
        if (!$this->shouldDisplayToCustomer()) {
            return '';
        }

        $controller = $this->context->controller;
        $loadEverywhere = Configuration::get('CAPTCHA_LOAD_EVERYWHERE') == 1;
        $isAuthController = $controller instanceof \AuthController || $controller instanceof \RegistrationController;
        $isContactController = $controller instanceof ContactController;

        $shouldLoad = (
            ($isAuthController && Configuration::get('CAPTCHA_ENABLE_ACCOUNT') == 1)
            || ($isContactController && Configuration::get('CAPTCHA_ENABLE_CONTACT') == 1)
            || $loadEverywhere
        );

        if (!$shouldLoad) {
            return '';
        }

        $this->context->controller->registerStylesheet(
            'module-eicaptcha',
            'modules/' . $this->module->name . '/views/css/eicaptcha.css'
        );

        $siteKey = $this->getConfig('CAPTCHA_HCAPTCHA_SITE_KEY');
        $theme = $this->getTheme();
        $lang = $this->getCaptchaLang();

        // render=explicit + onload so we can explicitly render both template divs
        // (registration form) and dynamically injected ones (contact form via renderContactFormWidget)
        $js = '<script>
function hcaptchaEiOnLoad() {
    document.querySelectorAll(".h-captcha:not([data-hcaptcha-widget-id])").forEach(function(el) {
        hcaptcha.render(el, {sitekey: "' . $siteKey . '", theme: "' . $theme . '"});
    });
}
</script>
<script src="https://js.hcaptcha.com/1/api.js?onload=hcaptchaEiOnLoad&render=explicit&hl=' . $lang . '" async defer></script>';

        return $js;
    }

    /**
     * {@inheritDoc}
     */
    public function getTemplateVars()
    {
        $vars = parent::getTemplateVars();
        $vars['siteKey'] = $this->getConfig('CAPTCHA_HCAPTCHA_SITE_KEY');

        return $vars;
    }

    /**
     * {@inheritDoc}
     */
    public function isConfigured()
    {
        $siteKey = $this->getConfig('CAPTCHA_HCAPTCHA_SITE_KEY');
        $secretKey = $this->getConfig('CAPTCHA_HCAPTCHA_SECRET_KEY');

        return !empty($siteKey) && !empty($secretKey);
    }

    /**
     * {@inheritDoc}
     */
    public function renderContactFormWidget(): string
    {
        $siteKey = $this->getConfig('CAPTCHA_HCAPTCHA_SITE_KEY');
        $theme = $this->getTheme();

        // Inject the h-captcha div into .form-fields before hcaptcha script loads.
        // hcaptchaEiOnLoad (registered in renderHeader) will then render it explicitly.
        return '<script>
document.addEventListener("DOMContentLoaded", function() {
    var formFields = document.querySelector(".form-fields");
    if (formFields && !formFields.querySelector(".h-captcha")) {
        var wrapper = document.createElement("div");
        wrapper.className = "form-group row eicaptcha-field";
        wrapper.innerHTML = "<label class=\"col-md-3 form-control-label\"></label>'
            . '<div class=\"col-md-9\"><div class=\"h-captcha\" data-sitekey=\"' . $siteKey . '\" data-theme=\"' . $theme . '\"></div></div>";
        formFields.appendChild(wrapper);
    }
});
</script>';
    }

    /**
     * {@inheritDoc}
     */
    public function getResponseFieldName()
    {
        return 'h-captcha-response';
    }

    /**
     * {@inheritDoc}
     */
    public function getTemplatePath()
    {
        return 'module:eicaptcha/views/templates/hook/providers/hcaptcha.tpl';
    }
}
