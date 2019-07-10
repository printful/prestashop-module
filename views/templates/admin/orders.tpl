{*
 * PrestaShop module for Printful
 *
 * @author    Printful
 * @copyright 2019 Printful
 * @license   GPL 3 license see LICENSE.txt
 *}

<div class="panel col-lg-12">
    <div class="panel-heading">
        {l s='Latest orders' mod='printful'}
    </div>
    {if $orders ne ''}
        <table class="table printful-latest-orders">
            <thead>
            <tr>
                <th class="col-order">{l s='Order' mod='printful'}</th>
                <th class="col-date">{l s='Date' mod='printful'}</th>
                <th class="col-from">{l s='From' mod='printful'}</th>
                <th class="col-status">{l s='Status' mod='printful'}</th>
                <th class="col-total">{l s='Total' mod='printful'}</th>
                <th class="col-actions">{l s='Action' mod='printful'}</th>
            </tr>
            </thead>
            <tbody>
            {foreach from=$orders item=order}
                <tr>
                    <td>#{$order.external_id|escape:'htmlall':'UTF-8'}</td>
                    <td>{$order.created|escape:'htmlall':'UTF-8'}</td>
                    <td>{$order.recipient.name|escape:'htmlall':'UTF-8'}</td>
                    <td>{$order.status|escape:'htmlall':'UTF-8'}</td>
                    <td>{$order.costs.total|escape:'htmlall':'UTF-8'}</td>
                    <td>
                        <a href="{$order.link|escape:'html':'UTF-8'}" target="_blank">{l s='Open in Printful'  mod='printful'}</a>
                    </td>
                </tr>
            {/foreach}
        </table>
    {else}
        <div class="printful-latest-orders">
            <p>{l s='Once your store gets some Printful product orders, they will be shown here!'  mod='printful'}</p>
        </div>
    {/if}
</div>