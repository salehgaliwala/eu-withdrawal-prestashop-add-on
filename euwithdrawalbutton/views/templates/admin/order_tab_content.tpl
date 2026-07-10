<div class="tab-pane d-print-block fade" id="euwithdrawal-tab" role="tabpanel">
    <div class="card mt-2">
        <div class="card-header">
            <h3 class="card-header-title">{l s='EU Withdrawal Request Details' mod='euwithdrawalbutton'}</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>{l s='Customer Name:' mod='euwithdrawalbutton'}</strong> {$withdrawal_request.customer_name|escape:'html':'UTF-8'}</p>
                    <p><strong>{l s='Email Address:' mod='euwithdrawalbutton'}</strong> {$withdrawal_request.email|escape:'html':'UTF-8'}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>{l s='Request Type:' mod='euwithdrawalbutton'}</strong> {$withdrawal_request.request_type|escape:'html':'UTF-8'}</p>
                    <p><strong>{l s='Date Requested:' mod='euwithdrawalbutton'}</strong> {$withdrawal_request.date_add|escape:'html':'UTF-8'}</p>
                </div>
            </div>
            
            {* Optional: Add a quick link to generate the PDF receipt from here *}
            <div class="mt-4">
                <a class="btn btn-primary" href="{$link->getAdminLink('AdminEuWithdrawalRequests')|escape:'html':'UTF-8'}&amp;id_euwithdrawal_request={$withdrawal_request.id_euwithdrawal_request|intval}&amp;exportpdfeuwithdrawal_requests" target="_blank">
                    <i class="material-icons">picture_as_pdf</i> {l s='Download PDF Receipt' mod='euwithdrawalbutton'}
                </a>
            </div>
        </div>
    </div>
</div>