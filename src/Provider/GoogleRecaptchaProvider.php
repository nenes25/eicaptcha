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
use ReCaptcha\ReCaptcha;

/**
 * Class GoogleRecaptchaProvider
 *
 * Provider for Google reCAPTCHA v2 and v3
 *
 * @since 3.0.0
 */
class GoogleRecaptchaProvider extends AbstractCaptchaProvider
{
    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'google_recaptcha';
    }

    /**
     * {@inheritDoc}
     */
    public function getDisplayName()
    {
        return 'Google reCAPTCHA v2/v3';
    }

    /**
     * {@inheritDoc}
     */
    public function validate($response, $remoteIp)
    {
        if (!$this->shouldDisplayToCustomer()) {
            return true;
        }

        $captchaVersion = $this->getConfig('CAPTCHA_VERSION', 2);
        $captchaV3MinScore = (float) $this->getConfig('CAPTCHA_V3_MINIMAL_SCORE', 0.5);

        // Fix issue if allow_url_fopen is set to 0
        if (function_exists('ini_get') && !ini_get('allow_url_fopen')) {
            $recaptchaMethod = new \ReCaptcha\RequestMethod\CurlPost();
        } else {
            $recaptchaMethod = null;
        }

        $captcha = new ReCaptcha($this->getConfig('CAPTCHA_PRIVATE_KEY'), $recaptchaMethod);

        if ($captchaVersion == 3) {
            $captcha->setScoreThreshold($captchaV3MinScore);
        }

        $result = $captcha->verify($response, $remoteIp);

        if (!$result->isSuccess()) {
            $errorMessage = $this->l('Please validate the captcha field before submitting your request');
            $this->setLastError($errorMessage);
            $this->log(sprintf($this->l('Recaptcha response %s'), print_r($result->getErrorCodes(), true)));

            if ($captchaVersion == 3) {
                if ($result->getScore() < $captchaV3MinScore) {
                    $errorMessageV3 = sprintf(
                        'Your request has been blocked by the captcha system, due to a low score of %s, required score is %s',
                        $result->getScore(),
                        $captchaV3MinScore
                    );
                    $this->log($errorMessageV3);
                }
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
                'label' => $this->l('Captcha V3 minimum score'),
                'hint' => sprintf(
                    $this->l('The minimum score required to validate the captcha is a number between 0 and 1. Default is 0.5 (recommended: 0.5 for normal security, 0.3 for less strict, 0.7 for more strict). %s'),
                    '<a href="https://developers.google.com/recaptcha/docs/v3?#interpreting_the_score" target="_blank">Learn more</a>'
                ),
                'name' => 'CAPTCHA_V3_MINIMAL_SCORE',
                'required' => true,
                'class' => 'fixed-width-sm',
                'suffix' => '(0.0 - 1.0)',
                'empty_message' => $this->l('Please fill the captcha v3 minimal score.'),
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

        $captchaVersion = $this->getConfig('CAPTCHA_VERSION', 2);

        if ($captchaVersion == 2) {
            return $this->renderHeaderV2();
        } else {
            return $this->renderHeaderV3();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function renderContactFormWidget(): string
    {
        $captchaVersion = $this->getConfig('CAPTCHA_VERSION', 2);

        $this->context->controller->registerJavascript(
            'modules-eicaptcha-contact-form',
            'modules/' . $this->module->name . '/views/js/eicaptcha-contact-form-v' . $captchaVersion . '.js'
        );

        return '';
    }

    /**
     * Return content for (re)captcha v2
     *
     * @return string|void
     */
    protected function renderHeaderV2()
    {
        $controller = $this->context->controller;
        $loadEverywhere = Configuration::get('CAPTCHA_LOAD_EVERYWHERE') == 1;
        $isAuthController = $controller instanceof \AuthController || $controller instanceof \RegistrationController;
        $isContactController = $controller instanceof ContactController;

        if ((
                $isAuthController
                && Configuration::get('CAPTCHA_ENABLE_ACCOUNT') == 1
            )
            ||
            ($isContactController
                && Configuration::get('CAPTCHA_ENABLE_CONTACT') == 1
            )
            || $loadEverywhere
        ) {
            $this->context->controller->registerStylesheet(
                'module-eicaptcha',
                'modules/' . $this->module->name . '/views/css/eicaptcha.css'
            );

            // Dynamic insertion of the content
            $publicKey = $this->getConfig('CAPTCHA_PUBLIC_KEY');
            $theme = $this->getTheme();
            $lang = $this->getCaptchaLang();

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
                    grecaptcha.render("captcha-box", {"theme" : "' . $theme . '", "sitekey" : "' . $publicKey . '"});
                } else {
                    console.warn("eicaptcha: unable to add captcha-box placeholder to display captcha ( not an error when form is submited sucessfully )");
                }
            };
            </script>';

            if ($isContactController && Configuration::get('CAPTCHA_ENABLE_CONTACT') == 1) {
                $js .= '<script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit&hl=' . $lang . '" async defer></script>';
            }

            return $js;
        }

        return '';
    }

    /**
     * Return content for recaptcha v3
     *
     * @return string|void
     */
    protected function renderHeaderV3()
    {
        $controller = $this->context->controller;
        $loadEverywhere = Configuration::get('CAPTCHA_LOAD_EVERYWHERE') == 1;

        if (
            ($controller instanceof ContactController
                && Configuration::get('CAPTCHA_ENABLE_CONTACT') == 1
            )
            || $loadEverywhere
        ) {
            $publicKey = $this->getConfig('CAPTCHA_PUBLIC_KEY');
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

        return '';
    }

    /**
     * {@inheritDoc}
     */
    public function getTemplateVars()
    {
        $vars = parent::getTemplateVars();
        $vars['captchaVersion'] = $this->getConfig('CAPTCHA_VERSION', 2);
        $vars['publicKey'] = $this->getConfig('CAPTCHA_PUBLIC_KEY');

        return $vars;
    }

    /**
     * {@inheritDoc}
     */
    public function isConfigured()
    {
        $publicKey = $this->getConfig('CAPTCHA_PUBLIC_KEY');
        $privateKey = $this->getConfig('CAPTCHA_PRIVATE_KEY');

        return !empty($publicKey) && !empty($privateKey);
    }

    /**
     * {@inheritDoc}
     */
    public function getTemplatePath()
    {
        return 'module:eicaptcha/views/templates/hook/providers/google_recaptcha.tpl';
    }
}
