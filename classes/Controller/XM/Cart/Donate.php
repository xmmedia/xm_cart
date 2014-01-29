<?php defined('SYSPATH') or die ('No direct script access.');

class Controller_XM_Cart_Donate extends Controller_Public {
	public function before() {
		parent::before();

		// make sure donations are enabled before continuing
		if ( ! Cart_Config::donation_cart()) {
			throw new HTTP_Exception_404('Donations have not been enabled');
		}

		if ($this->auto_render) {
			$this->add_style('cart_public', 'xm_cart/css/public.css')
				// ->add_script('stripe_v2', 'https://js.stripe.com/v2/')
				->add_script('cart_base', 'xm_cart/js/base.min.js')
				/*->add_script('cart_public', 'xm_cart/js/public.min.js')*/;
		}
	}

	public function action_index() {
		$order = Cart::retrieve_user_order();
		if (empty($order) || ! is_object($order)) {
			unset($order);
		} else {
			$order_product_count = count($order->cart_product->find_all());
			$order_has_other_products = ($order_product_count > 1 && Cart::has_donation_product($order));
		}

		$this->template->body_html = View::factory('cart_donate/index')
			->bind('order', $order)
			->bind('order_has_other_products', $order_has_other_products);
	}

	public function action_submit_donation() {
		$donation = $this->request->post('donation') * 1;
		if (empty($donation)) {
			Message::add('Please enter a donation amount before continuing.', Message::$error);
			$this->redirect(Route::get('cart_donate')->uri());
		}

		$donation_minimum = Kohana::$config->load('xm_cart.donation_minimum');
		$donation_maximum = Kohana::$config->load('xm_cart.donation_maximum');
		if ($donation < $donation_minimum) {
			Message::add('We have a minimum donation amount of $' . Cart::cf($donation_minimum) . ' to ensure we cover the fees incured while processing the donation.', Message::$error);
			$this->redirect(Route::get('cart_donate')->uri());
		} else if ($donation > $donation_maximum) {
			Message::add('It looks like your donation is higher than our maximum online donation of ' . Cart::cf($donation_maximum) . '. We have this maximum because of the fees that are charged when using a credit card. Please contact us to discuss other options.', Message::$error);
			$this->redirect(Route::get('cart_donate')->uri());
		}

		$donation_product = ORM::factory('Cart_Product', Kohana::$config->load('xm_cart.donation_product_id'));
		if ( ! $donation_product->loaded()) {
			throw new Kohana_Exception('The donation product could not be found');
		}

		$order = Cart::retrieve_user_order(TRUE, array('donation_cart_flag' => 1));

		// if there are other products in the cart, create a new empty cart
		$order_product_count = count($order->cart_product->find_all());
		$has_donation_product = Cart::has_donation_product($order);
		if (( ! empty($order_product_count) && $has_donation_product) || ($order_product_count > 1 && $has_donation_product)) {
			Cart::delete_order($order);
			$order = Cart::retrieve_user_order(TRUE);
		}

		$order_product = ORM::factory('Cart_Order_Product', array(
				'cart_order_id' => $order->pk(),
				'cart_product_id' => $donation_product->pk(),
			))->values(array(
				'cart_order_id' => $order->pk(),
				'cart_product_id' => $donation_product->pk(),
				'quantity' => 1,
				'unit_price' => $donation,
			))
			->save();

		$order->calculate_totals()
			->add_log('add_product', array(
				'cart_order_product_id' => $order_product->pk(),
				'cart_product_id' => $order_product->cart_product_id,
				'quantity' => $order_product->quantity,
				'unit_price' => $donation_product->cost,
				'name' => $donation_product->name,
			));

		$this->redirect(Route::get('cart_public')->uri(array('action' => 'checkout')));
	}
}