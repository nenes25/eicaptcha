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
use Tools;

/**
 * Class MathCaptchaProvider
 *
 * Simple math-based captcha provider (no external dependencies)
 *
 * @since 3.0.0
 */
class MathCaptchaProvider extends AbstractCaptchaProvider
{
    /**
     * Session key for storing the captcha answer
     */
    const SESSION_KEY = 'eicaptcha_math_answer';

    /**
     * Session key for storing the captcha timestamp
     */
    const SESSION_TIMESTAMP_KEY = 'eicaptcha_math_timestamp';

    /**
     * Session key for storing the captcha token (CSRF protection)
     */
    const SESSION_TOKEN_KEY = 'eicaptcha_math_token';

    /**
     * Captcha expiration time (5 minutes)
     */
    const EXPIRATION_TIME = 300;

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'math';
    }

    /**
     * @inheritDoc
     */
    public function getDisplayName()
    {
        return 'Math Captcha';
    }

    /**
     * @inheritDoc
     */
    public function validate($response, $remoteIp)
    {
        if (!$this->shouldDisplayToCustomer()) {
            return true;
        }

        // Start session if not already started
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // Get user response
        $userAnswer = Tools::getValue('math-captcha-response');
        $userToken = Tools::getValue('math-captcha-token');

        if (empty($userAnswer)) {
            $this->setLastError($this->l('Please solve the math captcha'));

            return false;
        }

        // Check if captcha exists in session
        if (!isset($_SESSION[self::SESSION_KEY]) || !isset($_SESSION[self::SESSION_TIMESTAMP_KEY]) || !isset($_SESSION[self::SESSION_TOKEN_KEY])) {
            $this->setLastError($this->l('Captcha session expired, please refresh the page'));

            return false;
        }

        // Check token (CSRF protection)
        if ($userToken !== $_SESSION[self::SESSION_TOKEN_KEY]) {
            $this->setLastError($this->l('Invalid captcha token'));
            $this->log('Invalid captcha token - possible CSRF attack');

            return false;
        }

        // Check expiration
        $timestamp = $_SESSION[self::SESSION_TIMESTAMP_KEY];
        if (time() - $timestamp > self::EXPIRATION_TIME) {
            unset($_SESSION[self::SESSION_KEY], $_SESSION[self::SESSION_TIMESTAMP_KEY], $_SESSION[self::SESSION_TOKEN_KEY]);
            $this->setLastError($this->l('Captcha expired, please refresh the page'));

            return false;
        }

        // Validate answer
        $correctAnswer = $_SESSION[self::SESSION_KEY];
        $isValid = ((int) $userAnswer === $correctAnswer);

        // Clear session (one-time use)
        unset($_SESSION[self::SESSION_KEY], $_SESSION[self::SESSION_TIMESTAMP_KEY], $_SESSION[self::SESSION_TOKEN_KEY]);

        if (!$isValid) {
            $this->setLastError($this->l('Incorrect answer to the math captcha'));
            $this->log(sprintf('Math captcha failed: expected %s, got %s', $correctAnswer, $userAnswer));

            return false;
        }

        $this->log($this->l('Captcha submitted with success'));

        return true;
    }

    /**
     * @inheritDoc
     */
    public function getConfigFields()
    {
        return [
            [
                'type' => 'radio',
                'label' => $this->l('Math Captcha Difficulty'),
                'name' => 'CAPTCHA_MATH_DIFFICULTY',
                'required' => true,
                'values' => [
                    [
                        'id' => 'easy',
                        'value' => 'easy',
                        'label' => $this->l('Easy (1-10)'),
                    ],
                    [
                        'id' => 'medium',
                        'value' => 'medium',
                        'label' => $this->l('Medium (1-50)'),
                    ],
                    [
                        'id' => 'hard',
                        'value' => 'hard',
                        'label' => $this->l('Hard (1-100)'),
                    ],
                ],
                'tab' => 'general',
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public function renderHeader(array $context = [])
    {
        // No external scripts needed for math captcha
        return '';
    }

    /**
     * @inheritDoc
     */
    public function getTemplateVars()
    {
        $vars = parent::getTemplateVars();

        // Generate new math question
        $mathData = $this->generateMathQuestion();

        $vars['mathQuestion'] = $mathData['question'];
        $vars['mathToken'] = $mathData['token'];

        return $vars;
    }

    /**
     * Generate a new math question
     *
     * @return array Array with 'question', 'answer', and 'token'
     */
    protected function generateMathQuestion()
    {
        // Start session if not already started
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        $difficulty = $this->getConfig('CAPTCHA_MATH_DIFFICULTY', 'easy');

        // Determine number range based on difficulty
        switch ($difficulty) {
            case 'hard':
                $max = 100;
                break;
            case 'medium':
                $max = 50;
                break;
            case 'easy':
            default:
                $max = 10;
                break;
        }

        // Generate random numbers
        $num1 = rand(1, $max);
        $num2 = rand(1, $max);

        // Random operation (addition or subtraction)
        $operations = ['+', '-'];
        $operation = $operations[array_rand($operations)];

        // Ensure no negative results for subtraction
        if ($operation === '-' && $num1 < $num2) {
            $temp = $num1;
            $num1 = $num2;
            $num2 = $temp;
        }

        // Calculate answer
        switch ($operation) {
            case '+':
                $answer = $num1 + $num2;
                break;
            case '-':
                $answer = $num1 - $num2;
                break;
            default:
                $answer = $num1 + $num2;
        }

        // Generate CSRF token
        $token = bin2hex(random_bytes(16));

        // Store in session
        $_SESSION[self::SESSION_KEY] = $answer;
        $_SESSION[self::SESSION_TIMESTAMP_KEY] = time();
        $_SESSION[self::SESSION_TOKEN_KEY] = $token;

        // Return question
        return [
            'question' => sprintf('%d %s %d', $num1, $operation, $num2),
            'answer' => $answer,
            'token' => $token,
        ];
    }

    /**
     * @inheritDoc
     */
    public function isConfigured()
    {
        // Math captcha doesn't require external configuration
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getTemplatePath()
    {
        return 'module:eicaptcha/views/templates/hook/providers/math.tpl';
    }
}
