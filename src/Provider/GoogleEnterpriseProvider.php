<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file docs/licenses/LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to contact@h-hennes.fr so we can send you a copy immediately.
 *
 * @author    Hervé HENNES <contact@h-hhennes.fr> and contributors / https://github.com/nenes25/eicaptcha
 * @copyright since 2013 Hervé HENNES
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License ("AFL") v. 3.0
 */

namespace Eicaptcha\Module\Provider;

use Configuration;
use ContactController;

/**
 * Class GoogleEnterpriseProvider
 *
 * Provider for Google reCAPTCHA Enterprise
 *
 * Note: This provider extends GoogleRecaptchaProvider as the frontend implementation
 * is identical. The main difference is in the backend validation which uses the
 * Google Cloud reCAPTCHA Enterprise API.
 *
 * @since 3.0.0
 */
class GoogleEnterpriseProvider extends GoogleRecaptchaProvider
{
    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'google_enterprise';
    }

    /**
     * @inheritDoc
     */
    public function getDisplayName()
    {
        return 'Google reCAPTCHA Enterprise';
    }

    /**
     * @inheritDoc
     */
    public function validate($response, $remoteIp)
    {
        if (!$this->shouldDisplayToCustomer()) {
            return true;
        }

        // Check if Google Cloud library is available
        if (!class_exists('\Google\Cloud\RecaptchaEnterprise\V1\RecaptchaEnterpriseServiceClient')) {
            $this->setLastError($this->l('Google Cloud reCAPTCHA Enterprise library is not installed. Please run: composer require google/cloud-recaptcha-enterprise'));
            $this->log('Google Cloud reCAPTCHA Enterprise library not found');

            // Fallback to standard reCAPTCHA validation
            return parent::validate($response, $remoteIp);
        }

        try {
            $projectId = $this->getConfig('CAPTCHA_GOOGLE_ENTERPRISE_PROJECT');
            $apiKey = $this->getConfig('CAPTCHA_GOOGLE_ENTERPRISE_KEY');

            if (empty($projectId) || empty($apiKey)) {
                $this->setLastError($this->l('Google reCAPTCHA Enterprise is not properly configured'));

                return false;
            }

            // Create the reCAPTCHA Enterprise client
            $client = new \Google\Cloud\RecaptchaEnterprise\V1\RecaptchaEnterpriseServiceClient([
                'credentials' => $apiKey,
            ]);

            // Prepare the assessment request
            $projectName = $client->projectName($projectId);
            $event = (new \Google\Cloud\RecaptchaEnterprise\V1\Event())
                ->setToken($response)
                ->setSiteKey($this->getConfig('CAPTCHA_PUBLIC_KEY'));

            $assessment = (new \Google\Cloud\RecaptchaEnterprise\V1\Assessment())
                ->setEvent($event);

            $request = (new \Google\Cloud\RecaptchaEnterprise\V1\CreateAssessmentRequest())
                ->setParent($projectName)
                ->setAssessment($assessment);

            // Create the assessment
            $response = $client->createAssessment($request);

            // Check if the token is valid
            if (!$response->getTokenProperties()->getValid()) {
                $this->setLastError($this->l('Please validate the captcha field before submitting your request'));
                $this->log('Invalid token: ' . $response->getTokenProperties()->getInvalidReason());

                return false;
            }

            // Check the risk score (for V3)
            $captchaVersion = $this->getConfig('CAPTCHA_VERSION', 2);
            if ($captchaVersion == 3) {
                $score = $response->getRiskAnalysis()->getScore();
                $minScore = (float) $this->getConfig('CAPTCHA_V3_MINIMAL_SCORE', 0.5);

                if ($score < $minScore) {
                    $errorMessage = sprintf(
                        'Your request has been blocked by the captcha system, due to a low score of %s, required score is %s',
                        $score,
                        $minScore
                    );
                    $this->setLastError($this->l('Please validate the captcha field before submitting your request'));
                    $this->log($errorMessage);

                    return false;
                }
            }

            $this->log($this->l('Captcha submitted with success'));

            return true;
        } catch (\Exception $e) {
            $this->setLastError($this->l('Error validating captcha with Google Enterprise: ') . $e->getMessage());
            $this->log('Google Enterprise validation error: ' . $e->getMessage());

            // Fallback to standard validation in case of error
            return parent::validate($response, $remoteIp);
        }
    }

    /**
     * @inheritDoc
     */
    public function getConfigFields()
    {
        $fields = parent::getConfigFields();

        // Add Enterprise-specific fields
        $enterpriseFields = [
            [
                'type' => 'text',
                'label' => $this->l('Google Cloud Project ID'),
                'name' => 'CAPTCHA_GOOGLE_ENTERPRISE_PROJECT',
                'required' => true,
                'desc' => $this->l('Your Google Cloud project ID where reCAPTCHA Enterprise is enabled'),
                'empty_message' => $this->l('Please fill the Google Cloud project ID'),
                'tab' => 'general',
            ],
            [
                'type' => 'textarea',
                'label' => $this->l('Service Account Key JSON'),
                'name' => 'CAPTCHA_GOOGLE_ENTERPRISE_KEY',
                'required' => true,
                'desc' => $this->l('Paste the content of your service account JSON key file'),
                'empty_message' => $this->l('Please fill the service account key'),
                'tab' => 'general',
                'rows' => 10,
            ],
        ];

        return array_merge($fields, $enterpriseFields);
    }

    /**
     * @inheritDoc
     */
    public function isConfigured()
    {
        $publicKey = $this->getConfig('CAPTCHA_PUBLIC_KEY');
        $projectId = $this->getConfig('CAPTCHA_GOOGLE_ENTERPRISE_PROJECT');
        $apiKey = $this->getConfig('CAPTCHA_GOOGLE_ENTERPRISE_KEY');

        return !empty($publicKey) && !empty($projectId) && !empty($apiKey);
    }

    /**
     * @inheritDoc
     */
    public function getTemplatePath()
    {
        // Use the same template as Google reCAPTCHA (frontend is identical)
        return 'module:eicaptcha/views/templates/hook/providers/google_recaptcha.tpl';
    }
}
