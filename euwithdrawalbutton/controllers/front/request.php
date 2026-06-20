<?php
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
        $order = new Order($id_order);

        if (!Validate::isLoadedObject($order)) {
            Tools::redirect('index.php?controller=history');
        }

        // Security check for both registered customers and guests
        if ($this->context->customer->id) {
            if ($this->context->customer->id != $order->id_customer) {
                Tools::redirect('index.php?controller=history');
            }
        } else {
            // Guest check: must provide secure_key if not logged in
            $secure_key = Tools::getValue('secure_key');
            if ($secure_key != $order->secure_key) {
                Tools::redirect('index.php?controller=history');
            }
        }

        $products = $order->getProducts();
        $eligible_products = array();

        foreach ($products as $product) {
            // Legal Exclusions: filter out virtual and custom products
            $is_virtual = (bool)Db::getInstance()->getValue('SELECT is_virtual FROM ' . _DB_PREFIX_ . 'product WHERE id_product = ' . (int)$product['product_id']);
            $has_customization = (int)$product['customization_quantity'] > 0;

            if (!$is_virtual && !$has_customization) {
                $eligible_products[] = $product;
            }
        }

        $this->context->smarty->assign([
            'order' => $order,
            'products' => $eligible_products,
            'id_order' => $id_order,
            'secure_key' => $order->secure_key,
            'action_url' => $this->context->link->getModuleLink('euwithdrawalbutton', 'request', [
                'id_order' => $id_order,
                'secure_key' => $order->secure_key
            ]),
        ]);

        $this->setTemplate('module:euwithdrawalbutton/views/templates/front/request.tpl');
    }

    public function postProcess()
    {
        if (Tools::isSubmit('submitWithdrawal')) {
            $id_order = (int)Tools::getValue('id_order');
            $order = new Order($id_order);

            if (!Validate::isLoadedObject($order)) {
                 $this->errors[] = $this->module->l('Invalid order.');
                 return;
            }

            if ($this->context->customer->id) {
                if ($this->context->customer->id != $order->id_customer) {
                    $this->errors[] = $this->module->l('Invalid order.');
                    return;
                }
            } else {
                $secure_key = Tools::getValue('secure_key');
                if ($secure_key != $order->secure_key) {
                    $this->errors[] = $this->module->l('Invalid order.');
                    return;
                }
            }

            $selected_items = Tools::getValue('selected_items');
            $quantities = Tools::getValue('quantity');

            if (empty($selected_items) || !is_array($selected_items)) {
                $this->errors[] = $this->module->l('Please select at least one item to return.');
                return;
            }

            $withdrawal_data = array();
            foreach ($selected_items as $id_order_detail) {
                $id_order_detail = (int)$id_order_detail;
                $quantity = isset($quantities[$id_order_detail]) ? (int)$quantities[$id_order_detail] : 0;

                if ($quantity <= 0) continue;

                $order_detail = new OrderDetail($id_order_detail);
                if (Validate::isLoadedObject($order_detail) && $order_detail->id_order == $order->id) {
                    if ($quantity > $order_detail->product_quantity) {
                        $this->errors[] = sprintf($this->module->l('Invalid quantity for product %s.'), $order_detail->product_name);
                        return;
                    }
                    $withdrawal_data[] = array(
                        'id_order_detail' => $id_order_detail,
                        'product_name' => $order_detail->product_name,
                        'quantity' => $quantity
                    );
                }
            }

            if (empty($withdrawal_data)) {
                $this->errors[] = $this->module->l('No valid items selected.');
                return;
            }

            $ip_address = Tools::getRemoteAddr();
            $user_agent = $_SERVER['HTTP_USER_AGENT'];
            $date_now = date('Y-m-d H:i:s');

            // Save request using ObjectModel
            include_once(_PS_MODULE_DIR_ . 'euwithdrawalbutton/classes/EuWithdrawalRequest.php');
            $request = new EuWithdrawalRequest();
            $request->id_order = (int)$order->id;
            $request->id_customer = (int)$order->id_customer;
            $request->items_data = json_encode($withdrawal_data);
            $request->ip_address = $ip_address;
            $request->user_agent = $user_agent;
            $request->date_add = $date_now;

            if ($request->add()) {
                // Update Order Status
                $new_state_id = (int)Configuration::get('EU_WITHDRAWAL_STATE_ID');
                if ($new_state_id) {
                    $history = new OrderHistory();
                    $history->id_order = (int)$order->id;
                    $history->changeIdOrderState($new_state_id, (int)$order->id);
                    $history->addWithemail(true);
                }

                // Generate PDF and Send Email
                $this->sendWithdrawalConfirmation($order, $withdrawal_data, $ip_address, $user_agent, $date_now);

                $this->success[] = $this->module->l('Your withdrawal request has been submitted successfully.');
            } else {
                $this->errors[] = $this->module->l('An error occurred while saving your request.');
            }
        }
    }

    protected function sendWithdrawalConfirmation($order, $withdrawal_data, $ip_address, $user_agent, $date_now)
    {
        $customer = new Customer((int)$order->id_customer);

        $items_html = '<ul>';
        foreach ($withdrawal_data as $item) {
            $items_html .= '<li>' . $item['product_name'] . ' (x' . $item['quantity'] . ')</li>';
        }
        $items_html .= '</ul>';

        $template_vars = [
            '{firstname}' => $customer->firstname,
            '{lastname}' => $customer->lastname,
            '{order_reference}' => $order->reference,
            '{date}' => $date_now,
            '{ip_address}' => $ip_address,
            '{user_agent}' => $user_agent,
            '{items}' => $items_html,
        ];

        // Generate PDF
        $pdf_content = $this->generateWithdrawalPDF($order, $withdrawal_data, $ip_address, $user_agent, $date_now);
        $file_attachment = [
            'content' => $pdf_content,
            'name' => 'withdrawal_receipt_' . $order->reference . '.pdf',
            'mime' => 'application/pdf',
        ];

        Mail::Send(
            (int)$order->id_lang,
            'withdrawal_conf',
            Mail::l('Withdrawal Request Acknowledgment', (int)$order->id_lang),
            $template_vars,
            $customer->email,
            $customer->firstname . ' ' . $customer->lastname,
            null,
            null,
            $file_attachment,
            null,
            _PS_MODULE_DIR_ . 'euwithdrawalbutton/mails/'
        );
    }

    protected function generateWithdrawalPDF($order, $withdrawal_data, $ip_address, $user_agent, $date_now)
    {
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor(Context::getContext()->shop->name);
        $pdf->SetTitle('Withdrawal Receipt - ' . $order->reference);
        $pdf->SetSubject('Withdrawal Receipt');

        $pdf->AddPage();

        $html = '<h1>Withdrawal Request Receipt</h1>';
        $html .= '<p><strong>Order Reference:</strong> ' . $order->reference . '</p>';
        $html .= '<p><strong>Date/Time:</strong> ' . $date_now . '</p>';
        $html .= '<p><strong>IP Address:</strong> ' . $ip_address . '</p>';
        $html .= '<p><strong>User Agent:</strong> ' . $user_agent . '</p>';
        $html .= '<h2>Items Selected for Withdrawal:</h2>';
        $html .= '<table border="1" cellpadding="5"><thead><tr><th>Product Name</th><th>Quantity</th></tr></thead><tbody>';
        foreach ($withdrawal_data as $item) {
            $html .= '<tr><td>' . $item['product_name'] . '</td><td>' . $item['quantity'] . '</td></tr>';
        }
        $html .= '</tbody></table>';

        $pdf->writeHTML($html, true, false, true, false, '');
        return $pdf->Output('withdrawal_receipt.pdf', 'S');
    }
}
