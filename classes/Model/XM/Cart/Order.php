<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Model for `cart_order`.
 *
 * @package    XM
 * @category   Models
 * @author     XM Media Inc.
 * @copyright  (c) 2013 XM Media Inc.
 */
class Model_XM_Cart_Order extends ORM {
	protected $_table_names_plural = FALSE;
	protected $_table_name = 'cart_order';
	// protected $_primary_val = 'name'; // default: name (column used as primary value)
	public $_table_name_display = 'Cart - Order'; // cl4 specific

	// default sorting
	// protected $_sorting = array();

	// relationships
	// protected $_has_one = array();
	protected $_has_many = array(
		'cart_gift_card_log' => array(
			'model' => 'Cart_Gift_Card_Log',
			'foreign_key' => 'cart_order_id',
		),
		'cart_order_additional_charge' => array(
			'model' => 'Cart_Order_Additional_Charge',
			'foreign_key' => 'cart_order_id',
		),
		'cart_order_discount' => array(
			'model' => 'Cart_Order_Discount',
			'foreign_key' => 'cart_order_id',
		),
		'cart_order_log' => array(
			'model' => 'Cart_Order_Log',
			'foreign_key' => 'cart_order_id',
		),
		'cart_order_payment' => array(
			'model' => 'Cart_Order_Payment',
			'foreign_key' => 'cart_order_id',
		),
		'cart_order_product' => array(
			'model' => 'Cart_Order_Product',
			'foreign_key' => 'cart_order_id',
		),
		'cart_order_shipping' => array(
			'model' => 'Cart_Order_Shipping',
			'foreign_key' => 'cart_order_id',
		),
		'cart_order_tax' => array(
			'model' => 'Cart_Order_Tax',
			'foreign_key' => 'cart_order_id',
		),
		'cart_additional_charge' => array(
			'model' => 'Cart_Additional_Charge',
			'through' => 'cart_order_additional_charge',
			'foreign_key' => 'cart_order_id',
			'far_key' => 'cart_additional_charge_id',
		),
		'cart_discount' => array(
			'model' => 'Cart_Discount',
			'through' => 'cart_order_discount',
			'foreign_key' => 'cart_order_id',
			'far_key' => 'cart_discount_id',
		),
		'user' => array(
			'model' => 'User',
			'through' => 'cart_order_log',
			'foreign_key' => 'cart_order_id',
			'far_key' => 'user_id',
		),
		'payment_type' => array(
			'model' => 'Payment_Type',
			'through' => 'cart_order_payment',
			'foreign_key' => 'cart_order_id',
			'far_key' => 'payment_type_id',
		),
		'payment_status' => array(
			'model' => 'Payment_Status',
			'through' => 'cart_order_payment',
			'foreign_key' => 'cart_order_id',
			'far_key' => 'payment_status_id',
		),
		'transaction' => array(
			'model' => 'Transaction',
			'through' => 'cart_order_payment',
			'foreign_key' => 'cart_order_id',
			'far_key' => 'transaction_id',
		),
		'cart_product' => array(
			'model' => 'Cart_Product',
			'through' => 'cart_order_product',
			'foreign_key' => 'cart_order_id',
			'far_key' => 'cart_product_id',
		),
		'cart_shipping' => array(
			'model' => 'Cart_Shipping',
			'through' => 'cart_order_shipping',
			'foreign_key' => 'cart_order_id',
			'far_key' => 'cart_shipping_id',
		),
		'cart_tax' => array(
			'model' => 'Cart_Tax',
			'through' => 'cart_order_tax',
			'foreign_key' => 'cart_order_id',
			'far_key' => 'cart_tax_id',
		),
	);
	protected $_belongs_to = array(
		'user' => array(
			'model' => 'User',
			'foreign_key' => 'user_id',
		),
		'country' => array(
			'model' => 'Country',
			'foreign_key' => 'country_id',
		),
		'shipping_state' => array(
			'model' => 'State',
			'foreign_key' => 'shipping_state_id',
		),
		'shipping_country' => array(
			'model' => 'Country',
			'foreign_key' => 'shipping_country_id',
		),
		'billing_state' => array(
			'model' => 'State',
			'foreign_key' => 'billing_state_id',
		),
		'billing_country' => array(
			'model' => 'Country',
			'foreign_key' => 'billing_country_id',
		),
	);

