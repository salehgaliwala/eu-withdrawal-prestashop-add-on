{extends file='page.tpl'}

{block name='page_content'}
    <div class="euwithdrawalbutton-form">
        <h2>{l s='Withdrawal Request for Order #%s' sprintf=[$order->reference] mod='euwithdrawalbutton'}</h2>

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
        {else}
            <form action="{$action_url|escape:'html':'UTF-8'}" method="post">
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
        {/if}
    </div>
{/block}
