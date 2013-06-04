<?php defined('SYSPATH') or die ('No direct script access.');

class Controller_XM_Cart_Product_List extends Controller_Public {
	public function before() {
		parent::before();

		if ($this->auto_render) {
			$this->add_script('cart_public', 'xm_cart/js/public.min.js');
		}
	}

	public function action_index() {
		$products = ORM::factory('Cart_Product')
			->find_all();
		$_products = array();
		foreach ($products as $product) {
			$_products[] = '<li>' . HTML::chars($product->name) . ' '
				. HTML::anchor(Route::get('cart_public')->uri(array('action' => 'add_product', 'id' => $product->id)), 'Add to Cart', array('class' => 'js_cart_add_product'))
				. '</li>';
		}

		$this->template->body_html = View::factory('cart_product_list/index')
			->set('product_list', implode('', $_products))
			->set('cart_prefix', (string) Kohana::$config->load('xm_cart.prefix'));
	}
}