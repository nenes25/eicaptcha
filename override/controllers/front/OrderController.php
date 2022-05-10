<?php

class OrderController extends OrderControllerCore
{
    public function postProcess()
    {
        if (
            Tools::isSubmit('submitCreate')
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
