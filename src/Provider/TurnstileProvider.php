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
use Tools;

/**
 * Class TurnstileProvider
 *
 * Provider for Cloudflare Turnstile
 * Free, privacy-friendly alternative to reCAPTCHA
 *
 * @since 3.0.0
 */
class TurnstileProvider extends AbstractCaptchaProvider
{
    /**
     * Cloudflare Turnstile API verification endpoint
     */
    const VERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'turnstile';
    }

    /**
     * @inheritDoc
     */
    public function getDisplayName()
    {
        return 'Cloudflare Turnstile';
    }

    /**
     * @inheritDoc
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

        $secretKey = $this->getConfig('CAPTCHA_TURNSTILE_SECRET_KEY');

        if (empty($secretKey)) {
            $this->setLastError($this->l('Turnstile secret key is not configured'));

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
            $this->setLastError($this->l('Unable to verify captcha response with Cloudflare Turnstile API'));

            return false;
        }

        $verifyResponse = json_decode($verify, true);

        if (!isset($verifyResponse['success']) || $verifyResponse['success'] !== true) {
            $errorMessage = $this->l('Please validate the captcha field before submitting your request');
            $this->setLastError($errorMessage);

            if (isset($verifyResponse['error-codes'])) {
                $this->log(sprintf($this->l('Turnstile response errors: %s'), print_r($verifyResponse['error-codes'], true)));
            }

            return false;
        }

        $this->log($this->l('Captcha submitted with success'));

        return true;
    }

    /**
     * @inheritDoc
     */
    public function getConfigFields()
    {
        return [
            [
                'type' => 'text',
                'label' => $this->l('Turnstile Site Key'),
                'name' => 'CAPTCHA_TURNSTILE_SITE_KEY',
                'required' => true,
                'desc' => sprintf(
                    $this->l('Get your site key from %s'),
                    '<a href="https://dash.cloudflare.com/?to=/:account/turnstile" target="_blank">Cloudflare Dashboard</a>'
                ),
                'empty_message' => $this->l('Please fill the Turnstile site key'),
                'tab' => 'general',
            ],
            [
                'type' => 'text',
                'label' => $this->l('Turnstile Secret Key'),
                'name' => 'CAPTCHA_TURNSTILE_SECRET_KEY',
                'required' => true,
                'desc' => $this->l('Your secret key from Cloudflare Dashboard'),
                'empty_message' => $this->l('Please fill the Turnstile secret key'),
                'tab' => 'general',
            ],
            [
                'type' => 'radio',
                'label' => $this->l('Widget Theme'),
                'name' => 'CAPTCHA_TURNSTILE_THEME',
                'required' => true,
                'values' => [
                    [
                        'id' => 'light',
                        'value' => 'light',
                        'label' => $this->l('Light'),
                    ],
                    [
                        'id' => 'dark',
                        'value' => 'dark',
                        'label' => $this->l('Dark'),
                    ],
                    [
                        'id' => 'auto',
                        'value' => 'auto',
                        'label' => $this->l('Auto (matches system theme)'),
                    ],
                ],
                'tab' => 'general',
            ],
            [
                'type' => 'radio',
                'label' => $this->l('Widget Size'),
                'name' => 'CAPTCHA_TURNSTILE_SIZE',
                'required' => true,
                'values' => [
                    [
                        'id' => 'normal',
                        'value' => 'normal',
                        'label' => $this->l('Normal'),
                    ],
                    [
                        'id' => 'compact',
                        'value' => 'compact',
                        'label' => $this->l('Compact'),
                    ],
                ],
                'tab' => 'general',
            ],
        ];
    }

    /**
     * @inheritDoc
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

            $siteKey = $this->getConfig('CAPTCHA_TURNSTILE_SITE_KEY');

            $js = '<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>';

            return $js;
        }

        return '';
    }

    /**
     * @inheritDoc
     */
    public function getTemplateVars()
    {
        $vars = parent::getTemplateVars();
        $vars['siteKey'] = $this->getConfig('CAPTCHA_TURNSTILE_SITE_KEY');
        $vars['turnstileTheme'] = $this->getConfig('CAPTCHA_TURNSTILE_THEME', 'auto');
        $vars['turnstileSize'] = $this->getConfig('CAPTCHA_TURNSTILE_SIZE', 'normal');

        return $vars;
    }

    /**
     * @inheritDoc
     */
    public function isConfigured()
    {
        $siteKey = $this->getConfig('CAPTCHA_TURNSTILE_SITE_KEY');
        $secretKey = $this->getConfig('CAPTCHA_TURNSTILE_SECRET_KEY');

        return !empty($siteKey) && !empty($secretKey);
    }

    /**
     * @inheritDoc
     */
    public function getTemplatePath()
    {
        return 'module:eicaptcha/views/templates/hook/providers/turnstile.tpl';
    }

    /**
     * Get the response field name for Turnstile
     *
     * @return string
     */
    public function getResponseFieldName()
    {
        return 'cf-turnstile-response';
    }
}
