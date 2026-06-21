<div style="font-family: Arial, sans-serif;">
    <h1>{l s='Withdrawal Request Receipt' pdf='true'}</h1>
    <p><strong>{l s='Order Reference:' pdf='true'}</strong> {$order->reference|escape:'htmlall':'UTF-8'}</p>
    <p><strong>{l s='Date/Time:' pdf='true'}</strong> {$date_now|escape:'htmlall':'UTF-8'}</p>
    <p><strong>{l s='IP Address:' pdf='true'}</strong> {$ip_address|escape:'htmlall':'UTF-8'}</p>
    <p><strong>{l s='User Agent:' pdf='true'}</strong> {$user_agent|escape:'htmlall':'UTF-8'}</p>

    <h2>{l s='Items Selected for Withdrawal:' pdf='true'}</h2>
    <table border="1" cellpadding="5" style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="background-color: #f2f2f2;">
                <th>{l s='Product Name' pdf='true'}</th>
                <th>{l s='Quantity' pdf='true'}</th>
            </tr>
        </thead>
        <tbody>
            {foreach from=$withdrawal_data item=item}
                <tr>
                    <td>{$item.product_name|escape:'htmlall':'UTF-8'}</td>
                    <td style="text-align: center;">{$item.quantity|intval}</td>
                </tr>
            {/foreach}
        </tbody>
    </table>

    <div style="margin-top: 50px; font-size: 10px; color: #555;">
        <p>{l s='This is an automated acknowledgment of your withdrawal request.' pdf='true'}</p>
    </div>
</div>
