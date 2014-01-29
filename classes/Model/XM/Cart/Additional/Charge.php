<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Model for `cart_additional_charge`.
 *
 * @package    XM
 * @category   Models
 * @author     XM Media Inc.
 * @copyright  (c) 2013 XM Media Inc.
 */
class Model_XM_Cart_Additional_Charge extends Cart_ORM {
	protected $_table_names_plural = FALSE;
	protected $_table_name = 'cart_additional_charge';
	public $_table_name_display = 'Cart - Additional Charge'; // xm specific

	// default sorting
	protected $_sorting = array(
		'display_order' => 'ASC',
		'name' => 'ASC',
	);

	// relationships
	// protected $_has_one = array();
	protected $_has_many = array(
		'cart_order_additional_charge' => array(
			'model' => 'Cart_Order_Additional_Charge',
			'foreign_key' => 'cart_additional_charge_id',
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
		'name' => array(
			'field_type' => 'Text',
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
			'field_attributes' => array(
				'maxlength' => 100,
			),
		),
		'display_name' => array(
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
		'start' => array(
			'field_type' => 'DateTime',
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
		),
		'end' => array(
			'field_type' => 'DateTime',
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
		),
		'calculation_method' => array(
			'field_type' => 'Radios',
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
			'field_options' => array(
				'source' => array(
					'source' => 'array',
					'data' => array(
						'%' => 'Percentage (%)',
						'$' => 'Dollar Value ($)'
					),
				),
				'default_value' => '$',
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
				'class' => 'numeric',
			),
		),
		'display_order' => array(
			'field_type' => 'Text',
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
			'field_attributes' => array(
				'maxlength' => 5,
				'size' => 5,
			),
		),
		'data' => array(
			'field_type' => 'Serializable',
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

	protected $_serialize_columns = array('data');

	/**
	 * Labels for columns.
	 *
	 * @return  array
	 */
	public function labels() {
		return array(
			'id' => 'ID',
			'expiry_date' => 'Expiry Date',
			'name' => 'Name',
			'display_name' => 'Display Name',
			'start' => 'Start',
			'end' => 'End',
			'calculation_method' => 'Calculation Method',
			'amount' => 'Amount',
			'display_order' => 'Display Order',
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
			'name' => array(
				array('not_empty'),
			),
			'display_name' => array(
				array('not_empty'),
			),
			'calculation_method' => array(
				array('not_empty'),
			),
			'amount' => array(
				array('not_empty'),
			),
			// data probably shouldn't be empty either
			// but we can't add that requirement till we've added the admin tool
		);
	}

	/**
	 * Filter definitions, run everytime a field is set.
	 *
	 * @return  array
	 */
	public function filters() {
		return array(
			'name' => array(
				array('trim'),
			),
			'display_name' => array(
				array('trim'),
			),
		);
	}

	public function display_name() {
		if ($this->calculation_method == '%') {
			return $this->display_name . ' ' . Num::format($this->amount, Cart::num_decimals($this->amount)) . '%';
		} else if ($this->calculation_method == '$') {
			return $this->display_name;
		}
	}

	public function data() {
		return Arr::extract($this->as_array(), array('name', 'display_name', 'start', 'end', 'display_order', 'calculation_method', 'amount', 'data'));
	}
} // class