	// column definitions
	protected $_table_columns = array(
		'id' => array(
			'field_type' => 'Hidden',
			'edit_flag' => TRUE,
			'is_nullable' => FALSE,
		),
		'expiry_date' => array(
			'field_type' => 'DateTime',
			'is_nullable' => FALSE,
		),
		'user_id' => array(
			'field_type' => 'Select',
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
			'field_options' => array(
				'source' => array(
					'source' => 'model',
					'data' => 'User',
					'label' => 'username',
				),
			),
		),
		'sub_total' => array(
			'field_type' => 'Text',
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
			'field_attributes' => array(
				'maxlength' => 13,
				'size' => 13,
			),
		),
		'grand_total' => array(
			'field_type' => 'Text',
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
			'field_attributes' => array(
				'maxlength' => 13,
				'size' => 13,
			),
		),
		'exchange_rate' => array(
			'field_type' => 'Text',
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
			'field_attributes' => array(
				'maxlength' => 17,
				'size' => 17,
			),
		),
		'country_id' => array(
			'field_type' => 'Select',
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
			'field_options' => array(
				'source' => array(
					'source' => 'model',
					'data' => 'Country',
				),
			),
		),
		'invoice' => array(
			'field_type' => 'Text',
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
			'field_attributes' => array(
				'maxlength' => 10,
				'size' => 10,
			),
		),
		'internal_order_num' => array(
			'field_type' => 'Text',
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
			'field_attributes' => array(
				'maxlength' => 15,
				'size' => 15,
			),
		),
		'status' => array(
			'field_type' => 'Select',
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
			'field_options' => array(
				'source' => array(
					'source' => 'array',
					'data' => array(
						CART_ORDER_STATUS_NEW       => 'New Order / Unpaid',
						CART_ORDER_STATUS_SUBMITTED => 'Submitted / Waiting for Payment',
						CART_ORDER_STATUS_PAYMENT   => 'Payment in Progress',
						CART_ORDER_STATUS_PAID      => 'Paid',
						CART_ORDER_STATUS_RECEIVED  => 'Received',
						CART_ORDER_STATUS_SHIPPED   => 'Shipped',
						CART_ORDER_STATUS_REFUNDED  => 'Refunded',
						CART_ORDER_STATUS_CANCELLED => 'Cancelled',
					),
				),
			),
		),
		'po_number' => array(
			'field_type' => 'Text',
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
			'field_attributes' => array(
				'maxlength' => 50,
			),
		),
		'order_note' => array(
			'field_type' => 'TextArea',
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
		),
		'user_address_loaded_flag' => array(
			'field_type' => 'Checkbox',
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
		),
		'email' => array(
			'field_type' => 'Text',
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
			'field_attributes' => array(
				'maxlength' => 200,
			),
		),
		'shipping_first_name' => array(
			'field_type' => 'Text',
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
			'field_attributes' => array(
				'maxlength' => 150,
			),
		),
		'shipping_last_name' => array(
			'field_type' => 'Text',
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
			'field_attributes' => array(
				'maxlength' => 150,
			),
		),
		'shipping_company' => array(
			'field_type' => 'Text',
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
			'field_attributes' => array(
				'maxlength' => 255,
			),
		),
		'shipping_address_1' => array(
			'field_type' => 'Text',
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
			'field_attributes' => array(
				'maxlength' => 255,
			),
		),
		'shipping_address_2' => array(
			'field_type' => 'Text',
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
			'field_attributes' => array(
				'maxlength' => 255,
			),
		),
		'shipping_city' => array(
			'field_type' => 'Text',
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
			'field_attributes' => array(
				'maxlength' => 150,
			),
		),
		'shipping_state_id' => array(
			'field_type' => 'Select',
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
			'field_options' => array(
				'source' => array(
					'source' => 'model',
					'data' => 'State',
				),
			),
		),
		'shipping_state' => array(
			'field_type' => 'Text',
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
			'field_attributes' => array(
				'maxlength' => 150,
			),
		),
		'shipping_postal_code' => array(
			'field_type' => 'Text',
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
			'field_attributes' => array(
				'maxlength' => 10,
				'size' => 10,
			),
		),
		'shipping_country_id' => array(
			'field_type' => 'Select',
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
			'field_options' => array(
				'source' => array(
					'source' => 'model',
					'data' => 'Country',
				),
			),
		),
		'shipping_phone' => array(
			'field_type' => 'Phone',
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
		),
		'same_as_shipping_flag' => array(
			'field_type' => 'Checkbox',
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
		),
		'billing_first_name' => array(
			'field_type' => 'Text',
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
			'field_attributes' => array(
				'maxlength' => 150,
			),
		),
		'billing_last_name' => array(
			'field_type' => 'Text',
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
			'field_attributes' => array(
				'maxlength' => 150,
			),
		),
		'billing_company' => array(
			'field_type' => 'Text',
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
			'field_attributes' => array(
				'maxlength' => 255,
			),
		),
		'billing_address_1' => array(
			'field_type' => 'Text',
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
			'field_attributes' => array(
				'maxlength' => 255,
			),
		),
		'billing_address_2' => array(
			'field_type' => 'Text',
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
			'field_attributes' => array(
				'maxlength' => 255,
			),
		),
		'billing_city' => array(
			'field_type' => 'Text',
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
			'field_attributes' => array(
				'maxlength' => 150,
			),
		),
		'billing_state_id' => array(
			'field_type' => 'Select',
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
			'field_options' => array(
				'source' => array(
					'source' => 'model',
					'data' => 'State',
				),
			),
		),
		'billing_state' => array(
			'field_type' => 'Text',
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
			'field_attributes' => array(
				'maxlength' => 150,
			),
		),
		'billing_postal_code' => array(
			'field_type' => 'Text',
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
			'field_attributes' => array(
				'maxlength' => 10,
				'size' => 10,
			),
		),
		'billing_country_id' => array(
			'field_type' => 'Select',
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
			'field_options' => array(
				'source' => array(
					'source' => 'model',
					'data' => 'Country',
				),
			),
		),
		'billing_phone' => array(
			'field_type' => 'Phone',
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
		),
	);

