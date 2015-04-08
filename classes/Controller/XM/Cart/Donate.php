<?php defined('SYSPATH') or die ('No direct script access.');

/**
 *
 *
 * @package    XM Cart
 * @category   Controllers
 * @author     XM Media Inc.
 * @copyright  (c) 2014 XM Media Inc.
 */
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

		$this->template->page_title = 'Donate Now' . $this->page_title_append;
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

		$donation_minimum = Cart_Config::load('donation_minimum');
		$donation_maximum = Cart_Config::load('donation_maximum');
		if ($donation < $donation_minimum) {
			Message::add('We have a minimum donation amount of ' . Cart::cf($donation_minimum) . ' to ensure we cover the fees incured while processing the donation.', Message::$error);
			$this->redirect(Route::get('cart_donate')->uri());
		} else if ($donation > $donation_maximum) {
			Message::add('It looks like your donation is higher than our maximum online donation of ' . Cart::cf($donation_maximum) . '. We have this maximum because of the fees that are charged when using a credit card. Please contact us to discuss other options.', Message::$error);
			$this->redirect(Route::get('cart_donate')->uri());
		}

		$donation_product = ORM::factory('Cart_Product', Cart_Config::load('donation_product_id'));
		if ( ! $donation_product->loaded()) {
			throw new Kohana_Exception('The donation product could not be found');
		}

		$order = Cart::retrieve_user_order(TRUE, array('donation_cart_flag' => 1));

		// if there are other products in the cart, create a new empty cart
		$order_product_count = $order->cart_product->find_all()->count();
		$has_donation_product = Cart::has_donation_product($order);
		if (($order_product_count == 0 && $has_donation_product) || ($order_product_count > 1 && $has_donation_product)) {
			Cart::empty_cart($order);
			$order = Cart::retrieve_user_order(TRUE);
		}

		$order->add_product($donation_product, 1, $donation);

		$this->redirect(Route::get('cart_public')->uri(array('action' => 'checkout')));
	}
}