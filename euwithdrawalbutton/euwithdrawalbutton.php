<?php
declare(strict_types=1);
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

if (!defined('_PS_VERSION_')) {
    exit;
}

class EuWithdrawalButton extends Module
{
    protected $config_prefix = 'EU_WITHDRAWAL_';

    public function __construct()
    {
        $this->name = 'euwithdrawalbutton';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Jules';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('EU Withdrawal Button');
        $this->description = $this->l('Provides compliance with EU Directive 2023/2673 by adding a withdrawal button for orders.');

        $this->ps_versions_compliancy = ['min' => '8.0.0', 'max' => '9.9.9'];
    }

    public function install()
    {
        include_once($this->local_path . 'sql/install.php');

        return parent::install() &&
            $this->createOrderStatus() &&
            $this->installTab() &&
            $this->registerHook('displayCustomerAccount') &&
            $this->registerHook('displayOrderDetail') &&
            $this->registerHook('displayFooter');
    }

    public function uninstall()
    {
        include_once($this->local_path . 'sql/uninstall.php');

        return $this->uninstallTab() &&
            parent::uninstall();
    }

    protected function createOrderStatus()
    {
        $state_name = 'Withdrawal Requested';
        $id_state = (int)Configuration::get($this->config_prefix . 'STATE_ID');

        if ($id_state) {
            $order_state = new OrderState($id_state);
            if (Validate::isLoadedObject($order_state)) {
                return true;
            }
        }

        $order_state = new OrderState();
        $order_state->name = array();
        foreach (Language::getLanguages() as $language) {
            $order_state->name[$language['id_lang']] = $state_name;
        }

        $order_state->send_email = false;
        $order_state->color = '#4169E1';
        $order_state->hidden = false;
        $order_state->delivery = false;
        $order_state->logable = false;
        $order_state->invoice = false;

        if ($order_state->add()) {
            $source = _PS_ROOT_DIR_ . '/img/os/9.gif'; // Using a generic icon
            $destination = _PS_ROOT_DIR_ . '/img/os/' . (int)$order_state->id . '.gif';
            copy($source, $destination);

            Configuration::updateValue($this->config_prefix . 'STATE_ID', (int)$order_state->id);
            return true;
        }

        return false;
    }

    protected function installTab()
    {
        $tab = new Tab();
        $tab->class_name = 'AdminEuWithdrawalRequests';
        $tab->id_parent = (int)Tab::getIdFromClassName('AdminOrders');
        $tab->module = $this->name;

        foreach (Language::getLanguages() as $lang) {
            $tab->name[$lang['id_lang']] = $this->l('Withdrawal Requests');
        }

        return $tab->add();
    }

    protected function uninstallTab()
    {
        $id_tab = (int)Tab::getIdFromClassName('AdminEuWithdrawalRequests');
        if ($id_tab) {
            $tab = new Tab($id_tab);
            return $tab->delete();
        }
        return true;
    }

    public function hookDisplayCustomerAccount($params)
    {
        // This hook usually adds a link to the customer account page
        return $this->display(__FILE__, 'views/templates/hook/displayCustomerAccount.tpl');
    }

    public function hookDisplayOrderDetail($params)
    {
        $order = $params['order'];
        if (!Validate::isLoadedObject($order)) {
            return '';
        }

        // Check if withdrawal is possible (e.g. order status, time limit, items eligibility)
        // For simplicity, we'll pass the order ID to the template and handle logic there or in the controller
        $this->context->smarty->assign([
            'withdrawal_order_id' => (int)$order->id,
            'withdrawal_url' => $this->context->link->getModuleLink($this->name, 'request', ['id_order' => (int)$order->id]),
        ]);

        return $this->display(__FILE__, 'views/templates/hook/displayOrderDetail.tpl');
    }

    public function hookDisplayFooter($params)
    {
        // Footer link for guest tracking as required
        $this->context->smarty->assign([
            'withdrawal_guest_url' => $this->context->link->getModuleLink($this->name, 'request'),
        ]);

        return $this->display(__FILE__, 'views/templates/hook/displayFooter.tpl');
    }
}
