<?php

class AuthController extends AuthControllerCore
{

    public function initContent()
    {
        if (Tools::isSubmit('submitCreate')) {
              Hook::exec('actionContactFormSubmitCaptcha');
        }

        if ( !$this->context->controllers->errors[]) {
            parent::initContent();
        } else {
            FrontController::initContent();
        }
    }
}