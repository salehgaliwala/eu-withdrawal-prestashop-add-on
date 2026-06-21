<?php
declare(strict_types=1);

if (!defined("_PS_VERSION_")) { exit; }
/**
 * 2024 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2024 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

class EuWithdrawalButtonRequestModuleFrontController extends ModuleFrontController
{
    public $auth = false;
    public $guestAllowed = true;

    public function initContent()
    {
        parent::initContent();

        $id_order = (int)Tools::getValue('id_order');
        $order_reference = '';
        if ($id_order) {
            $order = new Order($id_order);
            if (Validate::isLoadedObject($order)) {
                $order_reference = $order->reference;
            }
        }

        $this->context->smarty->assign([
            'action_url' => $this->context->link->getModuleLink('euwithdrawalbutton', 'request'),
            'customer_name' => $this->context->customer->id ? $this->context->customer->firstname . ' ' . $this->context->customer->lastname : '',
            'email' => $this->context->customer->id ? $this->context->customer->email : '',
            'order_reference' => $order_reference,
        ]);

        $this->setTemplate('module:euwithdrawalbutton/views/templates/front/request.tpl');
    }

    public function postProcess()
    {
        if (Tools::isSubmit('submitWithdrawal')) {
            $customer_name = Tools::getValue('customer_name');
            $order_reference = Tools::getValue('order_reference');
            $email = Tools::getValue('email');
            $request_type = Tools::getValue('request_type');

            if (empty($customer_name) || empty($order_reference) || empty($email) || !Validate::isEmail($email)) {
                $this->errors[] = $this->module->l('Please fill all required fields correctly.');
                return;
            }

            $id_order = (int)Db::getInstance()->getValue('SELECT id_order FROM ' . _DB_PREFIX_ . 'orders WHERE reference = "' . pSQL($order_reference) . '"');
            $order = new Order($id_order);

            $withdrawal_data = array();
            if ($request_type === 'line_items') {
                $item_names = Tools::getValue('item_name');
                $item_numbers = Tools::getValue('item_number');
                $item_quantities = Tools::getValue('item_quantity');

                if (!empty($item_names) && is_array($item_names)) {
                    foreach ($item_names as $key => $name) {
                        if (empty($name)) continue;
                        $withdrawal_data[] = array(
                            'product_name' => $name,
                            'product_number' => $item_numbers[$key],
                            'quantity' => (int)$item_quantities[$key]
                        );
                    }
                }

                if (empty($withdrawal_data)) {
                    $this->errors[] = $this->module->l('Please provide at least one item detail for partial withdrawal.');
                    return;
                }
            } else {
                $withdrawal_data = array('type' => 'entire_order');
            }

            include_once(_PS_MODULE_DIR_ . 'euwithdrawalbutton/classes/EuWithdrawalRequest.php');
            $request = new EuWithdrawalRequest();
            $request->id_order = $id_order;
            $request->id_customer = (int)$order->id_customer;
            $request->customer_name = $customer_name;
            $request->order_reference = $order_reference;
            $request->email = $email;
            $request->request_type = $request_type;
            $request->items_data = json_encode($withdrawal_data);
            $request->ip_address = Tools::getRemoteAddr();
            $request->user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $request->date_add = date('Y-m-d H:i:s');

            if ($request->add()) {
                if (Validate::isLoadedObject($order)) {
                    $new_state_id = (int)Configuration::get('EU_WITHDRAWAL_STATE_ID');
                    if ($new_state_id) {
                        $history = new OrderHistory();
                        $history->id_order = (int)$order->id;
                        $history->changeIdOrderState($new_state_id, (int)$order->id);
                        $history->addWithemail(true);
                    }
                }

                $this->sendNotifications($request);
                $this->success[] = $this->module->l('Your withdrawal request has been submitted successfully.');
            } else {
                $this->errors[] = $this->module->l('An error occurred while saving your request.');
            }
        }
    }

    protected function sendNotifications($request)
    {
        $items_html = '<ul>';
        if ($request->request_type === 'entire_order') {
            $items_html .= '<li>' . $this->module->l('Entire Order') . '</li>';
        } else {
            $withdrawal_data = json_decode($request->items_data, true);
            foreach ($withdrawal_data as $item) {
                $items_html .= '<li>' . $item['product_name'] . ' (#' . $item['product_number'] . ') x' . $item['quantity'] . '</li>';
            }
        }
        $items_html .= '</ul>';

        $template_vars = [
            '{customer_name}' => $request->customer_name,
            '{order_reference}' => $request->order_reference,
            '{email}' => $request->email,
            '{request_type}' => ($request->request_type === 'entire_order' ? 'Entire Order' : 'Partial (Line Items)'),
            '{date}' => $request->date_add,
            '{ip_address}' => $request->ip_address,
            '{user_agent}' => $request->user_agent,
            '{items}' => $items_html,
        ];

        // Generate PDF
        include_once(_PS_MODULE_DIR_ . 'euwithdrawalbutton/classes/HTMLTemplateWithdrawalReceipt.php');
        $pdf_obj = new PDF($request, 'WithdrawalReceipt', Context::getContext()->smarty);
        $pdf_content = $pdf_obj->render(false);
        $file_attachment = array(
            'content' => $pdf_content,
            'name' => 'withdrawal_receipt_' . $request->order_reference . '.pdf',
            'mime' => 'application/pdf',
        );

        // Send to Customer
        Mail::Send(
            (int)$this->context->language->id,
            'withdrawal_conf',
            Mail::l('Withdrawal Request Acknowledgment', (int)$this->context->language->id),
            $template_vars,
            $request->email,
            $request->customer_name,
            null,
            null,
            $file_attachment,
            null,
            _PS_MODULE_DIR_ . 'euwithdrawalbutton/mails/'
        );

        // Send to Admin
        Mail::Send(
            (int)$this->context->language->id,
            'withdrawal_admin',
            Mail::l('New Withdrawal Request Received', (int)$this->context->language->id),
            $template_vars,
            Configuration::get('PS_SHOP_EMAIL'),
            null,
            null,
            null,
            $file_attachment,
            null,
            _PS_MODULE_DIR_ . 'euwithdrawalbutton/mails/'
        );
    }
}
