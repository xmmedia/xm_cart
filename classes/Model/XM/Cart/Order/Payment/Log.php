<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Model for `cart_order_payment_log`.
 *
 * @package    XM
 * @category   Models
 * @author     XM Media Inc.
 * @copyright  (c) 2013 XM Media Inc.
 */
class Model_XM_Cart_Order_Payment_Log extends ORM {
	protected $_table_names_plural = FALSE;
	protected $_table_name = 'cart_order_payment_log';
	// protected $_primary_val = 'name'; // default: name (column used as primary value)
	public $_table_name_display = 'Cart - Order - Payment - Log'; // cl4 specific

	// disable logging of the log table
	protected $_log = FALSE;

	// default sorting
	// protected $_sorting = array();

	// relationships
	// protected $_has_one = array();
	// protected $_has_many = array();
	protected $_belongs_to = array(
		'cart_order_payment' => array(
			'model' => 'Cart_Order_Payment',
			'foreign_key' => 'cart_order_payment_id',
		),
	);

	// column definitions
	protected $_table_columns = array(
		'id' => array(
			'field_type' => 'Hidden',
			'edit_flag' => TRUE,
			'is_nullable' => FALSE,
		),
		'cart_order_payment_id' => array(
			'field_type' => 'Select',
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
			'field_options' => array(
				'source' => array(
					'source' => 'model',
					'data' => 'Cart_Order_Payment',
					'label' => 'id',
				),
			),
		),
		'timestamp' => array(
			'field_type' => 'DateTime',
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
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
		'status_string' => array(
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
		'details' => array(
			'field_type' => 'TextArea',
			// 'list_flag' => TRUE,
			// 'edit_flag' => TRUE,
			// 'search_flag' => TRUE,
			// 'view_flag' => TRUE,
			'is_nullable' => FALSE,
		),
	);

	protected $_serialize_columns = array('details');

	protected function _initialize() {
		parent::_initialize();

		$this->_table_columns['status']['field_options']['source']['data'] = (array) Kohana::$config->load('xm_cart.payment_status_labels');
	}

	/**
	 * Labels for columns.
	 *
	 * @return  array
	 */
	public function labels() {
		return array(
			'id' => 'ID',
			'cart_order_payment_id' => 'Cart Order Payment',
			'timestamp' => 'Timestamp',
			'status' => 'Status',
			'status_string' => 'Status String',
			'details' => 'Details',
		);
	}

	/**
	 * Rule definitions for validation.
	 *
	 * @return  array
	 */
	public function rules() {
		return array(
			'cart_order_payment_id' => array(
				array('selected'),
			),
		);
	}
} // class