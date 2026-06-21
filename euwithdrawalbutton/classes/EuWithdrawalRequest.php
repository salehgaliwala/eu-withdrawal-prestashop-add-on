<?php
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
    public $date_add;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'euwithdrawal_requests',
        'primary' => 'id_euwithdrawal_request',
        'fields' => array(
            'id_order' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'id_customer' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId'),
            'customer_name' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true, 'size' => 255),
            'order_reference' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true, 'size' => 64),
            'email' => array('type' => self::TYPE_STRING, 'validate' => 'isEmail', 'required' => true, 'size' => 255),
            'request_type' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true, 'size' => 32),
            'items_data' => array('type' => self::TYPE_STRING, 'required' => true),
            'ip_address' => array('type' => self::TYPE_STRING, 'size' => 255),
            'user_agent' => array('type' => self::TYPE_STRING, 'size' => 1024),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
        ),
    );
}
