<?php defined('SYSPATH') or die ('No direct script access.');

/**
 *
 *
 * @package    XM Cart
 * @category   Controllers
 * @author     XM Media Inc.
 * @copyright  (c) 2014 XM Media Inc.
 */
class Controller_XM_Cart_Admin extends Controller_Private {
	public $page = 'cart_admin';

	public function before() {
		parent::before();

		$this->page_title_append = 'Cart Admin - ' . $this->page_title_append;

		if ($this->auto_render) {
			$this->add_style('cart_private', 'xm_cart/css/private.css')
				->add_script('cart_base', 'xm_cart/js/base.min.js');
		}
	}

	/**
	 * List recent orders, show current shipping and tax rates, available discounts.
	 */
	public function action_index() {
		$this->template->page_title = $this->page_title_append;
		$this->template->body_html = View::factory('cart_admin/index');
	}

	protected function order_uri() {
		return Route::get('cart_admin_order')->uri();
	}

	protected function shipping_uri() {
		return Route::get('cart_admin_shipping')->uri();
	}

	protected function tax_uri() {
		return Route::get('cart_admin_tax')->uri();
	}
}