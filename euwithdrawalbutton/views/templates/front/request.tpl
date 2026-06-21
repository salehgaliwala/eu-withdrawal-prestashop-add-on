{extends file='page.tpl'}

{block name='page_content'}
    <div class="euwithdrawalbutton-form">
        {if isset($order)}
            <h2>{l s='Withdrawal Request for Order #%s' sprintf=[$order->reference] mod='euwithdrawalbutton'}</h2>
        {else}
            <h2>{l s='Withdrawal Request / Order Lookup' mod='euwithdrawalbutton'}</h2>
        {/if}

        {if isset($errors) && $errors}
            <div class="alert alert-danger">
                <ul>
                    {foreach from=$errors item=error}
                        <li>{$error|escape:'htmlall':'UTF-8'}</li>
                    {endforeach}
                </ul>
            </div>
        {/if}

        {if isset($success) && $success}
            <div class="alert alert-success">
                <ul>
                    {foreach from=$success item=s}
                        <li>{$s|escape:'htmlall':'UTF-8'}</li>
                    {endforeach}
                </ul>
            </div>
        {elseif isset($order)}
            <form action="{$action_url|escape:'html':'UTF-8'}" method="post">
                <input type="hidden" name="id_order" value="{$id_order|intval}">
                <input type="hidden" name="secure_key" value="{$secure_key|escape:'htmlall':'UTF-8'}">

                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>{l s='Select' mod='euwithdrawalbutton'}</th>
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

                <div class="form-group">
                    <p class="text-muted">
                        {l s='Note: Virtual and custom products are excluded from withdrawal as per legal requirements.' mod='euwithdrawalbutton'}
                    </p>
                </div>

                <div class="footer">
                    <button type="submit" name="submitWithdrawal" class="btn btn-primary">
                        {l s='Submit Withdrawal Request' mod='euwithdrawalbutton'}
                    </button>
                </div>
            </form>
        {else}
            <form action="{$lookup_action|escape:'html':'UTF-8'}" method="post" class="box">
                <p>{l s='To request a withdrawal, please identify your order.' mod='euwithdrawalbutton'}</p>
                <div class="form-group row">
                    <label class="col-md-3 form-control-label">{l s='Order Reference' mod='euwithdrawalbutton'}</label>
                    <div class="col-md-6">
                        <input type="text" name="order_reference" class="form-control" required>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-md-3 form-control-label">{l s='Email Address' mod='euwithdrawalbutton'}</label>
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
        {/if}
    </div>
{/block}
