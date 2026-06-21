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

        if ($id_order) {
            $this->displayWithdrawalForm($id_order);
        } else {
            $this->displayLookupForm();
        }
    }

    protected function displayLookupForm()
    {
        $this->context->smarty->assign([
            'lookup_action' => $this->context->link->getModuleLink('euwithdrawalbutton', 'request'),
        ]);
        $this->setTemplate('module:euwithdrawalbutton/views/templates/front/request.tpl');
    }

    protected function displayWithdrawalForm($id_order)
    {
        $order = new Order($id_order);

        if (!Validate::isLoadedObject($order)) {
            $this->errors[] = $this->module->l('Order not found.');
            return $this->displayLookupForm();
        }

        // Security check
        if ($this->context->customer->id) {
            if ($this->context->customer->id != $order->id_customer) {
                Tools::redirect('index.php?controller=history');
            }
        } else {
            $secure_key = Tools::getValue('secure_key');
            if ($secure_key != $order->secure_key) {
                $this->errors[] = $this->module->l('Invalid access for this order.');
                return $this->displayLookupForm();
            }
        }

        $products = $order->getProducts();
        $product_ids = array_map(function($p) { return (int)$p['product_id']; }, $products);

        // Single query to avoid N+1
        $virtual_statuses = array();
        if (!empty($product_ids)) {
            $results = Db::getInstance()->executeS('SELECT id_product, is_virtual FROM ' . _DB_PREFIX_ . 'product WHERE id_product IN (' . implode(',', array_unique($product_ids)) . ')');
            foreach ($results as $row) {
                $virtual_statuses[$row['id_product']] = (bool)$row['is_virtual'];
            }
        }

        $eligible_products = array();
        foreach ($products as $product) {
            $is_virtual = isset($virtual_statuses[$product['product_id']]) ? $virtual_statuses[$product['product_id']] : false;
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
        if (Tools::isSubmit('submitLookup')) {
            $this->processLookup();
        } elseif (Tools::isSubmit('submitWithdrawal')) {
            $this->processWithdrawal();
        }
    }

    protected function processLookup()
    {
        $reference = Tools::getValue('order_reference');
        $email = Tools::getValue('email');

        if (empty($reference) || empty($email) || !Validate::isEmail($email)) {
            $this->errors[] = $this->module->l('Please provide a valid order reference and email.');
            return;
        }

        $id_order = (int)Db::getInstance()->getValue('
            SELECT o.id_order
            FROM ' . _DB_PREFIX_ . 'orders o
            LEFT JOIN ' . _DB_PREFIX_ . 'customer c ON o.id_customer = c.id_customer
            WHERE o.reference = "' . pSQL($reference) . '" AND c.email = "' . pSQL($email) . '"'
        );

        if ($id_order) {
            $order = new Order($id_order);
            Tools::redirect($this->context->link->getModuleLink('euwithdrawalbutton', 'request', [
                'id_order' => $id_order,
                'secure_key' => $order->secure_key
            ]));
        } else {
            $this->errors[] = $this->module->l('Order not found with these credentials.');
        }
    }

    protected function processWithdrawal()
    {
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

        // Re-verify eligibility on server side
        $products = $order->getProducts();
        $product_ids = array_map(function($p) { return (int)$p['product_id']; }, $products);
        $virtual_statuses = array();
        if (!empty($product_ids)) {
            $results = Db::getInstance()->executeS('SELECT id_product, is_virtual FROM ' . _DB_PREFIX_ . 'product WHERE id_product IN (' . implode(',', array_unique($product_ids)) . ')');
            foreach ($results as $row) {
                $virtual_statuses[$row['id_product']] = (bool)$row['is_virtual'];
            }
        }

        $withdrawal_data = array();
        foreach ($selected_items as $id_order_detail) {
            $id_order_detail = (int)$id_order_detail;
            $quantity = isset($quantities[$id_order_detail]) ? (int)$quantities[$id_order_detail] : 0;

            if ($quantity <= 0) continue;

            $order_detail = new OrderDetail($id_order_detail);
            if (Validate::isLoadedObject($order_detail) && $order_detail->id_order == $order->id) {
                // Check if product is eligible
                $is_virtual = isset($virtual_statuses[$order_detail->product_id]) ? $virtual_statuses[$order_detail->product_id] : false;
                $has_customization = (int)$order_detail->customization_quantity > 0;

                if ($is_virtual || $has_customization) {
                    $this->errors[] = sprintf($this->module->l('Product %s is not eligible for withdrawal.'), $order_detail->product_name);
                    return;
                }

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

        include_once(_PS_MODULE_DIR_ . 'euwithdrawalbutton/classes/EuWithdrawalRequest.php');
        $request = new EuWithdrawalRequest();
        $request->id_order = (int)$order->id;
        $request->id_customer = (int)$order->id_customer;
        $request->items_data = json_encode($withdrawal_data);
        $request->ip_address = $ip_address;
        $request->user_agent = $user_agent;
        $request->date_add = $date_now;

        if ($request->add()) {
            $new_state_id = (int)Configuration::get('EU_WITHDRAWAL_STATE_ID');
            if ($new_state_id) {
                $history = new OrderHistory();
                $history->id_order = (int)$order->id;
                $history->changeIdOrderState($new_state_id, (int)$order->id);
                $history->addWithemail(true);
            }

            $this->sendWithdrawalConfirmation($order, $request);
            $this->success[] = $this->module->l('Your withdrawal request has been submitted successfully.');
        } else {
            $this->errors[] = $this->module->l('An error occurred while saving your request.');
        }
    }

    protected function sendWithdrawalConfirmation($order, $request)
    {
        $customer = new Customer((int)$order->id_customer);
        $withdrawal_data = json_decode($request->items_data, true);

        $items_html = '<ul>';
        foreach ($withdrawal_data as $item) {
            $items_html .= '<li>' . $item['product_name'] . ' (x' . $item['quantity'] . ')</li>';
        }
        $items_html .= '</ul>';

        $template_vars = [
            '{firstname}' => $customer->firstname,
            '{lastname}' => $customer->lastname,
            '{order_reference}' => $order->reference,
            '{date}' => $request->date_add,
            '{ip_address}' => $request->ip_address,
            '{user_agent}' => $request->user_agent,
            '{items}' => $items_html,
        ];

        // Generate PDF using PrestaShop's PDF class
        include_once(_PS_MODULE_DIR_ . 'euwithdrawalbutton/classes/HTMLTemplateWithdrawalReceipt.php');
        $pdf = new PDF($request, 'WithdrawalReceipt', Context::getContext()->smarty);
        $pdf_content = $pdf->render(false);

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
}
