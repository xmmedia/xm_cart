<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Model for `cart_order`.
 *
 * @package    XM Cart
 * @category   Models
 * @author     XM Media Inc.
 * @copyright  (c) 2014 XM Media Inc.
 */
class Model_XM_Cart_Order extends Cart_ORM {
	protected $_table_names_plural = FALSE;
	protected $_table_name = 'cart_order';
	// protected $_primary_val = 'name'; // default: name (column used as primary value)
	public $_table_name_display = 'Cart - Order'; // xm specific

	// default sorting
	protected $_sorting = array(
		'id' => 'DESC',
	);

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
		'shipping_state_select' => array(
			'model' => 'State',
			'foreign_key' => 'shipping_state_id',
		),
		'shipping_country' => array(
			'model' => 'Country',
			'foreign_key' => 'shipping_country_id',
		),
		'billing_state_select' => array(
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
		'unique_id' => array(
			'field_type' => 'Text',
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
			'field_attributes' => array(
				'size' => 24,
				'length' => 24,
			),
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
				'maxlength' => 9,
				'size' => 9,
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
				'maxlength' => 9,
				'size' => 9,
			),
		),
		'amount_paid' => array(
			'field_type' => 'Text',
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
			'field_attributes' => array(
				'maxlength' => 9,
				'size' => 9,
			),
		),
		'payment_processor_fee' => array(
			'field_type' => 'Text',
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
			'field_attributes' => array(
				'maxlength' => 7,
				'size' => 7,
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
		'order_num' => array(
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
						CART_ORDER_STATUS_EMPTIED   => 'Emptied',
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
			'field_attributes' => array(
				'class' => 'order_textarea',
			),
		),
		'donation_cart_flag' => array(
			'field_type' => 'Checkbox',
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
		'shipping_first_name' => array(
			'field_type' => 'Text',
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
			'field_attributes' => array(
				'maxlength' => 150,
				'data-cart_shipping_field' => 'first_name',
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
				'data-cart_shipping_field' => 'last_name',
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
				'data-cart_shipping_field' => 'company',
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
				'data-cart_shipping_field' => 'address_1',
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
				'data-cart_shipping_field' => 'address_2',
			),
		),
		'shipping_municipality' => array(
			'field_type' => 'Text',
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
			'field_attributes' => array(
				'maxlength' => 150,
				'data-cart_shipping_field' => 'municipality',
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
				'select_none' => FALSE,
				'select_one' => TRUE,
			),
			'field_attributes' => array(
				'data-cart_shipping_field' => 'state_id',
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
				'data-cart_shipping_field' => 'state',
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
				'data-cart_shipping_field' => 'postal_code',
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
				'select_none' => FALSE,
				'select_one' => TRUE,
			),
			'field_attributes' => array(
				'data-cart_shipping_field' => 'country_id',
			),
		),
		'shipping_phone' => array(
			'field_type' => 'Phone',
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
			'field_options' => array(
				'default_value' => '1----',
			),
			'field_attributes' => array(
				'data-cart_shipping_field' => 'phone',
			),
		),
		'shipping_email' => array(
			'field_type' => 'Text',
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
			'field_attributes' => array(
				'maxlength' => 200,
				'data-cart_shipping_field' => 'email',
			),
		),
		'same_as_shipping_flag' => array(
			'field_type' => 'Checkbox',
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
			'field_attributes' => array(
				'class' => 'js_cart_same_as_shipping_flag',
			),
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
				'data-cart_billing_field' => 'first_name',
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
				'data-cart_billing_field' => 'last_name',
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
				'data-cart_billing_field' => 'company',
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
				'data-cart_billing_field' => 'address_1',
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
				'data-cart_billing_field' => 'address_2',
			),
		),
		'billing_municipality' => array(
			'field_type' => 'Text',
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
			'field_attributes' => array(
				'maxlength' => 150,
				'data-cart_billing_field' => 'municipality',
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
				'select_none' => FALSE,
				'select_one' => TRUE,
			),
			'field_attributes' => array(
				'data-cart_billing_field' => 'state_id',
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
				'data-cart_billing_field' => 'state',
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
				'data-cart_billing_field' => 'postal_code',
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
				'select_none' => FALSE,
				'select_one' => TRUE,
			),
			'field_attributes' => array(
				'data-cart_billing_field' => 'country_id',
			),
		),
		'billing_phone' => array(
			'field_type' => 'Phone',
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
			'field_options' => array(
				'default_value' => '1----',
			),
			'field_attributes' => array(
				'data-cart_billing_field' => 'phone',
			),
		),
		'billing_email' => array(
			'field_type' => 'Text',
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
			'field_attributes' => array(
				'maxlength' => 200,
				'data-cart_billing_field' => 'email',
			),
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

	protected $user_labels = FALSE;
	protected $require_shipping = FALSE;
	protected $require_billing = FALSE;

	protected $shipping_fields = array(
		'shipping_first_name',
		'shipping_last_name',
		'shipping_company',
		'shipping_address_1',
		'shipping_address_2',
		'shipping_municipality',
		'shipping_state_id',
		'shipping_state',
		'shipping_postal_code',
		'shipping_country_id',
		'shipping_phone',
		'shipping_email',
	);

	protected $billing_fields = array(
		'same_as_shipping_flag',
		'billing_first_name',
		'billing_last_name',
		'billing_company',
		'billing_address_1',
		'billing_address_2',
		'billing_municipality',
		'billing_state_id',
		'billing_state',
		'billing_postal_code',
		'billing_country_id',
		'billing_phone',
		'billing_email',
	);

	protected $final_step_fields = array(
		'order_note',
	);

	protected $_sub_total = 0;
	protected $_tax_total = 0;

	protected function _initialize() {
		parent::_initialize();

		$this->_table_columns['status']['field_options']['source']['data'] = (array) Cart_Config::load('order_status_labels');

		if ( ! Cart_Config::load('show_billing_company')) {
			$this->_table_columns['billing_company']['edit_flag'] = FALSE;
		}

		if ( ! Cart_Config::load('show_phone_country_codes')) {
			$this->_table_columns['shipping_phone']['field_options']['show_country_code'] = FALSE;
			$this->_table_columns['billing_phone']['field_options']['show_country_code'] = FALSE;
		}
		if ( ! Cart_Config::load('show_phone_extensions')) {
			$this->_table_columns['shipping_phone']['field_options']['show_extension'] = FALSE;
			$this->_table_columns['billing_phone']['field_options']['show_extension'] = FALSE;
		}
	}

	/**
	 * Labels for columns.
	 *
	 * @return  array
	 */
	public function labels() {
		return array(
			'id' => 'ID',
			'expiry_date' => 'Expiry Date',
			'unique_id' => 'Unique ID',
			'user_id' => 'User',
			'sub_total' => 'Sub Total',
			'grand_total' => 'Grand Total',
			'amount_paid' => 'Amount Paid',
			'payment_processor_fee' => 'Payment Processor Fee',
			'exchange_rate' => 'Exchange Rate',
			'country_id' => 'Country',
			'order_num' => 'Order Num',
			'internal_order_num' => 'Internal Order Num',
			'status' => 'Status',
			'po_number' => 'PO Number',
			'order_note' => 'Notes',
			'donation_cart_flag' => 'Donation Cart',
			'user_address_loaded_flag' => 'User Address Loaded',
			'shipping_first_name' => ( ! $this->user_labels ? 'Shipping ' : '') . 'First Name',
			'shipping_last_name' => ( ! $this->user_labels ? 'Shipping ' : '') . 'Last Name',
			'shipping_company' => ( ! $this->user_labels ? 'Shipping ' : '') . 'Company',
			'shipping_address_1' => ( ! $this->user_labels ? 'Shipping ' : '') . 'Address Line 1',
			'shipping_address_2' => ( ! $this->user_labels ? 'Shipping ' : '') . 'Address Line 2',
			'shipping_municipality' => ( ! $this->user_labels ? 'Shipping ' : '') . 'City',
			'shipping_state_id' => ( ! $this->user_labels ? 'Shipping ' : '') . 'Province / State',
			'shipping_state' => ( ! $this->user_labels ? 'Shipping ' : '') . 'Province / State',
			'shipping_postal_code' => ( ! $this->user_labels ? 'Shipping ' : '') . 'Postal / Zip Code',
			'shipping_country_id' => ( ! $this->user_labels ? 'Shipping ' : '') . 'Country',
			'shipping_phone' => ( ! $this->user_labels ? 'Shipping ' : '') . 'Phone',
			'shipping_email' => ( ! $this->user_labels ? 'Shipping ' : '') . 'Email Address',
			'same_as_shipping_flag' => 'Same As Shipping',
			'billing_first_name' => ( ! $this->user_labels ? 'Billing ' : '') . 'First Name',
			'billing_last_name' => ( ! $this->user_labels ? 'Billing ' : '') . 'Last Name',
			'billing_company' => ( ! $this->user_labels ? 'Billing ' : '') . 'Company' . ($this->user_labels ? ' (optional)' : ''),
			'billing_address_1' => ( ! $this->user_labels ? 'Billing ' : '') . 'Address Line 1',
			'billing_address_2' => ( ! $this->user_labels ? 'Billing ' : '') . 'Address Line 2',
			'billing_municipality' => ( ! $this->user_labels ? 'Billing ' : '') . 'City',
			'billing_state_id' => ( ! $this->user_labels ? 'Billing ' : '') . 'Province / State',
			'billing_state' => ( ! $this->user_labels ? 'Billing ' : '') . 'Province / State',
			'billing_postal_code' => ( ! $this->user_labels ? 'Billing ' : '') . 'Postal / Zip Code',
			'billing_country_id' => ( ! $this->user_labels ? 'Billing ' : '') . 'Country',
			'billing_phone' => ( ! $this->user_labels ? 'Billing ' : '') . 'Phone',
			'billing_email' => ( ! $this->user_labels ? 'Billing ' : '') . 'Email Address',
		);
	}

	/**
	 * Rule definitions for validation.
	 *
	 * @return  array
	 */
	public function rules() {
		$rules = array();

		// shipping
		if ($this->require_shipping) {
			$rules = array_merge($rules, array(
				'shipping_first_name' => array(
					array('not_empty'),
				),
				'shipping_last_name' => array(
					array('not_empty'),
				),
				'shipping_address_1' => array(
					array('not_empty'),
				),
				'shipping_municipality' => array(
					array('not_empty'),
				),
				'shipping_state_id' => array(
					array('selected'),
				),
				'shipping_postal_code' => array(
					array('not_empty'),
				),
				'shipping_country_id' => array(
					array('selected'),
				),
				'shipping_phone' => array(
					array('not_empty'),
				),
				'shipping_email' => array(
					array('not_empty'),
					array('email'),
				),
			));
		}

		// billing
		if ($this->require_billing) {
			$rules = array_merge($rules, array(
				'billing_first_name' => array(
					array('not_empty'),
				),
				'billing_last_name' => array(
					array('not_empty'),
				),
				'billing_address_1' => array(
					array('not_empty'),
				),
				'billing_municipality' => array(
					array('not_empty'),
				),
				'billing_state_id' => array(
					array('selected'),
				),
				'billing_postal_code' => array(
					array('not_empty'),
				),
				'billing_country_id' => array(
					array('selected'),
				),
				'billing_phone' => array(
					array('not_empty'),
				),
				'billing_email' => array(
					array('not_empty'),
					array('email'),
				),
			));
		}

		return $rules;
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
			'shipping_municipality' => array(
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
			'billing_municipality' => array(
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

	public function for_user() {
		$this->user_labels = TRUE;

		return $this;
	}

	public function only_allow_shipping() {
		$this->require_shipping = TRUE;
		return $this->set_edit_flag_false($this->shipping_fields);
	}

	public function only_allow_billing() {
		$this->require_billing = TRUE;
		return $this->set_edit_flag_false($this->billing_fields);
	}

	public function only_allow_final_step() {
		return $this->set_edit_flag_false($this->final_step_fields);
	}

	public function set_edit_flag_false($allowed_fields) {
		foreach ($this->_table_columns as $column_name => $attributes) {
			if ( ! in_array($column_name, $allowed_fields)) {
				$this->set_table_columns($column_name, 'edit_flag', FALSE);
			}
		}

		return $this;
	}

	public function shipping_formatted() {
		$str = '';

		$this->set_mode('view');

		if ( ! empty($this->shipping_company)) {
			$str .= $this->shipping_company . PHP_EOL;
		}

		$str .= $this->shipping_first_name . ' ' . $this->shipping_last_name . PHP_EOL
			. XM::format_phone($this->shipping_phone) . PHP_EOL
			. $this->shipping_email . PHP_EOL
			. $this->shipping_address_1 . PHP_EOL;

		if ( ! empty($this->shipping_address_2)) {
			$str .= $this->shipping_address_2 . PHP_EOL;
		}

		$str .= $this->shipping_municipality . ', ' . $this->shipping_state_select->name . '  ' . $this->shipping_postal_code . PHP_EOL
			. $this->shipping_country->name;

		return $str;
	}

	public function billing_contact_formatted() {
		$str = '';

		$this->set_mode('view');

		$str .= $this->billing_first_name . ' ' . $this->billing_last_name . PHP_EOL
			. XM::format_phone($this->billing_phone) . PHP_EOL
			. $this->billing_email;

		return $str;
	}

	public function billing_address_formatted() {
		$str = '';

		$this->set_mode('view');

		if ( ! empty($this->billing_company)) {
			$str .= $this->billing_company . PHP_EOL;
		}

		$str .= $this->billing_address_1 . PHP_EOL;

		if ( ! empty($this->billing_address_2)) {
			$str .= $this->billing_address_2 . PHP_EOL;
		}

		$str .= $this->billing_municipality . ', ' . $this->billing_state_select->name . '  ' . $this->billing_postal_code . PHP_EOL
			. $this->billing_country->name;

		return $str;
	}

	public function set_status($status) {
		return $this->set('status', $status)
			->is_valid()
			->save();
	}

	public function calculate_shipping() {
		if ( ! $this->loaded()) {
			return $this;
		}

		if (Cart_Config::enable_shipping()) {
			$possible_shipping_rates = array();
			$shipping_total = 0;

			$shipping_rates = ORM::factory('Cart_Shipping')
				->where_active_dates()
				->find_all();
			// first want a list of all the possible shipping rates
			foreach ($shipping_rates as $shipping_rate) {
				if ( ! empty($shipping_rate->data['reasons']) && is_array($shipping_rate->data['reasons'])) {
					foreach ($shipping_rate->data['reasons'] as $reason) {
						switch ($reason['reason']) {
							case 'flat_rate' :
								$possible_shipping_rates[$shipping_rate->pk()]['model'] = $shipping_rate;
								$possible_shipping_rates[$shipping_rate->pk()]['order_amount'] = Cart::calc_method($shipping_rate->calculation_method, $shipping_rate->amount, $this->_sub_total);
								break;
						} // switch reasons
					} // foreach reasons
				} // if reasons
			} // foreach shipping rates

			// now loop through the shipping rates to find the cheapest one
			$lowest_shipping_rate = NULL;
			$selected_shipping_rate = NULL;
			foreach ($possible_shipping_rates as $shipping_rate) {
				if ($lowest_shipping_rate === NULL || $shipping_rate['order_amount'] < $lowest_shipping_rate) {
					$lowest_shipping_rate = $shipping_rate['order_amount'];
					$selected_shipping_rate = $shipping_rate;
				}
			}

			$existing_shipping_rate = $this->cart_order_shipping->find();
			$add_shipping = FALSE;
			$cart_order_shipping_data = array(
				'cart_order_id' => $this->pk(),
				'cart_shipping_id' => $selected_shipping_rate['model']->pk(),
				'display_name' => $selected_shipping_rate['model']->display_name(),
				'amount' => $selected_shipping_rate['order_amount'],
				'manual_flag' => 0,
				'data' => $selected_shipping_rate['model']->data(),
			);
			if ($existing_shipping_rate->loaded()) {
				if ($existing_shipping_rate->cart_shipping_id == $selected_shipping_rate['model']->pk()) {
					$existing_shipping_rate->values($cart_order_shipping_data)
						->save();
				} else {
					$this->add_log('remove_shipping', array(
						'cart_order_shipping_id' => $existing_shipping_rate->pk(),
						'cart_shipping_id' => $existing_shipping_rate->cart_shipping_id,
					));
					$existing_shipping_rate->delete();
					$add_shipping = TRUE;
				}
			} else {
				$add_shipping = TRUE;
			}
			if ($add_shipping) {
				$new_shipping_rate = ORM::factory('Cart_Order_Shipping')
					->values($cart_order_shipping_data)
					->save();
				$this->add_log('add_shipping', array(
					'cart_order_shipping_id' => $new_shipping_rate->pk(),
					'cart_shipping_id' => $new_shipping_rate->cart_shipping_id,
				));
			}

			if ( ! empty($selected_shipping_rate)) {
				$shipping_total = $selected_shipping_rate['order_amount'];
				$this->_sub_total += $shipping_total;
			}
		}

		return $this;
	}

	public function calculate_additional_charges() {
		if ( ! $this->loaded()) {
			return $this;
		}

		$additional_charge_total = 0;
		$additional_charges = ORM::factory('Cart_Additional_Charge')
			->where_active_dates()
			->find_all();
		$existing_additional_charges = $this->cart_order_additional_charge->find_all()
			->as_array('cart_additional_charge_id');
		foreach ($additional_charges as $additional_charge) {
			// if it's a manual charge, we don't want to make any changes to the charge
			// but we do want to include it's total in the total
			if ($additional_charge->data['manual']) {
				// because it's manual, it may not be on the cart
				if (isset($existing_additional_charges[$additional_charge->pk()])) {
					$cart_order_additional_charge = $existing_additional_charges[$additional_charge->pk()];
					unset($existing_additional_charges[$additional_charge->pk()]);

					$additional_charge_total += $cart_order_additional_charge->quantity * $cart_order_additional_charge->amount;
				}

				continue;
			}

			// if the charge already exists on the order, make sure it's up to date
			// if it doesn't exist, add the charge
			if (isset($existing_additional_charges[$additional_charge->pk()])) {
				$cart_order_additional_charge = $existing_additional_charges[$additional_charge->pk()];
				unset($existing_additional_charges[$additional_charge->pk()]);
				$add_additional_charge = FALSE;
			} else {
				$cart_order_additional_charge = ORM::factory('Cart_Order_Additional_Charge');
				$add_additional_charge = TRUE;
			}

			$cart_order_additional_charge->values(array(
					'cart_order_id' => $this->pk(),
					'cart_additional_charge_id' => $additional_charge->pk(),
					'display_name' => $additional_charge->display_name(),
					'quantity' => 1,
					'amount' => Cart::calc_method($additional_charge->calculation_method, $additional_charge->amount, $this->_sub_total),
					'data' => $additional_charge->data(),
				))
				->save();

			$additional_charge_total += $cart_order_additional_charge->quantity * $cart_order_additional_charge->amount;

			if ($add_additional_charge) {
				$this->add_log('add_additional_charge', array(
					'cart_order_additional_charge_id' => $cart_order_additional_charge->pk(),
					'cart_additional_charge_id' => $cart_order_additional_charge->cart_additional_charge_id,
				));
			}
		}

		foreach ($existing_additional_charges as $cart_order_additional_charge) {
			$this->add_log('remove_additional_charge', array(
				'cart_order_additional_charge_id' => $cart_order_additional_charge->pk(),
				'cart_additional_charge_id' => $cart_order_additional_charge->cart_additional_charge_id,
			));

			$cart_order_additional_charge->delete();
		}

		$this->_sub_total += $additional_charge_total;

		return $this;
	}

	public function calculate_taxes() {
		if ( ! $this->loaded()) {
			return $this;
		}

		$this->_tax_total = 0;
		if (Cart_Config::enable_tax()) {
			$taxes = array();

			// get the taxes that apply to all loctions/all orders
			$all_location_taxes = ORM::factory('Cart_Tax')
				->where('all_locations_flag', '=', 1)
				->where('only_without_flag', '=', 0)
				->where_active_dates()
				->find_all();
			foreach($all_location_taxes as $tax) {
				$taxes[$tax->pk()] = $tax;
			}

			if ( ! empty($this->shipping_country_id)) {
				// get the taxes that apply to a specific country
				$country_taxes = ORM::factory('Cart_Tax')
					->where('country_id', '=', $this->shipping_country_id)
					->where('state_id', '=', 0)
					->where('only_without_flag', '=', 0)
					->where('all_locations_flag', '=', 0)
					->where_active_dates()
					->find_all();
				foreach ($country_taxes as $tax) {
					$taxes[$tax->pk()] = $tax;
				}

				if ( ! empty($this->shipping_state_id)) {
					// get the taxes that apply to a specific state/province
					$state_taxes = ORM::factory('Cart_Tax')
						->where('country_id', '=', $this->shipping_country_id)
						->where('state_id', '=', $this->shipping_state_id)
						->where('only_without_flag', '=', 0)
						->where('all_locations_flag', '=', 0)
						->where_active_dates()
						->find_all();
					foreach ($state_taxes as $tax) {
						$taxes[$tax->pk()] = $tax;
					}
				}
			}

			// if we haven't applied any other taxes, retrieve the taxes that are applied when no other taxes are applied
			if (empty($taxes)) {
				$only_without_taxes = ORM::factory('Cart_Tax')
					->where('only_without_flag', '=', 1)
					->where('all_locations_flag', '=', 0)
					->where_active_dates()
					->find_all();
				foreach ($only_without_taxes as $tax) {
					$taxes[$tax->pk()] = $tax;
				}
			}

			$applied_taxes = array();
			foreach ($taxes as $tax) {
				$amount = Cart::calc_method($tax->calculation_method, $tax->amount, $this->_sub_total);

				$applied_taxes[$tax->pk()] = array(
					'cart_order_id' => $this->pk(),
					'cart_tax_id' => $tax->pk(),
					'display_name' => $tax->display_name(),
					'amount' => $amount,
					'display_order' => $tax->display_order,
					'data' => $tax->data(),
				);

				$this->_tax_total += $amount;
			}

			$existing_taxes = $this->cart_order_tax
				->find_all()
				->as_array('cart_tax_id');
			$keep_of_existing = array();
			foreach ($applied_taxes as $cart_tax_id => $tax_data) {
				if (isset($existing_taxes[$cart_tax_id])) {
					$existing_taxes[$cart_tax_id]->values($tax_data)
						->save();
					$keep_of_existing[] = $existing_taxes[$cart_tax_id]->pk();
				} else {
					$new_tax = ORM::factory('Cart_Order_Tax')
						->values($tax_data)
						->save();
					$this->add_log('add_tax', array(
						'cart_order_tax_id' => $new_tax->pk(),
						'cart_tax_id' => $new_tax->cart_tax_id,
					));
				}
			}
			foreach ($existing_taxes as $tax) {
				if ( ! in_array($tax->pk(), $keep_of_existing)) {
					$this->add_log('remove_tax', array(
						'cart_order_tax_id' => $tax->pk(),
						'cart_tax_id' => $tax->cart_tax_id,
					));
					$tax->delete();
				}
			}
		}
	}

	public function calculate_totals() {
		if ( ! $this->loaded()) {
			return $this;
		}

		$this->_sub_total = 0;
		foreach ($this->cart_order_product->find_all() as $order_product) {
			$this->_sub_total += $order_product->unit_price * $order_product->quantity;
		}

		$this->calculate_shipping();
		$this->calculate_additional_charges();
		$this->calculate_taxes();

		$grand_total = $this->_sub_total + $this->_tax_total;

		return $this->values(array(
				'sub_total' => $this->_sub_total,
				'grand_total' => $grand_total,
			))
			->is_valid()
			->save();
	}

	public function clear_taxes() {
		foreach ($this->cart_order_tax->find_all() as $tax) {
			$tax->delete();
		}

		return $this;
	}

	public function add_log($action, $data = array()) {
		ORM::factory('Cart_Order_Log')
			->values(array(
				'cart_order_id' => $this->pk(),
				'user_id' => (Auth::instance()->logged_in() ? Auth::instance()->get_user()->pk() : 0),
				'timestamp' => Date::formatted_time(),
				'action' => $action,
				'data' => $data,
			))
			->save();

		return $this;
	}

	/**
	 * Generates the order number and sets it on the object.
	 * Uses the first character of the first and last names and the primary key/ID padded with 0's.
	 *
	 * @return  Model_Order
	 */
	public function generate_order_num() {
		// first char of the first & last name and then capitalize
		$this->order_num = strtoupper(substr(UTF8::clean($this->billing_first_name), 0, 1) . substr(UTF8::clean($this->billing_last_name), 0, 1))
			// pad the id/primary key with up to 6 0's
			. sprintf('%06d', $this->pk());

		return $this;
	}

	/**
	 * Returns the description of the order for the Stripe charge.
	 *
	 * @return  string
	 */
	public function stripe_charge_description() {
		return 'Payment for ' . $this->order_num;
	}

	/**
	 * Retrieves a product on the order.
	 *
	 * @param   int  $cart_product_id  The `cart_product_id`.
	 *
	 * @return  Model_Order_Product
	 */
	public function product($cart_product_id) {
		return $this->cart_order_product
			->where('cart_order_product.cart_product_id', '=', $cart_product_id)
			->find();
	}
} // class