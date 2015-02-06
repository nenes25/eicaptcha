<?php
/**
* 2007-2014 PrestaShop
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
*  @copyright 2013-2014 Hennes Hervé
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  http://www.h-hennes.fr/blog/
*/

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/eicaptcha.php');

/* Instanciation du controller */
$controller = new FrontController();
if (Configuration::get('PS_SSL_ENABLED') == 1)
	$controller->ssl = true;
$controller->init();

$action = Tools::getValue('action');
$eicaptcha = new Eicaptcha();
$eicaptcha->hookAjaxCall();

?>