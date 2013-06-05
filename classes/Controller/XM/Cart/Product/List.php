<?php defined('SYSPATH') or die ('No direct script access.');

class Controller_XM_Cart_Product_List extends Controller_Public {
	public function before() {
		parent::before();

		if ($this->auto_render) {
			$this->add_style('cart_public', 'xm_cart/css/public.css')
				->add_script('cart_base', 'xm_cart/js/base.min.js')
				->add_script('cart_public', 'xm_cart/js/public.min.js');
		}
	}

	public function action_index() {
		$products = ORM::factory('Cart_Product')
			->find_all();
		$_products = array();
		foreach ($products as $product) {
			$_products[] = '<li>'
				. Form::open(Route::get('cart_public')->uri(array('action' => 'add_product', 'id' => $product->id)), array('method' => 'POST', 'class' => 'js_cart_add_product'))
					. HTML::chars($product->name) . ' $' . $product->cost
					. Form::hidden('cart_product_id', $product->id, array('class' => 'js_cart_product_id'))
					. Form::input('quantity', 1, array('size' => 3, 'maxlength' => 5, 'class' => 'js_cart_order_product_quantity'))
					. Form::submit(NULL, 'Add to Cart')
				. Form::close()
				. '</li>';
		}

		$this->template->body_html = View::factory('cart_product_list/index')
			->set('product_list', implode('', $_products))
			->set('cart_prefix', (string) Kohana::$config->load('xm_cart.prefix'));
	}
}