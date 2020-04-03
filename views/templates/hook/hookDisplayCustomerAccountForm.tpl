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

<div class="form-group row">
    <label class="col-md-3 form-control-label">{l s='Captcha' mod='eicaptcha'}</label>
    {**
     * Le contenu du captcha est automatiquement ajouté dans le selecteur #captcha-box
     * Captcha content is automaticaly added into the selector #captcha-box
     *}
     <div class="col-md-9">
        <div class="g-recaptcha" data-sitekey="{$publicKey|escape:'html'}" id="captcha-box" data-theme="{$captchatheme}"></div>
     </div>
</div>
