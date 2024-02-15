<?php

class AuthController extends AuthControllerCore
{
    public function initContent()
    {
        if (Tools::isSubmit('submitCreate') && Configuration::get('CAPTCHA_USE_AUTHCONTROLLER_OVERRIDE') == 1) {
            Hook::exec('actionCustomerRegisterSubmitCaptcha');

            if (!sizeof($this->context->controller->errors)) {
                parent::initContent();
            } else {
                $register_form = $this
                ->makeCustomerForm()
                ->setGuestAllowed(false)
                ->fillWith(Tools::getAllValues());

                FrontController::initContent();

                $this->context->smarty->assign([
                    'register_form' => $register_form->getProxy(),
                    'hook_create_account_top' => Hook::exec('displayCustomerAccountFormTop'),
                ]);
                $this->setTemplate('customer/registration');
            }
        } else {
            parent::initContent();
        }
    }
}
