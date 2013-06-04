<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Model for `cart_shipping_location`.
 *
 * @package    XM
 * @category   Models
 * @author     XM Media Inc.
 * @copyright  (c) 2013 XM Media Inc.
 */
class Model_XM_Cart_Shipping_Location extends ORM {
	protected $_table_names_plural = FALSE;
	protected $_table_name = 'cart_shipping_location';
	// protected $_primary_val = 'name'; // default: name (column used as primary value)
	public $_table_name_display = 'Cart - Shipping - Location'; // cl4 specific

	// default sorting
	// protected $_sorting = array();

	// relationships
	// protected $_has_one = array();
	// protected $_has_many = array();
	protected $_belongs_to = array(
		'cart_shipping' => array(
			'model' => 'Cart_Shipping',
			'foreign_key' => 'cart_shipping_id',
		),
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
		'cart_shipping_id' => array(
			'field_type' => 'Select',
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
			'field_options' => array(
				'source' => array(
					'source' => 'model',
					'data' => 'Cart_Shipping',
				),
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
			'cart_shipping_id' => 'Cart Shipping',
			'country_id' => 'Country',
			'state_id' => 'State',
		);
	}

	/**
	 * Rule definitions for validation.
	 *
	 * @return  array
	 */
	public function rules() {
		return array(
			'cart_shipping_id' => array(
				array('selected'),
			),
		);
	}
} // class