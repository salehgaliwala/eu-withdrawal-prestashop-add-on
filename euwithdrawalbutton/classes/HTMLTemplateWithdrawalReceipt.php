<?php
declare(strict_types=1);

if (!defined("_PS_VERSION_")) { exit; }

class HTMLTemplateWithdrawalReceipt extends HTMLTemplate
{
    public $withdrawal_request;
    public $withdrawal_data;
    public $date_now;

    public function __construct($withdrawal_request, $smarty)
    {
        $this->withdrawal_request = $withdrawal_request;
        $this->smarty = $smarty;
        $this->withdrawal_data = json_decode($withdrawal_request->items_data, true);
        $this->date_now = $withdrawal_request->date_add;

        // Header tab
        $this->title = HTMLTemplateWithdrawalReceipt::l('Withdrawal Receipt');
        $this->shop = new Shop((int)Context::getContext()->shop->id);
    }

    public function getContent()
    {
        $this->smarty->assign(array(
            'request' => $this->withdrawal_request,
            'withdrawal_data' => $this->withdrawal_data,
            'date_now' => $this->date_now,
        ));

        return $this->smarty->fetch(_PS_MODULE_DIR_ . 'euwithdrawalbutton/views/templates/admin/pdf/content.tpl');
    }

    public function getLogo()
    {
        $logo = Configuration::get('PS_LOGO');
        if ($logo && file_exists($logo)) {
            return $logo;
        }
        if ($logo && file_exists(_PS_IMG_DIR_ . $logo)) {
            return _PS_IMG_DIR_ . $logo;
        }
        return parent::getLogo();
    }

    public function getFilename()
    {
        return 'withdrawal_receipt_' . $this->withdrawal_request->order_reference . '.pdf';
    }

    public function getBulkFilename()
    {
        return 'withdrawal_receipts.pdf';
    }
}
