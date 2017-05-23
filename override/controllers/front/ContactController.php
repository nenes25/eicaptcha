<?php

class ContactController extends ContactControllerCore {

  public function checkAccess() {
    return (bool)Hook::exec('contactFormAccess');
  }

  public function initCursedPage() {
    parent::setMedia();

    if (!empty($this->redirect_after)) {
      parent::redirect();
    }

    if (!$this->content_only && ($this->display_header || (isset($this->className) && $this->className))) {
      parent::initHeader();
    }

    parent::initContent();
    if (!$this->content_only && ($this->display_footer || (isset($this->className) && $this->className))) {
      parent::initFooter();
    }
    parent::display();

    // like returning Controller::run() < Dispatcher::dispatch() < index.php
    die;
  }
}
