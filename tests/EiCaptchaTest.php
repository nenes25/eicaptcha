<?php

/**
 * Tests fonctionnels du module EiCaptcha
 * Test le bon affichage du captcha sur
 *  - Formulaire de contact
 *  - Formulaire de création de compte
 *  - Formulaire d'envoi à un ami ( si flag actif )
 *  - Formulaire commentaires produits ( si flag actif )
 */
class EiCaptchaTest extends PHPUnit_Extensions_Selenium2TestCase
{
    /** Url du site  */
    protected $_site_url = 'http://localhost/prestashop/';

    /** Versions cibles du module ( 1.4 | 1.5 | 1.6 | ALL ) */
    protected $_targeted_versions = 'ALL';


    /** Flag pour test affichage sendtofriend */
    protected $_test_sendtoafriend = false;

    /** Flag pour test affichage productcomments */
    protected $_test_productcomments = false;


    /**
     * Initialisation de la classe de test
     */
    public function setUp()
    {
        $this->setBrowser('firefox');
        $this->setBrowserUrl($this->_site_url);
    }

    /**
     * Test d'affichage du formulaire sur la page de contact
     * Et qu'ensuite il est affiché sur la page
     * @param string $version Version des prestashop à tester
     * @dataProvider getPrestashopVersions
     * @group eicaptcha
     */
    public function testCaptchaDisplayContactForm($version)
    {
        $this->url($this->_site_url.$version.'/index.php?controller=contact');

        //On vérifie que la div est bien insérée par le captcha
        try {
            $captchaBox = $this->byId('captcha-box');
        } catch (Exception $e) {
            $this->fail('Erreur : le bloc du captcha n\'est pas affiché sur la page de contact version '.$version);
        }

        //On s'assure que la div du captcha contiens bien du code
        $this->assertNotEquals('', $captchaBox->attribute('innerHTML'));
    }

    /**
     * Test d'affichage du captcha sur la page de creation de compte
     * @param string $version Version des prestashop à tester
     * @dataProvider getPrestashopVersions
     * @group eicaptcha
     */
    public function testCaptchaDisplayAccountCreation($version)
    {
        //Email aléatoire
        $email = sprintf('dev%s@yopmail.com', time());

        //1ère étape : Saisie de l'adresse email
        $this->url($this->_site_url.$version.'/index.php?controller=authentication&back=my-account');
        $this->byId('email_create')->value($email);
        $this->byId('SubmitCreate')->click();
        sleep(3);

        //2ème etape : Affichage des informations complémentaires
        //On vérifie que la div est bien insérée par le captcha
        try {
            $captchaBox = $this->byId('captcha-box');
        } catch (Exception $e) {
            $this->fail('Erreur : le bloc du captcha n\'est pas affiché sur la page de création du compte '.$version);
        }

        //On s'assure que la div du captcha contiens bien du code
        $this->assertNotEquals('', $captchaBox->attribute('innerHTML'));
    }


    /**
     * Test d'affichage du captcha sur la formulaire "Envoyer à un ami"
     * @param string $version Version des prestashop à tester
     * @dataProvider getPrestashopVersions
     * @group eicaptcha
     */
    public function testCaptchaDisplaySendToAFriend($version)
    {
        if ( !$this->_test_sendtoafriend )
            $this->markTestSkipped('Test du captcha sur le formulaire envoyer à un ami desactivé');
    }

    /**
     * Test d'affichage du captcha sur la formulaire "Envoyer à un ami"
     * @param string $version Version des prestashop à tester
     * @dataProvider getPrestashopVersions
     * @group eicaptcha
     */
    public function testCaptchaDisplayProductComments($version)
    {
        if ( !$this->_test_productcomments )
            $this->markTestSkipped('Test du captcha sur les commentaires produits desactivé');
    }


    /**
     * Récupération des versions de prestashop installées sur l'infrastructure
     * Et renvoi des versions ciblées par la variable $_targeted_versions
     */
    public function getPrestashopVersions()
    {
        $prestashop_dir = dirname(__FILE__).'/../../../';
        $dir            = opendir($prestashop_dir);

        $versions = array();

        //Récupération de toutes les versions prestashop installées
        while ($item = readdir($dir)) {
            if (is_dir($prestashop_dir.$item) && preg_match('#prestashop#', $item)) {
                //En fonction des paramètres du module on inclus seulement les version cibles
                if ($this->_targeted_versions != 'ALL') {
                    if (preg_match('#'.str_replace('.', '-', $this->_targeted_versions).'#', $item)) $versions[] = array($item);
                }
                // Si on cible toutes les versions on inclus tout
                else {
                    $versions[] = array($item);
                }
            }
        }

        return $versions;
    }
}