<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Model for `cart_order_product`.
 *
 * @package    XM
 * @category   Models
 * @author     XM Media Inc.
 * @copyright  (c) 2013 XM Media Inc.
 */
class Model_XM_Cart_Order_Product extends Cart_ORM {
	protected $_table_names_plural = FALSE;
	protected $_table_name = 'cart_order_product';
	// protected $_primary_val = 'name'; // default: name (column used as primary value)
	public $_table_name_display = 'Cart - Order - Product'; // xm specific

	// default sorting
	// protected $_sorting = array();

	// relationships
	// protected $_has_one = array();
	protected $_has_many = array(
		'cart_order_product_property' => array(
			'model' => 'Cart_Order_Product_Property',
			'foreign_key' => 'cart_order_product_id',
		),
		'cart_property' => array(
			'model' => 'Cart_Property',
			'through' => 'cart_order_product_property',
			'foreign_key' => 'cart_order_product_id',
			'far_key' => 'cart_property_id',
		),
	);
	protected $_belongs_to = array(
		'cart_order' => array(
			'model' => 'Cart_Order',
			'foreign_key' => 'cart_order_id',
		),
		'cart_product' => array(
			'model' => 'Cart_Product',
			'foreign_key' => 'cart_product_id',
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
		'cart_product_id' => array(
			'field_type' => 'Select',
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
			'field_options' => array(
				'source' => array(
					'source' => 'model',
					'data' => 'Cart_Product',
				),
			),
		),
		'quantity' => array(
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
		'unit_price' => array(
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
			'field_type' => 'TextArea',
			'list_flag' => TRUE,
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
			'cart_product_id' => 'Cart Product',
			'quantity' => 'Quantity',
			'unit_price' => 'Unit Price',
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
			'cart_product_id' => array(
				array('selected'),
			),
		);
	}

	public function amount() {
		return $this->quantity * $this->unit_price;
	}
} // class