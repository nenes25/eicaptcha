<?php

class OrderController extends OrderControllerCore
{
    public function postProcess()
    {
        if (
            Tools::isSubmit('submitCreate')
            && version_compare(_PS_VERSION_, '8.2.1', '<')
            && Module::isInstalled('eicaptcha')
            && Module::isEnabled('eicaptcha')
            && false === Module::getInstanceByName('eicaptcha')->hookActionCustomerRegisterSubmitCaptcha([])
            && !empty($this->errors)
        ) {
            unset($_POST['submitCreate']);
        }
        parent::postProcess();
    }
}
