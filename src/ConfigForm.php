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

namespace Eicaptcha\Module;

use Configuration;
use Context;
use EiCaptcha;
use Eicaptcha\Module\Factory\CaptchaFactory;
use HelperForm;
use Language;
use Tools;

class ConfigForm
{
    /**
     * @var EiCaptcha
     */
    private $module;

    /**
     * @var Context
     */
    private $context;

    /**
     * Installer constructor.
     *
     * @param EiCaptcha $module
     */
    public function __construct(EiCaptcha $module)
    {
        $this->module = $module;
        $this->context = $this->module->getContext();
    }

    /**
     * Admin Form for module Configuration
     *
     * @return string
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function renderForm()
    {
        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Eicaptcha Configuration'),
                    'icon' => 'icon-cogs',
                ],
                'tabs' => [
                    'general' => $this->l('General configuration'),
                    'advanced' => $this->l('Advanced parameters'),
                ],
                'description' => $this->l('Configure your captcha provider and protect your website from spam and bots'),
                'input' => array_merge(
                    // Provider selection
                    [
                        [
                            'type' => 'select',
                            'label' => $this->l('Captcha Provider'),
                            'name' => 'CAPTCHA_PROVIDER',
                            'required' => true,
                            'desc' => $this->l('Select the captcha system you want to use'),
                            'options' => [
                                'query' => $this->getAvailableProviders(),
                                'id' => 'id',
                                'name' => 'name',
                            ],
                            'tab' => 'general',
                        ],
                    ],
                    // Provider-specific fields (dynamically loaded)
                    $this->getProviderSpecificFields(),
                    // Common fields
                    $this->getCommonFields(),
                    // Advanced fields
                    $this->getAdvancedFields()
                ),
                'submit' => [
                    'title' => $this->l('Save'),
                    'class' => 'button btn btn-default pull-right',
                ],
            ],
        ];

        //Display debug data to help detect issues
        if (Tools::getValue('display_debug')) {
            $fields_form['form']['input'][] = [
                'type' => 'html',
                'name' => 'debug_html',
                'html_content' => $this->module->getDebugger()->debugModuleInstall(),
                'tab' => 'advanced',
            ];
        }

        //For version under PS 8.x we let the choice to the customer to use the override or the hook
        if (version_compare(_PS_VERSION_, '8.0') < 0) {
            $fields_form['form']['input'][] = [
                'type' => 'switch',
                'name' => 'CAPTCHA_USE_AUTHCONTROLLER_OVERRIDE',
                'label' => $this->l('Use the controller override'),
                'hint' => $this->l('Choose if you want to use the override or the hook to validate customers registration'),
                'desc' => $this->l('If you don\'t know what to do with this value let it on the default one'),
                'required' => false,
                'class' => 't',
                'is_bool' => true,
                'values' => [
                    [
                        'id' => 'active_on',
                        'value' => 1,
                        'label' => $this->l('Enabled'),
                    ],
                    [
                        'id' => 'active_off',
                        'value' => 0,
                        'label' => $this->l('Disabled'),
                    ],
                ],
                'tab' => 'advanced',
            ];
        }

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ?
            Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->id = 'eicaptcha';
        $helper->submit_action = 'SubmitCaptchaConfiguration';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->module->name . '&tab_module=' . $this->module->tab . '&module_name=' . $this->module->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        ];

        return $helper->generateForm([$fields_form]);
    }

    /**
     * Get provider-specific configuration fields
     *
     * @return array
     *
     * @since 3.0.0
     */
    protected function getProviderSpecificFields()
    {
        try {
            // Get the currently selected provider
            $currentProvider = Tools::getValue('CAPTCHA_PROVIDER', Configuration::get('CAPTCHA_PROVIDER') ?: 'google_recaptcha');
            $provider = CaptchaFactory::create($this->module, $currentProvider);

            // Get provider-specific fields
            return $provider->getConfigFields();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get common configuration fields (applicable to all providers)
     *
     * @return array
     *
     * @since 3.0.0
     */
    protected function getCommonFields()
    {
        return [
            [
                'type' => 'switch',
                'label' => $this->l('Enable Captcha for logged customers'),
                'name' => 'CAPTCHA_ENABLE_LOGGED_CUSTOMERS',
                'required' => true,
                'class' => 't',
                'is_bool' => true,
                'hint' => $this->l('Define if logged customers need to use captcha or not'),
                'values' => [
                    [
                        'id' => 'active_on',
                        'value' => 1,
                        'label' => $this->l('Enabled'),
                    ],
                    [
                        'id' => 'active_off',
                        'value' => 0,
                        'label' => $this->l('Disabled'),
                    ],
                ],
                'tab' => 'general',
            ],
            [
                'type' => 'switch',
                'label' => $this->l('Enable Captcha for contact form'),
                'name' => 'CAPTCHA_ENABLE_CONTACT',
                'required' => true,
                'class' => 't',
                'is_bool' => true,
                'values' => [
                    [
                        'id' => 'active_on',
                        'value' => 1,
                        'label' => $this->l('Enabled'),
                    ],
                    [
                        'id' => 'active_off',
                        'value' => 0,
                        'label' => $this->l('Disabled'),
                    ],
                ],
                'tab' => 'general',
            ],
            [
                'type' => 'switch',
                'label' => $this->l('Enable Captcha for account creation'),
                'name' => 'CAPTCHA_ENABLE_ACCOUNT',
                'required' => true,
                'class' => 't',
                'is_bool' => true,
                'values' => [
                    [
                        'id' => 'active_on',
                        'value' => 1,
                        'label' => $this->l('Enabled'),
                    ],
                    [
                        'id' => 'active_off',
                        'value' => 0,
                        'label' => $this->l('Disabled'),
                    ],
                ],
                'tab' => 'general',
            ],
            [
                'type' => 'switch',
                'label' => $this->l('Enable Captcha for newsletter registration'),
                'hint' => $this->l('Only availaibles in certain conditions*'),
                'name' => 'CAPTCHA_ENABLE_NEWSLETTER',
                'required' => true,
                'class' => 't',
                'is_bool' => true,
                'values' => [
                    [
                        'id' => 'active_on',
                        'value' => 1,
                        'label' => $this->l('Enabled'),
                    ],
                    [
                        'id' => 'active_off',
                        'value' => 0,
                        'label' => $this->l('Disabled'),
                    ],
                ],
                'tab' => 'general',
            ],
            [
                'type' => 'text',
                'label' => $this->l('Force Captcha language'),
                'hint' => $this->l('Language code ( en-GB | fr | de | de-AT | ... ) - Leave empty for autodetect'),
                'desc' => $this->l('For available language codes see: https://developers.google.com/recaptcha/docs/language'),
                'name' => 'CAPTCHA_FORCE_LANG',
                'required' => false,
                'tab' => 'general',
            ],
            [
                'type' => 'radio',
                'label' => $this->l('Theme'),
                'name' => 'CAPTCHA_THEME',
                'required' => true,
                'is_bool' => true,
                'values' => [
                    [
                        'id' => 'cdark',
                        'value' => 1,
                        'label' => $this->l('Dark'),
                    ],
                    [
                        'id' => 'clight',
                        'value' => 0,
                        'label' => $this->l('Light'),
                    ],
                ],
                'tab' => 'general',
            ],
        ];
    }

    /**
     * Get advanced configuration fields
     *
     * @return array
     *
     * @since 3.0.0
     */
    protected function getAdvancedFields()
    {
        return [
            [
                'type' => 'switch',
                'name' => 'CAPTCHA_DEBUG',
                'label' => $this->l('Enable Debug'),
                'hint' => $this->l('Use only for debug'),
                'desc' => sprintf(
                    $this->l('Enable loging for debuging module, see file %s'),
                    _PS_MODULE_DIR_ . 'eicaptcha/logs/debug.log'
                ),
                'required' => false,
                'class' => 't',
                'is_bool' => true,
                'values' => [
                    [
                        'id' => 'active_on',
                        'value' => 1,
                        'label' => $this->l('Enabled'),
                    ],
                    [
                        'id' => 'active_off',
                        'value' => 0,
                        'label' => $this->l('Disabled'),
                    ],
                ],
                'tab' => 'advanced',
            ],
            [
                'type' => 'switch',
                'name' => 'CAPTCHA_LOAD_EVERYWHERE',
                'label' => $this->l('Load Recaptcha library everywhere'),
                'hint' => $this->l('Let this option disabled by default if you don\'t use a specific contact form'),
                'desc' => $this->l('Only If you use a theme with elementor or warehouse and your contact form is not on the default page, enable this option.'),
                'required' => false,
                'class' => 't',
                'is_bool' => true,
                'values' => [
                    [
                        'id' => 'active_on',
                        'value' => 1,
                        'label' => $this->l('Enabled'),
                    ],
                    [
                        'id' => 'active_off',
                        'value' => 0,
                        'label' => $this->l('Disabled'),
                    ],
                ],
                'tab' => 'advanced',
            ],
            [
                'type' => 'html',
                'label' => $this->l('Check module installation'),
                'name' => 'enable_debug_html',
                'html_content' => '<a href="' . $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->module->name . '&tab_module=' . $this->module->tab . '&module_name=' . $this->module->name . '&display_debug=1&token=' . Tools::getAdminTokenLite('AdminModules') . '">' . $this->l('Check if module is well installed') . '</a>',
                'desc' => $this->l('click on this link will reload the page, please go again in tab "advanced parameters" to see the results'),
                'tab' => 'advanced',
            ],
        ];
    }

    /**
     * Post Process in back office
     *
     * @return string|void
     */
    public function postProcess()
    {
        if (Tools::isSubmit('SubmitCaptchaConfiguration')) {
            // Validate specific fields if needed
            $minScore = Tools::getValue('CAPTCHA_V3_MINIMAL_SCORE');
            if (!empty($minScore) && (!is_numeric($minScore) || $minScore < 0 || $minScore > 1)) {
                return $this->module->displayError($this->l('The V3 minimal score must be a number between 0 and 1'));
            }

            // Save CAPTCHA_PROVIDER first
            Configuration::updateValue('CAPTCHA_PROVIDER', Tools::getValue('CAPTCHA_PROVIDER'));

            // Save all configuration values dynamically
            $allFields = $this->getAllConfigurationFields();

            foreach ($allFields as $field) {
                if (isset($field['name'])) {
                    $value = Tools::getValue($field['name']);

                    // Handle different field types
                    if (isset($field['type']) && $field['type'] === 'switch') {
                        $value = (int) $value;
                    } elseif (isset($field['type']) && $field['type'] === 'text' && $field['name'] === 'CAPTCHA_V3_MINIMAL_SCORE') {
                        $value = (float) $value;
                    }

                    Configuration::updateValue($field['name'], $value);
                }
            }

            return $this->module->displayConfirmation($this->l('Settings updated'));
        }
    }

    /**
     * Get config values to hydrate the helperForm
     *
     * @return array
     */
    public function getConfigFieldsValues()
    {
        $values = [];

        // Get all configuration fields
        $allFields = $this->getAllConfigurationFields();

        foreach ($allFields as $field) {
            if (isset($field['name'])) {
                $default = '';

                // Set default values for specific fields
                if ($field['name'] === 'CAPTCHA_PROVIDER') {
                    $default = 'google_recaptcha';
                }

                $values[$field['name']] = Tools::getValue(
                    $field['name'],
                    Configuration::get($field['name']) ?: $default
                );
            }
        }

        return $values;
    }

    /**
     * Get all configuration fields from all sources
     *
     * @return array
     *
     * @since 3.0.0
     */
    protected function getAllConfigurationFields()
    {
        $allFields = [];

        // Provider selector
        $allFields[] = ['name' => 'CAPTCHA_PROVIDER'];

        // Get all providers and their fields
        try {
            $providers = CaptchaFactory::getAllProviders($this->module);

            foreach ($providers as $provider) {
                $providerFields = $provider->getConfigFields();
                $allFields = array_merge($allFields, $providerFields);
            }
        } catch (\Exception $e) {
            // Continue even if providers fail to load
        }

        // Common fields
        $commonFields = [
            ['name' => 'CAPTCHA_ENABLE_LOGGED_CUSTOMERS'],
            ['name' => 'CAPTCHA_ENABLE_CONTACT'],
            ['name' => 'CAPTCHA_ENABLE_ACCOUNT'],
            ['name' => 'CAPTCHA_ENABLE_NEWSLETTER'],
            ['name' => 'CAPTCHA_FORCE_LANG'],
            ['name' => 'CAPTCHA_THEME'],
        ];
        $allFields = array_merge($allFields, $commonFields);

        // Advanced fields
        $advancedFields = [
            ['name' => 'CAPTCHA_DEBUG', 'type' => 'switch'],
            ['name' => 'CAPTCHA_LOAD_EVERYWHERE', 'type' => 'switch'],
            ['name' => 'CAPTCHA_USE_AUTHCONTROLLER_OVERRIDE', 'type' => 'switch'],
        ];
        $allFields = array_merge($allFields, $advancedFields);

        return $allFields;
    }

    /**
     * Get available captcha providers
     *
     * @return array
     *
     * @since 3.0.0
     */
    protected function getAvailableProviders()
    {
        $providers = [];

        try {
            $allProviders = CaptchaFactory::getAllProviders($this->module);

            foreach ($allProviders as $provider) {
                $providers[] = [
                    'id' => $provider->getName(),
                    'name' => $provider->getDisplayName(),
                ];
            }
        } catch (\Exception $e) {
            // Fallback to default provider
            $providers[] = [
                'id' => 'google_recaptcha',
                'name' => 'Google reCAPTCHA',
            ];
        }

        return $providers;
    }

    /**
     * Alias of l function with specific context
     *
     * @param string $trans
     *
     * @return string
     */
    public function l($trans)
    {
        return $this->module->l($trans, 'configform');
    }
}
