<?php defined('SYSPATH') or die ('No direct script access.');

/**
 *
 *
 * @package    XM Cart
 * @category   Helpers
 * @author     XM Media Inc.
 * @copyright  (c) 2014 XM Media Inc.
 */
class XM_Cart {
	/**
	 * Returns the formatted number, with currency symbole.
	 *
	 * @param   float  $num  The float to format for currency.
	 * @return  string
	 */
	public static function cf($num) {
		return '$' . Num::format($num, 2, TRUE);
	}

	public static function address_html($str) {
		return UTF8::str_ireplace(array(PHP_EOL, '  '), array('<br>' . PHP_EOL, '&nbsp;&nbsp;'), HTML::chars($str));
	}

	public static function num_decimals($num) {
		$num = trim($num, 0);

		if ((int) $num == (float) $num) {
			return 0;
		}

		return (strlen($num) - strrpos($num, '.') - 1);
	}

	public static function countries() {
		$countries = array();
		foreach (ORM::factory('country')->find_all() as $country) {
			$countries[] = array('id' => $country->pk(), 'name' => $country->name);
		}

		return $countries;
	}

	public static function calc_method($calculation_method, $amount, $total) {
		if ($calculation_method == '%') {
				return $total * ($amount / 100);
			} else if ($calculation_method == '$') {
				return $amount;
			}
	}

	public static function calc_method_display($calculation_method, $amount) {
		return ($calculation_method == '$' ? '$' : '') . $amount . ($calculation_method == '%' ? '%' : '');
	}

	public static function total_rows($order) {
		$total_rows = array();

		$shipping = $order->cart_order_shipping->find();
		if ($shipping->loaded()) {
			$total_rows[] = array(
				'name' => $shipping->display_name,
				'value' => $shipping->amount,
				'value_formatted' => Cart::cf($shipping->amount),
			);
		}

		foreach ($order->cart_order_additional_charge->find_all() as $additional_charge) {
			$total_rows[] = array(
				'name' => $additional_charge->display_name,
				'value' => ($additional_charge->quantity * $additional_charge->amount),
				'value_formatted' => Cart::cf($additional_charge->quantity * $additional_charge->amount),
			);
		}

		if (Cart_Config::load('enable_sub_total')) {
			$total_rows[] = array(
				'name' => 'Sub Total',
				'value' => $order->sub_total,
				'value_formatted' => Cart::cf($order->sub_total),
			);
		}

		foreach ($order->cart_order_tax->find_all() as $tax) {
			$total_rows[] = array(
				'name' => $tax->display_name,
				'value' => $tax->amount,
				'value_formatted' => Cart::cf($tax->amount),
			);
		}

		$total_rows[] = array(
			'name' => 'Total',
			'value' => $order->grand_total,
			'value_formatted' => Cart::cf($order->grand_total),
			'is_grand_total' => TRUE,
		);

		return $total_rows;
	}

	/**
	 * Retrieve the value of the cart order cookie.
	 * If set, this will be the order unique ID.
	 * Otherwise it will be `NULL`.
	 *
	 * @return  string
	 */
	public static function user_cookie_value_retrieve() {
		return Cookie::get('xm_cart_order');
	}

	/**
	 * Sets the user cookie with the unique ID.
	 *
	 * @param   string  $unique_id  The order unique ID.
	 *
	 * @return  boolean
	 */
	public static function user_cookie_value_set($unique_id) {
		return Cookie::set('xm_cart_order', $unique_id, Cart_Config::load('user_cookie_expiration'));
	}

	/**
	 * Loads the order based on the passed unique ID.
	 *
	 * @param   string  $unique_id  The order unique ID.
	 *
	 * @return  Model_Cart_Order
	 */
	public static function load_order($unique_id) {
		return ORM::factory('Cart_Order')
			->where('unique_id', '=', $unique_id)
			->find();
	}

