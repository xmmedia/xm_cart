<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Model for `cart_order_transaction`.
 *
 * @package    XM Cart
 * @category   Models
 * @author     XM Media Inc.
 * @copyright  (c) 2014 XM Media Inc.
 */
class Model_XM_Cart_Order_Transaction extends Cart_ORM {
	protected $_table_names_plural = FALSE;
	protected $_table_name = 'cart_order_transaction';
	// protected $_primary_val = 'name'; // default: name (column used as primary value)
	public $_table_name_display = 'Cart - Order - Transaction'; // xm specific

	// default sorting
	protected $_sorting = array(
		'date_completed' => 'DESC',
	);

	// relationships
	protected $_has_many = array(
		'cart_order_transaction_log' => array(
			'model' => 'Cart_Order_Transaction_Log',
			'foreign_key' => 'cart_order_transaction_id',
		),
	);
	protected $_belongs_to = array(
		'cart_order' => array(
			'model' => 'Cart_Order',
			'foreign_key' => 'cart_order_id',
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
		'cart_order_id' => array(
			'field_type' => 'Select',
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
			'field_options' => array(
				'source' => array(
					'source' => 'model',
					'data' => 'Cart_Order',
					'label' => 'order_num',
				),
			),
		),
		'date_attempted' => array(
			'field_type' => 'DateTime',
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
		),
		'date_completed' => array(
			'field_type' => 'DateTime',
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
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
		'ip_address' => array(
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
		'payment_processor' => array(
			'field_type' => 'Select',
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
			'field_options' => array(
				'source' => array(
					'source' => 'array',
					'data' => array(),
				),
			),
		),
		'type' => array(
			'field_type' => 'Select',
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
			'field_options' => array(
				'source' => array(
					'source' => 'array',
					'data' => array(),
				),
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
					'data' => array(),
				),
			),
		),
		'amount' => array(
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
		'data' => array(
			'field_type' => 'Serializable',
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
		),
		'response' => array(
			'field_type' => 'Serializable',
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
		),
		'transaction_id' => array(
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
	);

	/**
	 * @var  array  $_expires_column  The time this row expires and is no longer returned in standard searches.
	 * Use format => 'Y-m-j H:i:s' for DATETIMEs and format => TRUE for TIMESTAMPs.
	 */
	protected $_expires_column = array(
		'column' 	=> 'expiry_date',
		'default'	=> 0,
	);

	protected $_serialize_columns = array('data', 'response');

	protected function _initialize() {
		parent::_initialize();

		$this->_table_columns['payment_processor']['field_options']['source']['data'] = (array) Cart_Config::load('payment_processors.' . PAYMENT_PROCESSOR_LIST);
		$this->_table_columns['type']['field_options']['source']['data'] = (array) Cart_Config::load('transaction_type_labels');
		$this->_table_columns['status']['field_options']['source']['data'] = (array) Cart_Config::load('transaction_status_labels');
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
			'cart_order_id' => 'Cart Order',
			'date_attempted' => 'Date Attempted',
			'date_completed' => 'Date Completed',
			'user_id' => 'User',
			'ip_address' => 'IP Address',
			'payment_processor' => 'Payment Processor',
			'type' => 'Type',
			'status' => 'Status',
			'amount' => 'Amount',
			'payment_processor_fee' => 'Payment Processor Fee',
			'data' => 'Data',
			'response' => 'Response',
			'transaction_id' => 'Transaction',
		);
	}

	/**
	 * Rule definitions for validation.
	 *
	 * @return  array
	 */
	public function rules() {
		return array(
			'cart_order' => array(
				array('selected'),
			),
			'payment_processor' => array(
				array('selected'),
			),
		);
	}

	public function add_log($status, $details) {
		ORM::factory('Cart_Order_Transaction_Log')
			->values(array(
				'cart_order_transaction_id' => $this->id,
				'timestamp' => Date::formatted_time(),
				'status' => (is_int($status) ? $status : 0),
				'status_string' => (is_string($status) ? $status : ''),
				'details' => $details,
			))
			->save();

		return $this;
	}
} // class