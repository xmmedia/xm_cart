<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Model for `cart_order_log`.
 *
 * @package    XM
 * @category   Models
 * @author     XM Media Inc.
 * @copyright  (c) 2013 XM Media Inc.
 */
class Model_XM_Cart_Order_Log extends ORM {
	protected $_table_names_plural = FALSE;
	protected $_table_name = 'cart_order_log';
	// protected $_primary_val = 'name'; // default: name (column used as primary value)
	public $_table_name_display = 'Cart - Order - Log'; // cl4 specific

	// default sorting
	// protected $_sorting = array();

	// relationships
	// protected $_has_one = array();
	// protected $_has_many = array();
	protected $_belongs_to = array(
		'cart_order' => array(
			'model' => 'Cart_Order',
			'foreign_key' => 'cart_order_id',
		),
		'user' => array(
			'model' => 'User',
			'foreign_key' => 'user_id',
		),
	);

	// column definitions
	protected $_table_columns = array(
		'id' => array(
			'field_type' => 'Hidden',
			'edit_flag' => TRUE,
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
					'label' => 'invoice',
				),
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
		'timestamp' => array(
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
		'action' => array(
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
		'data' => array(
			'field_type' => 'TextArea',
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
		),
	);

	/**
	 * Labels for columns.
	 *
	 * @return  array
	 */
	public function labels() {
		return array(
			'id' => 'ID',
			'cart_order_id' => 'Cart Order',
			'user_id' => 'User',
			'timestamp' => 'Timestamp',
			'action' => 'Action',
			'data' => 'Data',
		);
	}

	/**
	 * Rule definitions for validation.
	 *
	 * @return  array
	 */
	public function rules() {
		return array(
			'cart_order_id' => array(
				array('selected'),
			),
		);
	}
} // class