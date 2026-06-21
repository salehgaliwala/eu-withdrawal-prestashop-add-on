<?php
declare(strict_types=1);
if (!defined("_PS_VERSION_")) { exit; }

class HTMLTemplateWithdrawalReceipt extends HTMLTemplate
{
    public $withdrawal_request;
    public $order;
    public $withdrawal_data;
    public $ip_address;
    public $user_agent;
    public $date_now;

    public function __construct($withdrawal_request, $smarty)
    {
        $this->withdrawal_request = $withdrawal_request;
        $this->smarty = $smarty;
        $this->order = new Order((int)$withdrawal_request->id_order);
        $this->withdrawal_data = json_decode($withdrawal_request->items_data, true);
        $this->ip_address = $withdrawal_request->ip_address;
        $this->user_agent = $withdrawal_request->user_agent;
        $this->date_now = $withdrawal_request->date_add;

        // Header tab
        $this->title = HTMLTemplateWithdrawalReceipt::l('Withdrawal Receipt');

        $this->shop = new Shop((int)$this->order->id_shop);
    }

    public function getContent()
    {
        $this->smarty->assign(array(
            'order' => $this->order,
            'withdrawal_data' => $this->withdrawal_data,
            'ip_address' => $this->ip_address,
            'user_agent' => $this->user_agent,
            'date_now' => $this->date_now,
        ));

        return $this->smarty->fetch(_PS_MODULE_DIR_ . 'euwithdrawalbutton/views/templates/admin/pdf/content.tpl');
    }

    public function getLogo()
    {
        $id_shop = (int)$this->order->id_shop;
        $logo = _PS_IMG_DIR_ . Configuration::get('PS_LOGO', null, null, $id_shop);
        return $logo;
    }

    public function getFilename()
    {
        return 'withdrawal_receipt_' . $this->order->reference . '.pdf';
    }

    public function getBulkFilename()
    {
        return 'withdrawal_receipts.pdf';
    }
}
