<?php defined('SYSPATH') or die ('No direct script access.');

class Controller_XM_Cart_Donate extends Controller_Public {
	public function before() {
		parent::before();

		if ($this->auto_render) {
			$this->add_style('cart_public', 'xm_cart/css/public.css')
				// ->add_script('stripe_v2', 'https://js.stripe.com/v2/')
				->add_script('cart_base', 'xm_cart/js/base.min.js')
				/*->add_script('cart_public', 'xm_cart/js/public.min.js')*/;
		}
	}

	public function action_index() {
		$this->template->body_html = View::factory('cart_donate/index');
	}

	public function action_submit_donation() {
		$donation = $this->request->post('donation') * 1;
		if (empty($donation)) {
			Message::add('Please enter a donation amount before continuing.', Message::$error);
			$this->redirect(Route::get('cart_donate')->uri());
		}

		$donation_minimum = Kohana::$config->load('xm_cart.donation_minimum');
		if ($donation < $donation_minimum) {
			Message::add('We have a minimum donation amount of $' . $donation_minimum . ' to ensure we cover the fees in processing the donation.', Message::$error);
			$this->redirect(Route::get('cart_donate')->uri());
		}

		$donation_product = ORM::factory('Cart_Product', Kohana::$config->load('xm_cart.donation_product_id'));
		if ( ! $donation_product->loaded()) {
			throw new Kohana_Exception('The donation product could not be found');
		}

		$order = Cart::retrieve_user_order(TRUE);

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