	/**
	 * Retrieves a users order based on the cookie unique ID.
	 * Will create an order if $create is TRUE.
	 * The $new_order_defaults array will override the other default values set: user_id, country_id, and status.
	 * Will return NULL if there is order and not creating an order.
	 *
	 * @param   boolean  $create              If TRUE, a new cart will be created.
	 * @param   array    $new_order_defaults  Array of defaults to set on the order.
	 *
	 * @return  Model_Cart_Order
	 */
	public static function retrieve_user_order($create = FALSE, array $new_order_defaults = array()) {
		$unique_id = Cart::user_cookie_value_retrieve();

		// if there is an order unique ID in the cookie, attempt to retrieve it
		// if we can't, unset the $order var and we'll just create a new one
		if ( ! empty($unique_id)) {
			$order = Cart::load_order($unique_id);
			if ( ! $order->loaded()) {
				unset($order);
			}

			// only allow access to new orders and those that have been submitted, but not paidec
			if (isset($order) && ! Cart::allow_order_edit($order)) {
				unset($order);
			}

			if (isset($order)) {
				// make sure they own the order
				// it's possible they weren't logged in, but then did login so the order user_id will 0/unset
				if (Auth::instance()->logged_in() && ! empty($order->user_id) && Auth::instance()->get_user()->pk() != $order->user_id) {
					unset($order);
				// user is logged in and the current order is unassigned, so assign it to them
				} else if (Auth::instance()->logged_in() && empty($order->user_id)) {
					$order->set('user_id', Auth::instance()->get_user()->pk())
						->save()
						->add_log('set_user');
				}
			}
		}

		// no order found, just create a new one
		if ( ! isset($order) && $create) {
			if (empty($unique_id)) {
				$unique_query = DB::select('id')
					->from('cart_order')
					->where('unique_id', '=', ':unique_id')
					->limit(1)
					->bind(':unique_id', $unique_id);

				do {
					$unique_id = sha1(uniqid(NULL, TRUE) . Cart_Config::load('salt'));

					$result = $unique_query->execute();
				} while ($result->count());
			}

			$order = ORM::factory('Cart_Order')
				->values(array(
					'unique_id' => $unique_id,
					'user_id' => (Auth::instance()->logged_in() ? Auth::instance()->get_user()->pk() : 0),
					'country_id' => (int) Cart_Config::load('default_country_id'),
					'status' => CART_ORDER_STATUS_NEW,
				))
				->values($new_order_defaults)
				->save()
				->add_log('created');
		}

		if (isset($order) && $order->loaded()) {
			Cart::user_cookie_value_set($unique_id);

			return $order;
		} else {
			return NULL;
		}
	}

	/**
	 * Adds the log entry for "empty_cart" and sets the status of the cart to emptied.
	 * The cart cookie will be kept as the unique ID maybe be used for a new cart.
	 *
	 * @param   Model_Cart_Order  $order  The order to delete.
	 *
	 * @return  Model_Cart_Order
	 */
	public static function empty_cart($order) {
		return $order->add_log('empty_cart')
			->set('status', CART_ORDER_STATUS_EMPTIED)
			->save();
	}

	public static function allow_order_edit($order) {
		return in_array((int) $order->status, array(CART_ORDER_STATUS_NEW, CART_ORDER_STATUS_SUBMITTED), TRUE);
	}

	/**
	 * Runs when an order is completed.
	 * By default sends emails to the customer and admin.
	 *
	 * @param   Model_Cart_Order  $order  The order that was completed.
	 * @param   Model_Cart_Order_Payment  $order_payment  The order payment model that completed the order.
	 *
	 * @return  void
	 */
	public static function complete_order($order, $order_payment) {
		// send emails
		Cart::send_customer_order_email($order, $order_payment);
		Cart::send_admin_order_email($order, $order_payment);

		return;
	}

