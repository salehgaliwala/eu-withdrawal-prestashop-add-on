<?php
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

if (!defined("_PS_VERSION_")) { exit; }

class EuWithdrawalButtonRequestModuleFrontController extends ModuleFrontController
{
    public $auth = false;
    public $guestAllowed = true;

    public function initContent()
    {
        parent::initContent();

        $id_order = (int)Tools::getValue('id_order');
        $order = new Order($id_order);
        $products = array();
        $is_valid_order = false;

        // Security: If id_order is provided, verify it belongs to the customer or match secure_key/email
        if (Validate::isLoadedObject($order)) {
            if ($this->context->customer->id && $order->id_customer == $this->context->customer->id) {
                $is_valid_order = true;
            } else {
                $secure_key = Tools::getValue('secure_key');
                if ($secure_key === $order->secure_key) {
                    $is_valid_order = true;
                }
            }
        }

        if ($is_valid_order) {
            $products = $this->getEligibleProducts($order);
        }

        // Default to empty strings
        $customer_name = '';
        $customer_email = '';

        // If the order is valid, pull the details directly from the order's owner
        if ($is_valid_order && Validate::isLoadedObject($order)) {
            $customer = new Customer($order->id_customer);
            if (Validate::isLoadedObject($customer)) {
                $customer_name = $customer->firstname . ' ' . $customer->lastname;
                $customer_email = $customer->email;
            }
        } 
        // Fallback for logged-in users who haven't loaded an order yet
        elseif (isset($this->context->customer->id) && $this->context->customer->id) {
            $customer_name = $this->context->customer->firstname . ' ' . $this->context->customer->lastname;
            $customer_email = $this->context->customer->email;
        }

        $this->context->smarty->assign([
            'action_url' => $this->context->link->getModuleLink('euwithdrawalbutton', 'request'),
            'is_valid_order' => $is_valid_order,
            'order' => $order,
            'products' => $products,
            'customer_name' => $customer_name,
            'email' => $customer_email,
        ]);

        $this->setTemplate('module:euwithdrawalbutton/views/templates/front/request.tpl');
    }

    protected function getEligibleProducts($order)
    {
        $products = $order->getProducts();
        $eligible_products = array();

        $product_ids = array();
        foreach ($products as $product) {
            $product_ids[] = (int)$product['product_id'];
        }

        $virtual_statuses = array();
        if (!empty($product_ids)) {
            $results = Db::getInstance()->executeS('
                SELECT id_product, is_virtual
                FROM ' . _DB_PREFIX_ . 'product
                WHERE id_product IN (' . implode(',', array_unique($product_ids)) . ')'
            );
            foreach ($results as $row) {
                $virtual_statuses[$row['id_product']] = (bool)$row['is_virtual'];
            }
        }

        foreach ($products as $product) {
            $is_virtual = isset($virtual_statuses[$product['product_id']]) ? $virtual_statuses[$product['product_id']] : false;
            $has_customization = (int)$product['customization_quantity'] > 0;

            if (!$is_virtual && !$has_customization) {
                $eligible_products[] = $product;
            }
        }
        return $eligible_products;
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
            $this->errors[] = $this->module->l('Order not found or access denied.');
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

        // Security Verification
        $is_authorized = false;
        if ($this->context->customer->id && $order->id_customer == $this->context->customer->id) {
            $is_authorized = true;
        } else {
            $secure_key = Tools::getValue('secure_key');
            if ($secure_key === $order->secure_key) {
                $is_authorized = true;
            }
        }

        if (!$is_authorized) {
            $this->errors[] = $this->module->l('Unauthorized access.');
            return;
        }

        $request_type = Tools::getValue('request_type');
        $withdrawal_data = array();

        if ($request_type === 'line_items') {
            $selected_items = Tools::getValue('selected_items');
            $quantities = Tools::getValue('quantity');

            if (empty($selected_items) || !is_array($selected_items)) {
                $this->errors[] = $this->module->l('Please select at least one item.');
                return;
            }

            $order_products = $this->getEligibleProducts($order);
            $eligible_ids = array_column($order_products, 'id_order_detail');

            foreach ($selected_items as $id_order_detail) {
                if (!in_array($id_order_detail, $eligible_ids)) {
                    continue;
                }

                $qty = (int)$quantities[$id_order_detail];
                if ($qty <= 0) continue;

                foreach ($order_products as $p) {
                    if ($p['id_order_detail'] == $id_order_detail) {
                        if ($qty > $p['product_quantity']) {
                             $this->errors[] = sprintf($this->module->l('Invalid quantity for product %s.'), $p['product_name']);
                             return;
                        }
                        $withdrawal_data[] = array(
                            'id_order_detail' => $id_order_detail,
                            'product_name' => $p['product_name'],
                            'product_reference' => $p['product_reference'],
                            'quantity' => $qty
                        );
                        break;
                    }
                }
            }

            if (empty($withdrawal_data)) {
                $this->errors[] = $this->module->l('No valid items selected.');
                return;
            }
        } else {
            $withdrawal_data = array('type' => 'entire_order');
        }

        include_once(_PS_MODULE_DIR_ . 'euwithdrawalbutton/classes/EuWithdrawalRequest.php');
        $request = new EuWithdrawalRequest();
        $request->id_order = $id_order;
        $request->id_customer = (int)$order->id_customer;
        $request->customer_name = Tools::getValue('customer_name', $this->context->customer->firstname . ' ' . $this->context->customer->lastname);
        $request->order_reference = $order->reference;
        $request->email = Tools::getValue('email', $this->context->customer->email);
        $request->request_type = $request_type;
        $request->items_data = json_encode($withdrawal_data);
        $request->ip_address = Tools::getRemoteAddr();
        $request->user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $request->status = 'pending';
        $request->date_add = date('Y-m-d H:i:s');

        if ($request->add()) {
            $new_state_id = (int)Configuration::get('EU_WITHDRAWAL_STATE_ID');
            if ($new_state_id) {
                $history = new OrderHistory();
                $history->id_order = (int)$order->id;
                $history->changeIdOrderState($new_state_id, (int)$order->id);
                $history->addWithemail(true);
            }

            $this->sendNotifications($request);
            $this->success[] = $this->module->l('Your withdrawal request has been submitted successfully.');
        } else {
            $this->errors[] = $this->module->l('An error occurred while saving your request.');
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
                $items_html .= '<li>' . $item['product_name'] . ' (#' . $item['product_reference'] . ') x' . $item['quantity'] . '</li>';
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
