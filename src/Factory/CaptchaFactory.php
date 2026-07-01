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

namespace Eicaptcha\Module\Factory;

use Configuration;
use EiCaptcha;
use Eicaptcha\Module\Provider\CaptchaProviderInterface;
use Eicaptcha\Module\Provider\GoogleEnterpriseProvider;
use Eicaptcha\Module\Provider\GoogleRecaptchaProvider;
use Eicaptcha\Module\Provider\HCaptchaProvider;

/**
 * Class CaptchaFactory
 *
 * Factory for creating captcha provider instances
 *
 * @since 3.0.0
 */
class CaptchaFactory
{
    /**
     * @var array Map of provider names to their class names
     */
    private static $providers = [
        'google_recaptcha' => GoogleRecaptchaProvider::class,
        'google_enterprise' => GoogleEnterpriseProvider::class,
        'hcaptcha' => HCaptchaProvider::class,
    ];

    /**
     * @var array Cached provider instances
     */
    private static $instances = [];

    /**
     * Create a captcha provider instance based on configuration
     *
     * @param EiCaptcha $module Module instance
     * @param string|null $providerName Provider name (null = auto-detect from config)
     *
     * @return CaptchaProviderInterface
     *
     * @throws \Exception If provider is not found
     */
    public static function create(EiCaptcha $module, $providerName = null)
    {
        // Auto-detect provider from configuration
        if ($providerName === null) {
            $providerName = Configuration::get('CAPTCHA_PROVIDER');

            // Backward compatibility: if no provider is set, use google_recaptcha
            if (empty($providerName)) {
                $providerName = 'google_recaptcha';
            }
        }

        // Return cached instance if available
        if (isset(self::$instances[$providerName])) {
            return self::$instances[$providerName];
        }

        // Check if provider exists
        if (!isset(self::$providers[$providerName])) {
            throw new \Exception(sprintf('Captcha provider "%s" not found', $providerName));
        }

        $providerClass = self::$providers[$providerName];

        // Check if class exists
        if (!class_exists($providerClass)) {
            throw new \Exception(sprintf('Captcha provider class "%s" not found', $providerClass));
        }

        // Create and cache instance
        $provider = new $providerClass($module);
        self::$instances[$providerName] = $provider;

        return $provider;
    }

    /**
     * Get all available providers
     *
     * @param EiCaptcha $module Module instance
     *
     * @return array Array of provider instances indexed by provider name
     */
    public static function getAllProviders(EiCaptcha $module)
    {
        $providers = [];

        foreach (self::$providers as $name => $class) {
            try {
                $providers[$name] = self::create($module, $name);
            } catch (\Exception $e) {
                // Skip providers that cannot be instantiated
                continue;
            }
        }

        return $providers;
    }

    /**
     * Register a new provider
     *
     * @param string $name Provider name
     * @param string $className Provider class name
     *
     * @return void
     */
    public static function registerProvider($name, $className)
    {
        self::$providers[$name] = $className;
    }

    /**
     * Check if a provider is registered
     *
     * @param string $name Provider name
     *
     * @return bool
     */
    public static function hasProvider($name)
    {
        return isset(self::$providers[$name]);
    }

    /**
     * Get the list of registered provider names
     *
     * @return array
     */
    public static function getProviderNames()
    {
        return array_keys(self::$providers);
    }

    /**
     * Clear cached instances
     *
     * @return void
     */
    public static function clearCache()
    {
        self::$instances = [];
    }
}