	public static function send_customer_order_email($order, $order_payment) {
		$is_donation_cart = (Cart_Config::donation_cart() && $order->donation_cart_flag);
		$email_cart_view = ($is_donation_cart ? 'cart/email/cart_donation' : 'cart/email/cart');

		$order_products = $order->cart_order_product->find_all()->as_array();
		$total_rows = Cart::total_rows($order);
		$paid_with = array(
			'type' => $order_payment->response['card']['type'],
			'last_4' => $order_payment->response['card']['last4'],
		);

		$mail = new Mail();
		$mail->IsHTML(TRUE);

		$have_customer_email = FALSE;
		if (Cart_Config::enable_shipping() &&  ! empty($order->shipping_email) && Valid::email($order->shipping_email)) {
			$mail->AddAddress($order->shipping_email, $order->shipping_first_name . ' ' . $order->shipping_last_name);
			$have_customer_email = TRUE;
		}
		if (( ! Cart_Config::enable_shipping() || UTF8::strtolower($order->shipping_email) != UTF8::strtolower($order->billing_email)) && ! empty($order->billing_email) && Valid::email($order->billing_email)) {
			$mail->AddAddress($order->billing_email, $order->billing_first_name . ' ' . $order->billing_last_name);
			$have_customer_email = TRUE;
		}
		if ($have_customer_email) {
			$email_body_html = View::factory('cart/email/customer_order')
				->bind('order', $order)
				->bind('order_product_array', $order_products)
				->bind('total_rows', $total_rows)
				->bind('paid_with', $paid_with)
				->bind('cart_view', $email_cart_view)
				->set('enable_shipping', Cart_Config::enable_shipping())
				->set('enable_tax', Cart_Config::enable_tax())
				->set('donation_cart', $is_donation_cart);

			$subject_title_data = Cart::prefix_message_data(array_merge(array('company' => LONG_NAME), $order->as_array()));

			$mail->Subject = Cart::message('email.customer_order.subject' . ($is_donation_cart ? '_donation' : ''), $subject_title_data);
			$mail->Body = View::factory('cart/email/template')
				->set('title', Cart::message('email.customer_order.email_title' . ($is_donation_cart ? '_donation' : ''), $subject_title_data))
				->bind('body_html', $email_body_html);
			$mail->Send();
		}
	}

	public static function send_admin_order_email($order, $order_payment) {
		$is_donation_cart = (Cart_Config::donation_cart() && $order->donation_cart_flag);
		$email_cart_view = ($is_donation_cart ? 'cart/email/cart_donation' : 'cart/email/cart');
		$administrator_email = Cart_Config::load('administrator_email');

		$order_products = $order->cart_order_product->find_all()->as_array();
		$total_rows = Cart::total_rows($order);
		$paid_with = array(
			'type' => $order_payment->response['card']['type'],
			'last_4' => $order_payment->response['card']['last4'],
		);

		$mail = new Mail();
		$mail->IsHTML(TRUE);

		$mail->AddAddress($administrator_email['email'], $administrator_email['name']);

		$email_body_html = View::factory('cart/email/admin_order')
			->bind('order', $order)
			->bind('order_product_array', $order_products)
			->bind('total_rows', $total_rows)
			->bind('paid_with', $paid_with)
			->bind('cart_view', $email_cart_view)
			->set('enable_shipping', Cart_Config::enable_shipping())
			->set('enable_tax', Cart_Config::enable_tax())
			->set('donation_cart', $is_donation_cart);

		$subject_title_data = Cart::prefix_message_data(array_merge(array('company' => LONG_NAME), $order->as_array()));

		$mail->Subject = Cart::message('email.admin_order.subject' . ($is_donation_cart ? '_donation' : ''), $subject_title_data);
		$mail->Body = View::factory('cart/email/template')
			->set('title', Cart::message('email.admin_order.email_title' . ($is_donation_cart ? '_donation' : ''), $subject_title_data))
			->bind('body_html', $email_body_html);
		$mail->Send();
	}

	public static function has_donation_product($order) {
		if ( ! Cart_Config::donation_cart()) {
			return FALSE;
		}

		$donation_order_product = $order->cart_order_product
			->where('cart_product_id', '=', Cart_Config::load('donation_product_id'))
			->find();
		if ($donation_order_product->loaded()) {
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Returns a translated Kohana message from within the xm_cart message file.
	 * Essentially a shortcut.
	 *
	 * @param   string  $path    The path to the message inside the xm_cart message file.
	 * @param   array   $params  The merge parameters.
	 *
	 * @return  string
	 */
	public static function message($path, array $params = array()) {
		return __(Kohana::message('xm_cart', $path), $params);
	}

	/**
	 * Adds a colon infront of each key so it can be used within messages.
	 * Useful when attempting to send an entire object to Message.
	 *
	 * @param   array  $data  The array to prefix.
	 *
	 * @return  array
	 */
	protected static function prefix_message_data(array $data) {
		$_data = array();

		foreach ($data as $key => $value) {
			$_data[':' . $key] = $value;
		}

		return $_data;
	}
}