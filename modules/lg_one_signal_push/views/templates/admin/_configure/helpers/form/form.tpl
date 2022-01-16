

{extends file="helpers/form/form.tpl"}

{block name="defaultForm"}

    {if (isset($form_errors)) && (count($form_errors) > 0)}
        <div class="alert alert-danger">
            <h4>{l s='Error!' mod='lg_one_signal_push'}</h4>
            <ul class="list-unstyled">
            {foreach from=$form_errors item='message'}
                <li>{$message|escape:'htmlall':'UTF-8'}</li>
            {/foreach}
            </ul>
        </div>
    {/if}

    {if (isset($form_infos)) && (count($form_infos) > 0)}
        <div class="alert alert-warning">
            <h4>{l s='Warning!' mod='lg_one_signal_push'}</h4>
            <ul class="list-unstyled">
            {foreach from=$form_infos item='message'}
                <li>{$message|escape:'htmlall':'UTF-8'}</li>
            {/foreach}
            </ul>
        </div>
    {/if}

    {if (isset($form_successes)) && (count($form_successes) > 0)}
        <div class="alert alert-success">
{*            <h4>{l s='Success!' mod='cronjobs'}</h4>*}
            <h4>{l s='Success!' mod='lg_one_signal_push'}</h4>
            <ul class="list-unstyled">
            {foreach from=$form_successes item='message'}
                <li>{$message|escape:'htmlall':'UTF-8'}</li>
            {/foreach}
            </ul>
        </div>
    {/if}

    {$smarty.block.parent}
{/block}
