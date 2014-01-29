<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Model for `cart_tax`.
 *
 * @package    XM Cart
 * @category   Models
 * @author     XM Media Inc.
 * @copyright  (c) 2014 XM Media Inc.
 */
class Model_XM_Cart_Tax extends Cart_ORM {
	protected $_table_names_plural = FALSE;
	protected $_table_name = 'cart_tax';
	public $_table_name_display = 'Cart - Tax'; // xm specific

	// default sorting
	protected $_sorting = array(
		'display_order' => 'ASC',
		'name' => 'ASC',
	);

	// relationships
	// protected $_has_one = array();
	protected $_has_many = array(
		'cart_order_tax' => array(
			'model' => 'Cart_Order_Tax',
			'foreign_key' => 'cart_tax_id',
		),
	);
	protected $_belongs_to = array(
		'country' => array(
			'model' => 'Country',
			'foreign_key' => 'country_id',
		),
		'state' => array(
			'model' => 'State',
			'foreign_key' => 'state_id',
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
				'maxlength' => 25,
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
		'all_locations_flag' => array(
			'field_type' => 'Checkbox',
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
		),
		'only_without_flag' => array(
			'field_type' => 'Checkbox',
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
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
		'state_id' => array(
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
				'default_value' => '%',
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
				'maxlength' => 6,
				'size' => 6,
				'class' => 'numeric',
			),
		),
		/**
		 * Options:
		 * applies_to_shipping: if true, the tax will also be applied to shipping
		 */
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

	protected $_options = array(
		'add_field_help' => TRUE,
	);

	protected $_field_help = array(
		'name' => array(
			'add' => 'Used within the cart admin.',
			'edit' => 'Used within the cart admin.',
		),
		'display_name' => array(
			'add' => 'Displayed to the user.',
			'edit' => 'Displayed to the user.',
		),
		'all_locations_flag' => array(
			'add' => 'Tax will be applied to all addresses/locations.',
			'edit' => 'Tax will be applied to all addresses/locations.',
		),
		'only_without_flag' => array(
			'add' => 'Only add this when the address/location does not have any other taxes applied to it.',
			'edit' => 'Only add this when the address/location does not have any other taxes applied to it.',
		),
		'display_order' => array(
			'add' => 'The order in which the taxes will be shown on the order.',
			'edit' => 'The order in which the taxes will be shown on the order.',
		),
		'amount' => array(
			'add' => 'The percentage (ie, 5 for 5%) or the fixed dollar value (ie, 5 for $5).',
			'edit' => 'The percentage (ie, 5 for 5%) or the fixed dollar value (ie, 5 for $5).',
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
			'expiry_date' => 'Expiry Date',
			'name' => 'Name',
			'display_name' => 'Display Name',
			'start' => 'Start',
			'end' => 'End',
			'all_locations_flag' => 'All Locations',
			'only_without_flag' => 'Only Without Specific',
			'country_id' => 'Country',
			'state_id' => 'State',
			'display_order' => 'Display Order',
			'calculation_method' => 'Calculation Method',
			'amount' => 'Amount',
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

	public static function show_country_select() {
		$taxes_with_country = ORM::factory('Cart_Tax')
			->where('country_id', '>', 0)
			->where_active_dates()
			->find_all();
		return count($taxes_with_country) > 0;
	}

	public static function show_state_select($country_id) {
		$taxes_with_states_for_country = ORM::factory('Cart_Tax')
			->where('country_id', '=', $country_id)
			->where('state_id', '>', 0)
			->where_active_dates()
			->find_all();
		return count($taxes_with_states_for_country) > 0;
	}

	public function display_name() {
		if ($this->calculation_method == '%') {
			$amount_display = Num::format($this->amount, Cart::num_decimals($this->amount)) . '%';
		} else if ($this->calculation_method == '$') {
			$amount_display = Cart::cf($this->amount);
		}

		return $this->display_name . ' ' . $amount_display;
	}

	public function data() {
		return Arr::extract($this->as_array(), array('name', 'display_name', 'start', 'end', 'all_locations_flag', 'only_without_flag', 'country_id', 'state_id', 'display_order', 'calculation_method', 'amount', 'data'));
	}
} // class