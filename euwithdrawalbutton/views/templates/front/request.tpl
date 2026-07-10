{extends file='page.tpl'}

{block name='page_content'}
    <div class="euwithdrawalbutton-form">
        <h2>{l s='Withdrawal Request Form' mod='euwithdrawalbutton'}</h2>

        {if isset($errors) && $errors}
            <div class="alert alert-danger">
                <ul>
                    {foreach from=$errors item=error}
                        <li>{$error|escape:'htmlall':'UTF-8'}</li>
                    {/foreach}
                </ul>
            </div>
        {/if}

        {if isset($success) && $success}
            <div class="alert alert-success">
                <ul>
                    {foreach from=$success item=s}
                        <li>{$s|escape:'htmlall':'UTF-8'}</li>
                    {/foreach}
                </ul>
            </div>
        {else}
            {if !$is_valid_order}
                <form action="{$action_url|escape:'html':'UTF-8'}" method="post" class="box">
                    <p>{l s='To request a withdrawal, please identify your order.' mod='euwithdrawalbutton'}</p>
                    <div class="form-group row">
                        <label class="col-md-3 form-control-label required">{l s='Order Reference' mod='euwithdrawalbutton'}</label>
                        <div class="col-md-6">
                            <input type="text" name="order_reference" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-3 form-control-label required">{l s='Email Address' mod='euwithdrawalbutton'}</label>
                        <div class="col-md-6">
                            <input type="email" name="email" class="form-control" required>
                        </div>
                    </div>
                    <div class="footer text-sm-center">
                        <button type="submit" name="submitLookup" class="btn btn-primary">
                            {l s='Lookup Order' mod='euwithdrawalbutton'}
                        </button>
                    </div>
                </form>
            {else}
                <form action="{$action_url|escape:'html':'UTF-8'}" method="post" id="withdrawal-form" class="box">
                    <input type="hidden" name="id_order" value="{$order->id|intval}">
                    <input type="hidden" name="secure_key" value="{$order->secure_key|escape:'htmlall':'UTF-8'}">

                    <div class="form-group row">
                        <label class="col-md-3 form-control-label required">{l s='Name' mod='euwithdrawalbutton'}</label>
                        <div class="col-md-6">
                            <input type="text" name="customer_name" class="form-control" value="{$customer_name|escape:'htmlall':'UTF-8'}" required>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 form-control-label">{l s='Order Number / Reference' mod='euwithdrawalbutton'}</label>
                        <div class="col-md-6">
                            <input type="text" class="form-control" value="{$order->reference|escape:'htmlall':'UTF-8'}" readonly disabled>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 form-control-label required">{l s='Email for receipt confirmation' mod='euwithdrawalbutton'}</label>
                        <div class="col-md-6">
                            <input type="email" name="email" class="form-control" value="{$email|escape:'htmlall':'UTF-8'}" required>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-md-3 form-control-label required">{l s='What would you like to withdraw?' mod='euwithdrawalbutton'}</label>
                        <div class="col-md-9">
                            <div class="radio">
                                <label>
                                    <input type="radio" name="request_type" value="entire_order" checked>
                                    {l s='Withdraw the entire order' mod='euwithdrawalbutton'}
                                </label>
                            </div>
                            <div class="radio">
                                <label>
                                    <input type="radio" name="request_type" value="line_items" id="radio-line-items">
                                    {l s='Withdraw the line items' mod='euwithdrawalbutton'}
                                </label>
                            </div>
                        </div>
                    </div>

                    <div id="line-items-section" style="display: none;">
                        <hr>
                        <h4>{l s='Item Details' mod='euwithdrawalbutton'}</h4>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>{l s='Select' mod='euwithdrawalbutton'}</th>
                                    <th>{l s='Item Number' mod='euwithdrawalbutton'}</th>
                                    <th>{l s='Product' mod='euwithdrawalbutton'}</th>
                                    <th>{l s='Quantity' mod='euwithdrawalbutton'}</th>
                                </tr>
                            </thead>
                            <tbody>
                                {foreach from=$products item=product}
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="selected_items[]" value="{$product.id_order_detail|intval}">
                                        </td>
                                        <td>{$product.product_reference|escape:'htmlall':'UTF-8'}</td>
                                        <td>{$product.product_name|escape:'htmlall':'UTF-8'}</td>
                                        <td>
                                            <select name="quantity[{$product.id_order_detail|intval}]" class="form-control" style="width: 80px;">
                                                {for $i=1 to $product.product_quantity}
                                                    <option value="{$i|intval}" {if $i == $product.product_quantity}selected{/if}>{$i|intval}</option>
                                                {/for}
                                            </select>
                                        </td>
                                    </tr>
                                {/foreach}
                            </tbody>
                        </table>
                        <p class="text-muted">
                            {l s='Note: Virtual and custom products are excluded from withdrawal as per legal requirements.' mod='euwithdrawalbutton'}
                        </p>
                    </div>

                    <div class="footer text-sm-center mt-3">
                        <button type="submit" name="submitWithdrawal" class="btn btn-primary">
                            {l s='Submit Withdrawal Request' mod='euwithdrawalbutton'}
                        </button>
                    </div>
                </form>

                <script type="text/javascript">
                    document.addEventListener('DOMContentLoaded', function() {
                        const lineItemsRadio = document.querySelectorAll('input[name="request_type"]');
                        const lineItemsSection = document.getElementById('line-items-section');

                        lineItemsRadio.forEach(radio => {
                            radio.addEventListener('change', function() {
                                if (this.value === 'line_items') {
                                    lineItemsSection.style.display = 'block';
                                } else {
                                    lineItemsSection.style.display = 'none';
                                }
                            });
                        });
                    });
                </script>
            {/if}
        {/if}
    </div>
{/block}
