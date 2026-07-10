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

declare(strict_types=1);

if (!defined('_PS_VERSION_')) {
    exit;
}

class EuWithdrawalRequest extends ObjectModel
{
    public $id_order;
    public $id_customer;
    public $customer_name;
    public $order_reference;
    public $email;
    public $request_type;
    public $items_data;
    public $ip_address;
    public $user_agent;
    public $status;
    public $date_add;

    public function __construct($id = null, $id_lang = null, $id_shop = null)
    {
        parent::__construct($id, $id_lang, $id_shop);
        if (!$this->id) {
            $this->status = 'pending';
        }
    }

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'euwithdrawal_requests',
        'primary' => 'id_euwithdrawal_request',
        'fields' => array(
            'id_order' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'id_customer' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'customer_name' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true, 'size' => 255),
            'order_reference' => array('type' => self::TYPE_STRING, 'validate' => 'isReference', 'required' => true, 'size' => 64),
            'email' => array('type' => self::TYPE_STRING, 'validate' => 'isEmail', 'required' => true, 'size' => 255),
            'request_type' => array('type' => self::TYPE_STRING, 'required' => true, 'size' => 32),
            'items_data' => array('type' => self::TYPE_STRING, 'required' => true),
            'ip_address' => array('type' => self::TYPE_STRING, 'size' => 255),
            'user_agent' => array('type' => self::TYPE_STRING, 'size' => 1024),
            'status' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 32),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDateFormat'),
        ),
    );
}
