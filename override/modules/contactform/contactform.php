<?php

class ContactformOverride extends Contactform
{
    public function sendMessage()
    {
        //Module Eicaptcha : Check captcha before submit
        Hook::exec('actionContactFormSubmitBefore');
        if (!sizeof($this->context->controller->errors)) {
            parent::sendMessage();
        }
    }
}
