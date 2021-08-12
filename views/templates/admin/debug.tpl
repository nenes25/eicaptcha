{if $errors|@count}
    <div class="alert alert-warning">
        <h4>{l s='Errors' mod='eicaptcha'}</h4>
        <ul>
            {foreach from=$errors item=error}
                <li>{$error|html_entity_decode}</li>
            {/foreach}
        </ul>
    </div>
{/if}

{if $success|@count}
    <div class="alert alert-success">
        <h4>{l s='Success' mod='eicaptcha'}</h4>
        <ul>
            {foreach from=$success item=msg}
                <li>{$msg}</li>
            {/foreach}
        </ul>
    </div>
{/if}

<div class="alert alert-info">
    <h4>{l s='Additional information' mod='eicaptcha'}</h4>
    <ul>
        <li>{l s='Recaptcha version' mod='eicaptcha'} : {$recaptchaVersion}</li>
        <li>{l s='Prestashop version' mod='eicaptcha'} : {$prestashopVersion}</li>
        <li>{l s='Theme name' mod='eicaptcha'} : {$themeName}</li>
        <li>{l s='Php version' mod='eicaptcha'} : {$phpVersion}</li>
    </ul>
    <p>&nbsp;</p>
    <p>{l s='In case of problem please open an issue with the asked information on' mod='eicaptcha'} : <a href="https://github.com/nenes25/eicaptcha/issues" target="_blank">github</a></p>
</div>