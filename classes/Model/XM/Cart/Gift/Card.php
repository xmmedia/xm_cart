<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Model for `cart_gift_card`.
 *
 * @package    XM
 * @category   Models
 * @author     XM Media Inc.
 * @copyright  (c) 2013 XM Media Inc.
 */
class Model_XM_Cart_Gift_Card extends Cart_ORM {
	protected $_table_names_plural = FALSE;
	protected $_table_name = 'cart_gift_card';
	// protected $_primary_val = 'name'; // default: name (column used as primary value)
	public $_table_name_display = 'Cart - Gift Card'; // cl4 specific

	// default sorting
	// protected $_sorting = array();

	// relationships
	// protected $_has_one = array();
	protected $_has_many = array(
		'cart_gift_card_log' => array(
			'model' => 'Cart_Gift_Card_Log',
			'foreign_key' => 'cart_gift_card_id',
		),
		'cart_order' => array(
			'model' => 'Cart_Order',
			'through' => 'cart_gift_card_log',
			'foreign_key' => 'cart_gift_card_id',
			'far_key' => 'cart_order_id',
		),
		'user' => array(
			'model' => 'User',
			'through' => 'cart_gift_card_log',
			'foreign_key' => 'cart_gift_card_id',
			'far_key' => 'user_id',
		),
	);
	// protected $_belongs_to = array();

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
		'code' => array(
			'field_type' => 'Text',
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
			'field_attributes' => array(
				'maxlength' => 25,
				'size' => 25,
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
				'maxlength' => 11,
				'size' => 11,
				'class' => 'numeric',
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

	/**
	 * Labels for columns.
	 *
	 * @return  array
	 */
	public function labels() {
		return array(
			'id' => 'ID',
			'expiry_date' => 'Expiry Date',
			'code' => 'Code',
			'amount' => 'Amount',
		);
	}

	/**
	 * Rule definitions for validation.
	 *
	 * @return  array
	 */
	public function rules() {
		return array(
			'code' => array(
				array('not_empty'),
			),
		);
	}

	/**
	 * Filter definitions, run everytime a field is set.
	 *
	 * @return  array
	 */
	public function filters() {
		return array(
			'code' => array(
				array('trim'),
			),
		);
	}
} // class