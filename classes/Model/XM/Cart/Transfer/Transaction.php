<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Model for `cart_transfer_transaction`.
 *
 * @package    XM Cart
 * @category   Models
 * @author     XM Media Inc.
 * @copyright  (c) 2016 XM Media Inc.
 */
class Model_XM_Cart_Transfer_Transaction extends Cart_ORM {
	protected $_table_names_plural = FALSE;
	protected $_table_name = 'cart_transfer_transaction';
	public $_table_name_display = 'Cart - Transfer Transaction'; // xm specific

	// column definitions
	protected $_table_columns = array(
		'id' => array(
			'field_type' => 'Hidden',
			'edit_flag' => TRUE,
			'is_nullable' => FALSE,
		),
		'transfer_id' => array(
			'field_type' => 'Select',
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
			'field_options' => array(
				'source' => array(
					'source' => 'model',
					'data' => 'Transfer',
				),
			),
		),
		'stripe_id' => array(
			'field_type' => 'Select',
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
			'field_options' => array(
				'source' => array(
					'source' => 'model',
					'data' => 'Stripe',
				),
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

	protected $_serialize_columns = array('data');

	/**
	 * Labels for columns.
	 *
	 * @return  array
	 */
	public function labels() {
		return array(
			'id' => 'ID',
			'transfer_id' => 'Transfer ID',
			'stripe_id' => 'Stripe ID',
			'data' => 'Data',
		);
	}
} // class