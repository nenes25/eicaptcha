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
use Context;
use EiCaptcha;

/**
 * Class AbstractCaptchaProvider
 *
 * Base class with common functionality for all captcha providers
 *
 * @since 3.0.0
 */
abstract class AbstractCaptchaProvider implements CaptchaProviderInterface
{
    /**
     * @var EiCaptcha
     */
    protected $module;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var string Last error message
     */
    protected $lastError = '';

    /**
     * @var array Themes available for providers that support theming
     */
    protected $themes = [0 => 'light', 1 => 'dark'];

    /**
     * @var string Captcha language
     */
    protected $captchaLang = 'en';

    /**
     * Constructor
     *
     * @param EiCaptcha $module
     */
    public function __construct(EiCaptcha $module)
    {
        $this->module = $module;
        $this->context = $module->getContext();
        $this->captchaLang = $this->context->language->iso_code;

        // Allow forcing a specific language
        $forceLang = Configuration::get('CAPTCHA_FORCE_LANG');
        if (!empty($forceLang) && \Validate::isLanguageIsoCode($forceLang)) {
            $this->captchaLang = $forceLang;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getLastError()
    {
        return $this->lastError;
    }

    /**
     * Set the last error message
     *
     * @param string $error
     */
    protected function setLastError($error)
    {
        $this->lastError = $error;
        $this->log($error);
    }

    /**
     * Check if captcha should be displayed to the current customer
     *
     * @return bool
     */
    public function shouldDisplayToCustomer()
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

    /**
     * Log a message if debug mode is enabled
     *
     * @param string $message
     */
    protected function log($message)
    {
        if (Configuration::get('CAPTCHA_DEBUG')) {
            $this->module->getDebugger()->log(sprintf('[%s] %s', $this->getName(), $message));
        }
    }

    /**
     * Get the theme selected in configuration
     *
     * @return string Theme name (light or dark)
     */
    protected function getTheme()
    {
        return $this->themes[Configuration::get('CAPTCHA_THEME')];
    }

    /**
     * Get the captcha language
     *
     * @return string Language ISO code
     */
    protected function getCaptchaLang()
    {
        return $this->captchaLang;
    }

    /**
     * {@inheritDoc}
     */
    public function getCssFiles()
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function getJsFiles()
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function getResponseFieldName()
    {
        return 'g-recaptcha-response';
    }

    /**
     * {@inheritDoc}
     */
    public function renderContactFormWidget(): string
    {
        return '';
    }

    /**
     * Translate a string in the module context
     *
     * @param string $string
     *
     * @return string
     */
    protected function l($string)
    {
        return $this->module->l($string, strtolower(str_replace('Provider', '', basename(get_class($this)))));
    }

    /**
     * {@inheritDoc}
     */
    public function getTemplateVars()
    {
        return [
            'displayCaptcha' => $this->shouldDisplayToCustomer(),
            'provider' => $this->getName(),
            'providerTemplatePath' => $this->getTemplatePath(),
            'captchalang' => $this->captchaLang,
            'captchatheme' => $this->getTheme(),
        ];
    }

    /**
     * Get configuration value specific to this provider
     *
     * @param string $key Configuration key
     * @param mixed $default Default value if not set
     *
     * @return mixed Configuration value
     */
    protected function getConfig($key, $default = null)
    {
        $value = Configuration::get($key);

        return $value !== false ? $value : $default;
    }
}
