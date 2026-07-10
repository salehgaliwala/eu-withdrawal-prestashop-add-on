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

include_once(_PS_MODULE_DIR_ . 'euwithdrawalbutton/classes/EuWithdrawalRequest.php');

class AdminEuWithdrawalRequestsController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'euwithdrawal_requests';
        $this->identifier = 'id_euwithdrawal_request';
        $this->className = 'EuWithdrawalRequest';
        $this->lang = false;
        $this->explicitSelect = true;
        $this->allow_export = true;

        parent::__construct();

        $this->fields_list = array(
            'id_euwithdrawal_request' => array(
                'title' => $this->module->l('ID'),
                'align' => 'center',
                'width' => 25
            ),
            'order_reference' => array(
                'title' => $this->module->l('Order Reference'),
                'width' => 100,
            ),
            'customer_name' => array(
                'title' => $this->module->l('Customer'),
                'width' => 150,
            ),
            'email' => array(
                'title' => $this->module->l('Email'),
                'width' => 150,
            ),
            'request_type' => array(
                'title' => $this->module->l('Type'),
                'width' => 100,
            ),
            'status' => array(
                'title' => $this->module->l('Status'),
                'width' => 100,
                'type' => 'select',
                'list' => array(
                    'pending' => $this->module->l('Pending'),
                    'accepted' => $this->module->l('Accepted'),
                    'rejected' => $this->module->l('Rejected'),
                ),
                'filter_key' => 'a!status',
                'callback' => 'displayStatus',
            ),
            'date_add' => array(
                'title' => $this->module->l('Date'),
                'type' => 'datetime',
                'width' => 150
            ),
        );

        $this->addRowAction('edit');
        $this->addRowAction('exportpdf');
    }

    public function displayStatus($value, $row)
    {
        switch ($value) {
            case 'accepted':
                return '<span class="badge badge-success label label-success">' . $this->module->l('Accepted') . '</span>';
            case 'rejected':
                return '<span class="badge badge-danger label label-danger">' . $this->module->l('Rejected') . '</span>';
            case 'pending':
            default:
                return '<span class="badge badge-warning label label-warning">' . $this->module->l('Pending') . '</span>';
        }
    }

    public function initToolbar()
    {
        parent::initToolbar();
        // Removes the "Add new" button in older PrestaShop themes
        unset($this->toolbar_btn['new']);
    }

    public function initPageHeaderToolbar()
    {
        parent::initPageHeaderToolbar();
        // Removes the "Add new" button in PrestaShop 1.7/8+
        unset($this->page_header_toolbar_btn['new']);
    }

    public function renderView()
    {
        $id = (int)Tools::getValue($this->identifier);
        $request = new EuWithdrawalRequest($id);

        if (!Validate::isLoadedObject($request)) {
            return parent::renderView();
        }

        $items = json_decode($request->items_data, true);

        $this->tpl_view_vars = array(
            'request' => $request,
            'items' => $items,
        );

        return parent::renderView();
    }

    public function renderForm()
    {
        $id = (int)Tools::getValue($this->identifier);
        $request = new EuWithdrawalRequest($id);

        if (!Validate::isLoadedObject($request)) {
            return parent::renderForm();
        }

        $items = json_decode($request->items_data, true);

        $this->context->smarty->assign(array(
            'request' => $request,
            'items' => $items,
        ));

        // Fetch the detailed request information
        $details_html = $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'euwithdrawalbutton/views/templates/admin/eu_withdrawal_requests_details.tpl');

        $this->fields_form = array(
            'legend' => array(
                'title' => $this->module->l('Change Withdrawal Request Status'),
                'icon' => 'icon-edit'
            ),
            'input' => array(
                array(
                    'type' => 'select',
                    'label' => $this->module->l('Status'),
                    'name' => 'status',
                    'required' => true,
                    'options' => array(
                        'query' => array(
                            array('id' => 'pending', 'name' => $this->module->l('Pending')),
                            array('id' => 'accepted', 'name' => $this->module->l('Accepted')),
                            array('id' => 'rejected', 'name' => $this->module->l('Rejected')),
                        ),
                        'id' => 'id',
                        'name' => 'name'
                    ),
                ),
            ),
            'submit' => array(
                'title' => $this->module->l('Save'),
                'class' => 'btn btn-default pull-right'
            )
        );

        $this->fields_value = array(
            'status' => $request->status,
        );

        return $details_html . parent::renderForm();
    }

    public function displayExportpdfLink($token = null, $id = null, $name = null)
    {
        $this->context->smarty->assign(array(
            'href' => self::$currentIndex . '&' . $this->identifier . '=' . (int)$id . '&exportpdf' . $this->table . '&token=' . ($token ?: $this->token),
            'action' => $this->module->l('Export PDF', 'AdminEuWithdrawalRequestsController'),
        ));

        return $this->context->smarty->fetch('helpers/list/list_action_default.tpl');
    }

    public function postProcess()
    {
        if (Tools::isSubmit('exportpdf' . $this->table)) {
            $this->processExportPdf();
        }
        parent::postProcess();
    }

    public function processExportPdf()
    {
        $id = (int)Tools::getValue($this->identifier);
        $request = new EuWithdrawalRequest($id);

        if (!Validate::isLoadedObject($request)) {
            die('Request not found');
        }

        include_once(_PS_MODULE_DIR_ . 'euwithdrawalbutton/classes/HTMLTemplateWithdrawalReceipt.php');
        $pdf = new PDF($request, 'WithdrawalReceipt', Context::getContext()->smarty);
        $pdf->render();
        exit;
    }
}
