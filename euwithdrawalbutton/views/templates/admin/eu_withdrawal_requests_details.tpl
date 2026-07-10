{*
 * 2024 PrestaShop
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
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2024 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 *}

<div class="panel">
    <div class="panel-heading">
        <i class="icon-info"></i> {l s='Withdrawal Request Details' mod='euwithdrawalbutton'}
    </div>
    <div class="form-horizontal">
        <div class="row">
            <label class="control-label col-lg-3">{l s='Order Reference' mod='euwithdrawalbutton'}:</label>
            <div class="col-lg-9">
                <p class="form-control-static">{$request->order_reference|escape:'htmlall':'UTF-8'}</p>
            </div>
        </div>
        <div class="row">
            <label class="control-label col-lg-3">{l s='Customer Name' mod='euwithdrawalbutton'}:</label>
            <div class="col-lg-9">
                <p class="form-control-static">{$request->customer_name|escape:'htmlall':'UTF-8'}</p>
            </div>
        </div>
        <div class="row">
            <label class="control-label col-lg-3">{l s='Email' mod='euwithdrawalbutton'}:</label>
            <div class="col-lg-9">
                <p class="form-control-static">{$request->email|escape:'htmlall':'UTF-8'}</p>
            </div>
        </div>
        <div class="row">
            <label class="control-label col-lg-3">{l s='Request Type' mod='euwithdrawalbutton'}:</label>
            <div class="col-lg-9">
                <p class="form-control-static">
                    {if $request->request_type == 'entire_order'}
                        {l s='Entire Order' mod='euwithdrawalbutton'}
                    {else}
                        {l s='Partial (Line Items)' mod='euwithdrawalbutton'}
                    {/if}
                </p>
            </div>
        </div>
        <div class="row">
            <label class="control-label col-lg-3">{l s='Date' mod='euwithdrawalbutton'}:</label>
            <div class="col-lg-9">
                <p class="form-control-static">{$request->date_add|escape:'htmlall':'UTF-8'}</p>
            </div>
        </div>
        <div class="row">
            <label class="control-label col-lg-3">{l s='IP Address' mod='euwithdrawalbutton'}:</label>
            <div class="col-lg-9">
                <p class="form-control-static">{$request->ip_address|escape:'htmlall':'UTF-8'}</p>
            </div>
        </div>
    </div>
</div>

{if $request->request_type == 'line_items'}
<div class="panel">
    <div class="panel-heading">
        <i class="icon-list"></i> {l s='Withdrawn Items' mod='euwithdrawalbutton'}
    </div>
    <table class="table">
        <thead>
            <tr>
                <th>{l s='Product Name' mod='euwithdrawalbutton'}</th>
                <th>{l s='Product Reference' mod='euwithdrawalbutton'}</th>
                <th>{l s='Quantity' mod='euwithdrawalbutton'}</th>
            </tr>
        </thead>
        <tbody>
            {foreach from=$items item=item}
                <tr>
                    <td>{$item.product_name|escape:'htmlall':'UTF-8'}</td>
                    <td>{$item.product_reference|escape:'htmlall':'UTF-8'}</td>
                    <td>{$item.quantity|intval}</td>
                </tr>
            {/foreach}
        </tbody>
    </table>
</div>
{/if}
