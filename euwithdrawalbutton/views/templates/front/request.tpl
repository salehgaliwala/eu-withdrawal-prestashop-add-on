{extends file='page.tpl'}

{block name='page_content'}
    <div class="euwithdrawalbutton-form">
        <h2>{l s='Withdrawal Request Form' mod='euwithdrawalbutton'}</h2>

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
            <form action="{$action_url|escape:'html':'UTF-8'}" method="post" id="withdrawal-form" class="box">
                <div class="form-group row">
                    <label class="col-md-3 form-control-label required">{l s='Name' mod='euwithdrawalbutton'}</label>
                    <div class="col-md-6">
                        <input type="text" name="customer_name" class="form-control" value="{$customer_name|escape:'htmlall':'UTF-8'}" required>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-md-3 form-control-label required">{l s='Order Number / Reference' mod='euwithdrawalbutton'}</label>
                    <div class="col-md-6">
                        <input type="text" name="order_reference" class="form-control" value="{$order_reference|escape:'htmlall':'UTF-8'}" required>
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
                    <div id="items-container">
                        <div class="item-row row form-group">
                            <div class="col-md-4">
                                <input type="text" name="item_name[]" class="form-control" placeholder="{l s='Item name' mod='euwithdrawalbutton'}">
                            </div>
                            <div class="col-md-3">
                                <input type="text" name="item_number[]" class="form-control" placeholder="{l s='Item number' mod='euwithdrawalbutton'}">
                            </div>
                            <div class="col-md-2">
                                <input type="number" name="item_quantity[]" class="form-control" placeholder="{l s='Qty' mod='euwithdrawalbutton'}" min="1">
                            </div>
                            <div class="col-md-3">
                                <button type="button" class="btn btn-danger remove-item">{l s='Remove' mod='euwithdrawalbutton'}</button>
                            </div>
                        </div>
                    </div>
                    <button type="button" id="add-item" class="btn btn-secondary">{l s='Add Another Item' mod='euwithdrawalbutton'}</button>
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
                    const itemsContainer = document.getElementById('items-container');
                    const addItemBtn = document.getElementById('add-item');

                    lineItemsRadio.forEach(radio => {
                        radio.addEventListener('change', function() {
                            if (this.value === 'line_items') {
                                lineItemsSection.style.display = 'block';
                            } else {
                                lineItemsSection.style.display = 'none';
                            }
                        });
                    });

                    addItemBtn.addEventListener('click', function() {
                        const newRow = itemsContainer.querySelector('.item-row').cloneNode(true);
                        newRow.querySelectorAll('input').forEach(input => input.value = '');
                        itemsContainer.appendChild(newRow);
                    });

                    itemsContainer.addEventListener('click', function(e) {
                        if (e.target.classList.contains('remove-item')) {
                            if (itemsContainer.querySelectorAll('.item-row').length > 1) {
                                e.target.closest('.item-row').remove();
                            }
                        }
                    });
                });
            </script>
        {/if}
    </div>
{/block}
