<?php defined('SYSPATH') or die ('No direct script access.');

/**
 * Displays and processes the donation form.
 *
 * @package    XM Cart
 * @category   Controllers
 * @author     XM Media Inc.
 * @copyright  (c) 2015 XM Media Inc.
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

	/**
	 * Displays the donation form.
	 *
	 * @return  void
	 */
	public function action_index() {
		$default_donation_amount = $this->default_donation_amount();

		$order = Cart::retrieve_user_order();
		if (empty($order) || ! is_object($order)) {
			unset($order);
		} else {
			$order_product_count = count($order->cart_product->find_all());
			$order_has_other_products = ($order_product_count > 1 && Cart::has_donation_product($order));
			$default_donation_amount = $this->default_donation_amount($order);
		}

		$this->template->page_title = 'Donate Now' . $this->page_title_append;
		$this->template->body_html = View::factory('cart_donate/index')
			->bind('order', $order)
			->bind('order_has_other_products', $order_has_other_products)
			->bind('default_donation_amount', $default_donation_amount);
	}

	/**
	 * Processes the donation form, sending the user to the checkout.
	 *
	 * @return  void
	 */
	public function action_submit_donation() {
		$donation = $this->request->post('donation');
		if (empty($donation)) {
			Message::add('Please enter a donation amount before continuing.', Message::$error);
			$this->redirect(Route::get('cart_donate')->uri());
		}

		// remove the dollar sign and commas so we can get a propery float
		$donation = str_replace(array('$', ','), '', $donation) * 1;

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

		// if there's a donation product in the cart
		// but we'll delete the donation product and then add it again
		if (Cart::has_donation_product($order)) {
			$donation_order_product = $order->product($donation_product->pk());
			if ($donation_order_product->loaded()) {
				$donation_order_product->delete();
			}
		}

		$order->add_product($donation_product, 1, $donation);

		$this->redirect(Route::get('cart_public')->uri(array('action' => 'checkout')));
	}

	/**
	 * Returns the default donation amount, based on the donation minimum.
	 * But if there is already a donation product in the cart, it will use the unit_price (the donation amount) as the default.
	 *
	 * @param   Model_Cart_Order  $order  The order model.
	 *
	 * @return  float
	 */
	protected function default_donation_amount($order = NULL) {
		$default_donation_amount = Cart_Config::load('donation_minimum');

		if ( ! empty($order) && is_object($order)) {
			$donation_product = ORM::factory('Cart_Product', Cart_Config::load('donation_product_id'));
			if ( ! $donation_product->loaded()) {
				throw new Kohana_Exception('The donation product could not be found');
			}

			$donation_order_product = $order->product($donation_product->pk());
			$default_donation_amount = $donation_order_product->unit_price;
		}

		return $default_donation_amount;
	}
}