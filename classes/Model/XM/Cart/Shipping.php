<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Model for `cart_shipping`.
 *
 * Some sample JSON data for the data attribute:
 *
 * Flat rate:
 *
 *     {"reasons":[{"reason":"flat_rate"}]}
 *
 * Order total between $0 and $100:
 *
 *     {"reasons":[{"reason":"order_total","min":0,"max":100}]}
 *
 * Order total above $100.01 (works along side the above one):
 *
 *     {"reasons":[{"reason":"order_total","min":100.01}]}
 *
 * Shipping location of Alberta, Canada:
 *
 *     {"reasons":[{"reason":"shipping_location","locations":[{"country_id":40,"state_id":1}]}]}
 *
 * No other rate (will be applied if no other rate applies):
 *
 *     {"reasons":[{"reason":"no_other_rate"}]}
 *
 * Shipping location of Alberta or BC, Canada and minimum order of $100:
 *
 *     {"reasons":[{"reason":"shipping_location","locations":[{"country_id":40,"state_id":1},{"country_id":40,"state_id":7}]},{"reason":"order_total","min":100}]}
 *
 *
 * @package    XM
 * @category   Models
 * @author     XM Media Inc.
 * @copyright  (c) 2013 XM Media Inc.
 */
class Model_XM_Cart_Shipping extends ORM {
	protected $_table_names_plural = FALSE;
	protected $_table_name = 'cart_shipping';
	public $_table_name_display = 'Cart - Shipping'; // cl4 specific

	// default sorting
	protected $_sorting = array(
		'display_order' => 'ASC',
		'name' => 'ASC',
	);

	// relationships
	// protected $_has_one = array();
	protected $_has_many = array(
		'cart_order_shipping' => array(
			'model' => 'Cart_Order_Shipping',
			'foreign_key' => 'cart_shipping_id',
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
				'maxlength' => 50,
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
			'field_options' => array(
				'24_hour' => TRUE,
			),
		),
		'end' => array(
			'field_type' => 'DateTime',
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
			'field_options' => array(
				'24_hour' => TRUE,
			),
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
				'maxlength' => 6,
				'size' => 6,
			),
		),
		'data' => array(
			'field_type' => 'TextArea',
			// 'list_flag' => TRUE,
			// 'edit_flag' => TRUE,
			// 'search_flag' => TRUE,
			// 'view_flag' => TRUE,
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

	public function where_active_dates() {
		return $this
			->where_open()
				->or_where_open()
					->where('start', '<=', DB::expr("NOW()"))
					->where('end', '>=', DB::expr("NOW()"))
				->or_where_close()
				->or_where_open()
					->where('start', '<=', DB::expr("NOW()"))
					->where('end', '=', 0)
				->or_where_close()
				->or_where_open()
					->where('start', '=', 0)
					->where('end', '>=', DB::expr("NOW()"))
				->or_where_close()
				->or_where_open()
					->where('start', '=', 0)
					->where('end', '=', 0)
				->or_where_close()
			->where_close();
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