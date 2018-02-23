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
 *  @copyright 2013-2016 Hennes Hervé
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  http://www.h-hennes.fr/blog/
 */
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;

include_once '/var/www/public/public/prestashop/prestashop16/config/config.inc.php';
require_once dirname(__FILE__) . '/../../vendor/autoload.php';

class ContactFormTest extends TestCase {

    public static function setUpBeforeClass() {
        \Configuration::updateValue('CAPTCHA_PUBLIC_KEY', $_ENV['captcha_site_key']);
        \Configuration::updateValue('CAPTCHA_PRIVATE_KEY', $_ENV['captcha_private_key']);
        parent::setUpBeforeClass();
    }

    /**
     * Test that contact form fails if no captcha is defined
     * @dataProvider getContactFormData
     * @group eicaptcha_contactform
     * @param array $datas
     */
    public function testCaptchaOnContactForm($datas) {
        
        $client = new Client(array(
            'base_uri' => 'http://web.h-hennes.fr'
        ));
        $response = $client->request('POST', 'prestashop/prestashop_1-6-1-15/fr/nous-contacter', array(
            'form_params' => $datas
        ));
        
        //@Todo: Vérifier qu'on ait bien un message d'erreur

        var_dump($response);
    }

    /**
     * Data Provider pour tester l'envoi du formulaire de contact
     * @return type
     */
    public function getContactFormData() {
        return [
            [
                [
                    'id_contact' => 1,
                    'from' => 'unittest@h-hennes.fr',
                    'id_order' => 'sample_order',
                    'message' => 'This is a test message send by the unit test',
                    'submitMessage' => '1'
                ]
            ]
        ];
    }

}
