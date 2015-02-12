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

$(document).ready(function () {

    /**
     * Mise en place du captcha sur la page de création de compte
     * Vérification du captcha au submit
     */
    $("#submitAccount").live("click", function () {

        //Affichage du message de chargement
        if ($(".load").length)
            $(".load").html("").html(waiting_message);
        else
            $(this).after("<div class=\"load\">" + waiting_message + "</div>");

        //Vérification du captcha
        if (!grecaptcha.getResponse()) {
            $.ajax({
                method: "POST",
                url: checkCaptchaUrl,
                data: "action=display_captcha_error",
                success: function (msg) {
                    $(".load").html("");
                    $(errorSelector).remove();
                    $(formSelector).before(msg);
                    $("html, body").animate({scrollTop:$(errorSelector).offset().top  },"slow");
                }
            });

            return false;
        }
        else {
            $(".load").html("");
        }
    });
});