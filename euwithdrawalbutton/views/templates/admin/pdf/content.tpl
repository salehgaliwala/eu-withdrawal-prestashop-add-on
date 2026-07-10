<div style="font-family: Arial, sans-serif;">
    <h1>{l s='Withdrawal Request Receipt' pdf='true'}</h1>
    <p><strong>{l s='Customer Name:' pdf='true'}</strong> {$request->customer_name|escape:'htmlall':'UTF-8'}</p>
    <p><strong>{l s='Order Reference:' pdf='true'}</strong> {$request->order_reference|escape:'htmlall':'UTF-8'}</p>
    <p><strong>{l s='Email:' pdf='true'}</strong> {$request->email|escape:'htmlall':'UTF-8'}</p>
    <p><strong>{l s='Request Type:' pdf='true'}</strong> {if $request->request_type == 'entire_order'}{l s='Entire Order' pdf='true'}{else}{l s='Partial (Line Items)' pdf='true'}{/if}</p>
    <p><strong>{l s='Date/Time:' pdf='true'}</strong> {$date_now|escape:'htmlall':'UTF-8'}</p>
    <p><strong>{l s='IP Address:' pdf='true'}</strong> {$request->ip_address|escape:'htmlall':'UTF-8'}</p>
    <p><strong>{l s='User Agent:' pdf='true'}</strong> {$request->user_agent|escape:'htmlall':'UTF-8'}</p>

    {if $request->request_type == 'line_items'}
    <h2>{l s='Items Selected for Withdrawal:' pdf='true'}</h2>
    <table border="1" cellpadding="5" style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="background-color: #f2f2f2;">
                <th>{l s='Product Name' pdf='true'}</th>
                <th>{l s='Product Reference' pdf='true'}</th>
                <th>{l s='Quantity' pdf='true'}</th>
            </tr>
        </thead>
        <tbody>
            {foreach from=$withdrawal_data item=item}
                <tr>
                    <td>{$item.product_name|escape:'htmlall':'UTF-8'}</td>
                    <td>{$item.product_reference|escape:'htmlall':'UTF-8'}</td>
                    <td style="text-align: center;">{$item.quantity|intval}</td>
                </tr>
            {/foreach}
        </tbody>
    </table>
    {/if}

    <div style="margin-top: 50px; font-size: 10px; color: #555;">
        <p>{l s='This is an automated acknowledgment of your withdrawal request.' pdf='true'}</p>
    </div>
</div>
