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

/**
 * Interface CaptchaProviderInterface
 *
 * Defines the contract for all captcha providers
 *
 * @since 3.0.0
 */
interface CaptchaProviderInterface
{
    /**
     * Get the provider name/identifier
     *
     * @return string Provider identifier (e.g., 'google_recaptcha', 'hcaptcha', 'math')
     */
    public function getName();

    /**
     * Get the provider display name for UI
     *
     * @return string Provider display name (e.g., 'Google reCAPTCHA', 'hCaptcha')
     */
    public function getDisplayName();

    /**
     * Validate the captcha response
     *
     * @param string $response The captcha response token from the form
     * @param string $remoteIp The user's IP address
     *
     * @return bool True if validation succeeds, false otherwise
     */
    public function validate($response, $remoteIp);

    /**
     * Get configuration fields specific to this provider
     * Returns an array of HelperForm field definitions
     *
     * @return array Configuration fields array compatible with PrestaShop HelperForm
     */
    public function getConfigFields();

    /**
     * Get JavaScript/HTML to include in the page header
     *
     * @param array $context Additional context (controller, form type, etc.)
     *
     * @return string HTML/JavaScript code to include in header
     */
    public function renderHeader(array $context = []);

    /**
     * Get template variables for rendering the captcha field
     *
     * @return array Smarty template variables
     */
    public function getTemplateVars();

    /**
     * Check if the provider is properly configured
     *
     * @return bool True if all required configuration is present
     */
    public function isConfigured();

    /**
     * Get the template file path for this provider
     *
     * @return string Template file path relative to module directory
     */
    public function getTemplatePath();

    /**
     * Get the last error message (if validation failed)
     *
     * @return string Error message or empty string
     */
    public function getLastError();

    /**
     * Get additional CSS files required by this provider
     *
     * @return array Array of CSS file paths
     */
    public function getCssFiles();

    /**
     * Get additional JS files required by this provider
     *
     * @return array Array of JS file paths
     */
    public function getJsFiles();
}
