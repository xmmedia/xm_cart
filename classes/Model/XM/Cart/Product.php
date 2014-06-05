<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Model for `cart_product`.
 *
 * @package    XM Cart
 * @category   Models
 * @author     XM Media Inc.
 * @copyright  (c) 2014 XM Media Inc.
 */
class Model_XM_Cart_Product extends Cart_ORM {
	protected $_table_names_plural = FALSE;
	protected $_table_name = 'cart_product';
	public $_table_name_display = 'Cart - Product'; // xm specific

	// default sorting
	protected $_sorting = array(
		'name' => 'ASC',
	);

	// relationships
	// protected $_has_one = array();
	protected $_has_many = array(
		'cart_discount_product' => array(
			'model' => 'Cart_Discount_Product',
			'foreign_key' => 'cart_product_id',
		),
		'cart_order_product' => array(
			'model' => 'Cart_Order_Product',
			'foreign_key' => 'cart_product_id',
		),
		'cart_product_property' => array(
			'model' => 'Cart_Product_Property',
			'foreign_key' => 'cart_product_id',
		),
		'cart_property' => array(
			'model' => 'Cart_Property',
			'through' => 'cart_product_property',
			'foreign_key' => 'cart_product_id',
			'far_key' => 'cart_property_id',
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
		'part_number' => array(
			'field_type' => 'Text',
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
			'field_attributes' => array(
				'maxlength' => 20,
				'size' => 20,
			),
		),
		'name' => array(
			'field_type' => 'Text',
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
			'field_attributes' => array(
				'maxlength' => 255,
			),
		),
		'description' => array(
			'field_type' => 'TextArea',
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
		),
		'cost' => array(
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
		'photo_filename' => array(
			'field_type' => 'File',
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
			'field_options' => array(
				'file_options' => array(
					'destination_folder' => CART_PRODUCT_PHOTO_PATH,
					'name_change_method' => 'id',
					'ext_check_only' => TRUE,
					'allowed_extensions' => array('jpg', 'png', 'gif'),
					'model_name' => 'Cart_Product',
				),
			),
		),
		'inventory_available' => array(
			'field_type' => 'Text',
			'list_flag' => TRUE,
			'edit_flag' => TRUE,
			'search_flag' => TRUE,
			'view_flag' => TRUE,
			'is_nullable' => FALSE,
			'field_attributes' => array(
				'maxlength' => 11,
				'size' => 11,
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
			'part_number' => 'Part Number',
			'name' => 'Name',
			'description' => 'Description',
			'cost' => 'Cost',
			'photo_filename' => 'Photo',
			'inventory_available' => 'Inventory Available',
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
		);
	}

	/**
	 * Filter definitions, run everytime a field is set.
	 *
	 * @return  array
	 */
	public function filters() {
		return array(
			'part_number' => array(
				array('trim'),
			),
			'name' => array(
				array('trim'),
			),
			'description' => array(
				array('trim'),
			),
		);
	}

	/**
	 * Returns the name of the product, possibly including the part number.
	 *
	 * @return  string
	 */
	public function name() {
		if (Cart_Config::show_product_part_number()) {
			return $this->part_number . ' – ' . $this->name;
		} else {
			return $this->name;
		}
	}

	/**
     * Generates the URI for the photo.
     *
     * @return  string
     */
    public function photo_uri() {
        return Route::get('cart_public_photo')->uri(array(
            'id' => $this->pk(),
            'timestamp' => $this->timestamp(),
        ));
    }

    /**
     * Returns the timestamp of the photo or NULL if the file doesn't exist.
     *
     * @return  int
     */
    public function timestamp() {
        $file_path = $this->get_filename_with_path('photo_filename');
        if (file_exists($file_path)) {
            return filemtime($file_path);
        } else {
            return NULL;
        }
    }

    /**
     * Returns the public URI to the product.
     * By default it just returns the continue shopping URL/URI.
     *
     * @return  string
     */
    public function view_uri() {
    	return Cart_Config::continue_shopping_url();
    }
} // class