{*
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
*  @author    Hennes Hervé <contact@h-hennes.fr>
*  @copyright Hennes Hervé
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  http://www.h-hennes.fr/blog/
*}

{if $displayCaptcha}
    <div class="form-group row eicaptcha-field eicaptcha-math">
        <label class="col-md-3 form-control-label required">
            {l s='Math Captcha' mod='eicaptcha'}
        </label>
        <div class="col-md-9">
            <div class="math-captcha-question">
                <label for="math-captcha-response">
                    {l s='What is' mod='eicaptcha'} <strong>{$mathQuestion|escape:'html'}</strong> ?
                </label>
                <input type="number"
                       id="math-captcha-response"
                       name="math-captcha-response"
                       class="form-control"
                       required
                       autocomplete="off"
                       style="max-width: 150px; display: inline-block; margin-left: 10px;" />
                <input type="hidden" name="math-captcha-token" value="{$mathToken|escape:'html'}" />
            </div>
            <small class="form-text text-muted">
                {l s='Please solve this simple math problem to verify you are human' mod='eicaptcha'}
            </small>
        </div>
    </div>
{/if}
