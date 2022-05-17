{*
* 2007-2021 PrestaShop
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
{* This template is used to be displayed the captcha in custom forms *}
{if $displayCaptcha}
    <div class="eicaptcha-captcha-field">
        {if $captchaVersion == 2}
            <div class="g-recaptcha" data-sitekey="{$publicKey|escape:'html'}" id="captcha-box-custom"
                 data-theme="{$captchatheme}"></div>
            <script src="https://www.google.com/recaptcha/api.js?hl={$captchalang}"
                    async defer></script>
        {else}
            <input type="hidden" id="captcha-box-custom" name="g-recaptcha-response"/>
            <script src="https://www.google.com/recaptcha/api.js?render={$publicKey|escape:'html'}"></script>
            <script>
                grecaptcha.ready(function () {ldelim}
                    grecaptcha.execute('{$publicKey|escape:'html'}', {ldelim}action: 'contact'{rdelim}).then(function (token) {ldelim}
                        var recaptchaResponse = document.getElementById('captcha-box-custom');
                        recaptchaResponse.value = token;
                        {rdelim});
                    {rdelim});
            </script>
        {/if}
    </div>
{/if}
