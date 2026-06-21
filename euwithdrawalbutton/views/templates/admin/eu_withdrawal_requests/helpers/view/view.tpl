{extends file="helpers/view/view.tpl"}

{block name="override_value"}
    <div class="panel">
        <div class="panel-heading">
            <i class="icon-info"></i> {l s='Withdrawal Request Details' mod='euwithdrawalbutton'}
        </div>
        <div class="form-horizontal">
            <div class="row">
                <label class="control-label col-lg-3">{l s='Order Reference' mod='euwithdrawalbutton'}:</label>
                <div class="col-lg-9">
                    <p class="form-control-static">
                        <a href="index.php?controller=AdminOrders&id_order={$order->id|intval}&vieworder&token={getAdminToken tab='AdminOrders'}">
                            {$order->reference|escape:'htmlall':'UTF-8'}
                        </a>
                    </p>
                </div>
            </div>
            <div class="row">
                <label class="control-label col-lg-3">{l s='Customer' mod='euwithdrawalbutton'}:</label>
                <div class="col-lg-9">
                    <p class="form-control-static">
                        <a href="index.php?controller=AdminCustomers&id_customer={$customer->id|intval}&viewcustomer&token={getAdminToken tab='AdminCustomers'}">
                            {$customer->firstname|escape:'htmlall':'UTF-8'} {$customer->lastname|escape:'htmlall':'UTF-8'}
                        </a>
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
            <div class="row">
                <label class="control-label col-lg-3">{l s='User Agent' mod='euwithdrawalbutton'}:</label>
                <div class="col-lg-9">
                    <p class="form-control-static">{$request->user_agent|escape:'htmlall':'UTF-8'}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="panel">
        <div class="panel-heading">
            <i class="icon-list"></i> {l s='Withdrawn Items' mod='euwithdrawalbutton'}
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th>{l s='Product Name' mod='euwithdrawalbutton'}</th>
                    <th>{l s='Quantity' mod='euwithdrawalbutton'}</th>
                </tr>
            </thead>
            <tbody>
                {foreach from=$items item=item}
                    <tr>
                        <td>{$item.product_name|escape:'htmlall':'UTF-8'}</td>
                        <td>{$item.quantity|intval}</td>
                    </tr>
                {/foreach}
            </tbody>
        </table>
    </div>
{/block}
