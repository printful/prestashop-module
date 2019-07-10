{*
 * PrestaShop module for Printful
 *
 * @author    Printful
 * @copyright 2019 Printful
 * @license   GPL 3 license see LICENSE.txt
 *}

<h2>{l s='Quick links'  mod='printful'}</h2>
<div class="printful-quick-links">
    {foreach from=$shortcuts item=item}
        <a href="{$item.link|escape:'html':'UTF-8'}" target="_blank" class="printful-quick-links-item">
            <i class="material-icons">{$item.icon|escape:'htmlall':'UTF-8'}</i>
            <h4>{$item.label|escape:'htmlall':'UTF-8'}</h4>
        </a>
    {/foreach}
</div>