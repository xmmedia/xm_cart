<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Model for `cart_shipping`.
 *
 * Some sample JSON data for the data attribute/field:
 *
 * Flat rate:
 *
 *     {"reasons":[{"reason":"flat_rate"}]}
 *
 * Sub total between $0 and $100:
 *
 *     {"reasons":[{"reason":"sub_total","min":0,"max":100}]}
 *
 * Sub total above $100.01 (works along side the above one):
 *
 *     {"reasons":[{"reason":"sub_total","greater_than":100.01}]}
 *
 * Shipping address of Alberta, Canada:
 *
 *     {"reasons":[{"reason":"shipping_address","locations":[{"country_id":40,"state_id":1}]}]}
 *
 * No other rate (will be applied if no other rate applies):
 *
 *     {"reasons":[{"reason":"no_other_rate"}]}
 *
 * Shipping address of Alberta or BC, Canada and minimum sub total of $100:
 *
 *     {"reasons":[{"reason":"shipping_address","locations":[{"country_id":40,"state_id":1},{"country_id":40,"state_id":7}]},{"reason":"sub_total","greater_than":100}]}
 *
 *
 * @package    XM Cart
 * @category   Models
 * @author     XM Media Inc.
 * @copyright  (c) 2014 XM Media Inc.
 */
class Model_XM_Cart_Shipping extends Cart_ORM {
	protected $_table_names_plural = FALSE;
	protected $_table_name = 'cart_shipping';
	public $_table_name_display = 'Cart - Shipping'; // xm specific

	// default sorting
	protected $_sorting = array(
		'amount' => 'ASC',
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
						'$' => 'Dollar Value ($)',
						'%' => 'Percentage (%)',
						'f' => 'Free',
					),
				),
				'default_value' => '$',
				'orientation' => 'vertical',
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
			'start' => 'Start Date',
			'end' => 'End Date',
			'calculation_method' => 'Calculation Method',
			'amount' => 'Shipping Rate',
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
				array(array($this, 'validate_amount'), array(':validation')),
			),
			'data' => array(
				array(array($this, 'validate_data'), array(':validation')),
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

	public function display_name() {
		if ($this->calculation_method == '%') {
			return $this->display_name . ' ' . Num::format($this->amount, Cart::num_decimals($this->amount)) . '%';
		} else if ($this->calculation_method == '$') {
			return $this->display_name;
		}
	}

	public function data() {
		return Arr::extract($this->as_array(), array('name', 'display_name', 'start', 'end', 'calculation_method', 'amount', 'data'));
	}

	/**
	 * Checks the amount field based on the value of the calculation_method field.
	 *
	 * @param   Validation  $validate  The validation object.
	 *
	 * @return  void
	 */
	public function validate_amount(Validation $validate) {
		if ($this->calculation_method != 'f' && empty($this->amount)) {
			$validate->error('amount', 'empty');
		}
	}

	/**
	 * Checks the data field which includes the reasons.
	 *
	 * @param   Validation  $validate  The validation object.
	 *
	 * @return  void
	 */
	public function validate_data(Validation $validate) {
		$reasons = (array) Arr::get($this->data, 'reasons');

		if (empty($reasons)) {
			$validate->error('data', 'one_reason');
		} else {
			foreach ($reasons as $reason) {
				switch ($reason['reason']) {
					case 'flat_rate' :
						// no extra validation needed
						break;

					case 'sub_total' :
						if (isset($reason['min']) || isset($reason['max'])) {
							if ($reason['min'] >= $reason['max']) {
								$validate->error('data', 'min_greater_max');
							} else if (($reason['max'] - $reason['min']) <= 0.01) {
								$validate->error('data', 'min_max_difference');
							}
						}
						break;

					case 'shipping_address' :
						break;
				}
			}
		}
	}
} // class