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
 *  @copyright 2013-2018 Hennes Hervé
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  http://www.h-hennes.fr/blog/
 */
use PHPUnit\Framework\TestCase;

//Inclusion de la configuration
//include_once dirname(__FILE__).'/../../config/config.inc.php';

include_once '/var/www/public/public/prestashop/prestashop16/config/config.inc.php';
require_once dirname(__FILE__).'/../../vendor/autoload.php';

class EiCaptchaTest extends TestCase {
    
    /** @var string Module Name */
    protected $_moduleName = 'eicaptcha';
    
    /** @var array Module hooks */
    protected $_moduleHooks = array(
        'header',
        'displayCustomerAccountForm',
        'contactFormAccess',
    );
    
   
    /**
     * Check if module is well installed
     * @group eicaptcha_install
     */
    public function testModuleIsInstalled() {
        $this->assertTrue(\Module::isInstalled($this->_moduleName));
    }
    
    /**
     * Check if module is well hooked
     * @depends testModuleIsInstalled
     * @group eicaptcha_install
     */
    public function testModuleIsHooked() {
        
        $moduleInstance = \Module::getInstanceByName($this->_moduleName);
        
        foreach ( $this->_moduleHooks as $hook) {
            $this->assertNotFalse($moduleInstance->isRegisteredInHook($hook));
        }
    }
    
    /**
     * Check module configuration after installation
     * @depends testModuleIsInstalled
     * @group eicaptcha_install
     */
    public function testDefaultConfiguration()
    {
        $this->assertFalse(\Configuration::get('CAPTCHA_PUBLIC_KEY'));
        $this->assertFalse(\Configuration::get('CAPTCHA_PRIVATE_KEY'));
        $this->assertEquals("0",(string)\Configuration::get('CAPTCHA_ENABLE_ACCOUNT'));
        $this->assertEquals("0",(string)\Configuration::get('CAPTCHA_ENABLE_CONTACT'));
        $this->assertEquals("0",(string)\Configuration::get('CAPTCHA_THEME'));
    }
    
    /**
     * Set Captcha configuration
     * @depends testModuleIsInstalled
     * @group eicaptcha_install
     */
    public function testSetCaptchaConfiguration()
    {
        \Configuration::updateValue('CAPTCHA_PUBLIC_KEY',$_ENV['captcha_site_key']);
        \Configuration::updateValue('CAPTCHA_PRIVATE_KEY',$_ENV['captcha_private_key']);
        
        $this->assertEquals($_ENV['captcha_site_key'], \Configuration::get('CAPTCHA_PUBLIC_KEY'));
        $this->assertEquals($_ENV['captcha_private_key'], \Configuration::get('CAPTCHA_PRIVATE_KEY'));
    }
    
}
