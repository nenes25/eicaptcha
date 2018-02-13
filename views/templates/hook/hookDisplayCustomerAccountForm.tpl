{*
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
*  @copyright Hennes Hervé
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  http://www.h-hennes.fr/blog/
*}

<label>{l s='Captcha' mod='eicaptcha'}</label>
{**
 * Le contenu du captcha est automatiquement ajouté dans le selecteur #captcha-box
 * Captcha content is automaticaly added into the selector #captcha-box
 *}
<div class="g-recaptcha row" data-sitekey="{$publicKey|escape:'htmlall':'UTF-8'}" id="captcha-box" data-theme="{$captchatheme|escape:'htmlall':'UTF-8'}"></div>

{* Les variables nécessaires au bon fonctionnement du plugin *}
{addJsDefL name='waiting_message'}{l s='Please wait during captcha check' mod='eicaptcha' js=1}{/addJsDefL}
{addJsDef checkCaptchaUrl=$checkCaptchaUrl|escape:'html'}
{addJsDef errorSelector=$checkCaptchaUrl|escape:'html'}
{addJsDef checkCaptchaUrl=$errorSelector|escape:'html'}
{addJsDef formSelector=$formSelector|escape:'html'}
<script src="https://www.google.com/recaptcha/api.js?hl={$captchaforcelang|escape:'htmlall':'UTF-8'}" async defer></script>