	/**
	 * @var  array  $_expires_column  The time this row expires and is no longer returned in standard searches.
	 * Use format => 'Y-m-j H:i:s' for DATETIMEs and format => TRUE for TIMESTAMPs.
	 */
	protected $_expires_column = array(
		'column' 	=> 'expiry_date',
		'default'	=> 0,
	);

	/**
	 * Labels for columns.
	 *
	 * @return  array
	 */
	public function labels() {
		return array(
			'id' => 'ID',
			'expiry_date' => 'Expiry Date',
			'user_id' => 'User',
			'sub_total' => 'Sub Total',
			'grand_total' => 'Grand Total',
			'exchange_rate' => 'Exchange Rate',
			'country_id' => 'Country',
			'invoice' => 'Invoice',
			'internal_order_num' => 'Internal Order Num',
			'status' => 'Status',
			'po_number' => 'PO Number',
			'order_note' => 'Order Note',
			'user_address_loaded_flag' => 'User Address Loaded',
			'email' => 'Email',
			'shipping_first_name' => 'Shipping First Name',
			'shipping_last_name' => 'Shipping Last Name',
			'shipping_company' => 'Shipping Company',
			'shipping_address_1' => 'Shipping Address 1',
			'shipping_address_2' => 'Shipping Address 2',
			'shipping_city' => 'Shipping City',
			'shipping_state_id' => 'Shipping State',
			'shipping_state' => 'Shipping State',
			'shipping_postal_code' => 'Shipping Postal Code',
			'shipping_country_id' => 'Shipping Country',
			'shipping_phone' => 'Shipping Phone',
			'same_as_shipping_flag' => 'Same As Shipping',
			'billing_first_name' => 'Billing First Name',
			'billing_last_name' => 'Billing Last Name',
			'billing_company' => 'Billing Company',
			'billing_address_1' => 'Billing Address 1',
			'billing_address_2' => 'Billing Address 2',
			'billing_city' => 'Billing City',
			'billing_state_id' => 'Billing State',
			'billing_state' => 'Billing State',
			'billing_postal_code' => 'Billing Postal Code',
			'billing_country_id' => 'Billing Country',
			'billing_phone' => 'Billing Phone',
		);
	}

	/**
	 * Filter definitions, run everytime a field is set.
	 *
	 * @return  array
	 */
	public function filters() {
		return array(
			'shipping_first_name' => array(
				array('trim'),
			),
			'shipping_last_name' => array(
				array('trim'),
			),
			'shipping_company' => array(
				array('trim'),
			),
			'shipping_address_1' => array(
				array('trim'),
			),
			'shipping_address_2' => array(
				array('trim'),
			),
			'shipping_city' => array(
				array('trim'),
			),
			'shipping_state' => array(
				array('trim'),
			),
			'shipping_postal_code' => array(
				array('trim'),
			),
			'billing_first_name' => array(
				array('trim'),
			),
			'billing_last_name' => array(
				array('trim'),
			),
			'billing_company' => array(
				array('trim'),
			),
			'billing_address_1' => array(
				array('trim'),
			),
			'billing_address_2' => array(
				array('trim'),
			),
			'billing_city' => array(
				array('trim'),
			),
			'billing_state' => array(
				array('trim'),
			),
			'billing_postal_code' => array(
				array('trim'),
			),
		);
	}
} // class