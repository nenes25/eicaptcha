<?php

$exec_dir = str_replace('modules/eicaptcha', '', trim(shell_exec('pwd')));
include_once $exec_dir . 'config/config.inc.php';

class EiCaptchaTest extends PHPUnit_Framework_TestCase {
    
    //Nom du module
    protected $_moduleName = 'eicaptcha';
    
    /**
     * Vérification que le module est installé (via la méthode prestashop)
     * @group eicaptcha_install
     */
    public function testModuleIsInstalled() {
        $this->assertTrue(Module::isInstalled($this->_moduleName));
    }
    
    /**
     * Vérification que le module est bien greffé sur les hooks
     * @group eicaptcha_install
     */
    public function testModuleIsHooked() {
        
        $moduleInstance = ModuleCore::getInstanceByName($this->_moduleName);
        $modulesHooks = array('header','displayCustomerAccountForm');
        
        foreach ( $modulesHooks as $hook) {
            $this->assertNotFalse($moduleInstance->isRegisteredInHook($hook));
        }
    }
    
    /**
     * Test de la configuration du module
     * @group eicaptcha_install
     */
    public function testModuleConfiguration(){
        
        //On vérifie que les configurations obligatoires existent
        $this->assertNotFalse(ConfigurationCore::get('CAPTCHA_PUBLIC_KEY'));
        $this->assertNotFalse(ConfigurationCore::get('CAPTCHA_PRIVATE_KEY'));
        
        //Et qu'elles ne sont pas vide
        $this->assertNotEmpty(ConfigurationCore::get('CAPTCHA_PUBLIC_KEY'));
        $this->assertNotEmpty(ConfigurationCore::get('CAPTCHA_PRIVATE_KEY'));
    }

}
