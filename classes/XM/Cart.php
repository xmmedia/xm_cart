<?php defined('SYSPATH') or die ('No direct script access.');

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

		if (Kohana::$config->load('xm_cart.enable_sub_total')) {
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

	public static function retrieve_user_order($create = FALSE) {
		$order_id = Session::instance()->path('xm_cart.cart_order_id');

		// if there is an order in the session, attempt to retrieve it
		// if we can't, unset the $order var and we'll just create a new one
		if ( ! empty($order_id)) {
			$order = ORM::factory('Cart_Order', $order_id);
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
			$order = ORM::factory('Cart_Order')
				->values(array(
					'user_id' => (Auth::instance()->logged_in() ? Auth::instance()->get_user()->pk() : 0),
					'country_id' => (int) Kohana::$config->load('xm_cart.default_country_id'),
					'status' => CART_ORDER_STATUS_NEW,
				))
				->save()
				->add_log('created');
		}

		if (isset($order) && $order->loaded()) {
			Session::instance()->set_path('xm_cart.cart_order_id', $order->pk());

			return $order;
		} else {
			return NULL;
		}
	} // function retrieve_order

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
		$enable_shipping = (bool) Kohana::$config->load('xm_cart.enable_shipping');
		$enable_tax = (bool) Kohana::$config->load('xm_cart.enable_tax');
		$donation_cart = (bool) Kohana::$config->load('xm_cart.donation_cart');
		$email_cart_view = ($donation_cart ? 'cart/email/cart_donation' : 'cart/email/cart');

		$order_products = $order->cart_order_product->find_all()->as_array();
		$total_rows = Cart::total_rows($order);
		$paid_with = array(
			'type' => $order_payment->response['card']['type'],
			'last_4' => $order_payment->response['card']['last4'],
		);

		$mail = new Mail();
		$mail->IsHTML(TRUE);

		$have_customer_email = FALSE;
		if ($enable_shipping &&  ! empty($order->shipping_email) && Valid::email($order->shipping_email)) {
			$mail->AddAddress($order->shipping_email, $order->shipping_first_name . ' ' . $order->shipping_last_name);
			$have_customer_email = TRUE;
		}
		if (( ! $enable_shipping || UTF8::strtolower($order->shipping_email) != UTF8::strtolower($order->billing_email)) && ! empty($order->billing_email) && Valid::email($order->billing_email)) {
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
				->bind('enable_shipping', $enable_shipping)
				->bind('enable_tax', $enable_tax)
				->bind('donation_cart', $donation_cart);

			$subject_title_data = Cart::prefix_message_data($order->as_array());

			$mail->Subject = Cart::message('email.customer_order.subject' . ($donation_cart ? '_donation' : ''), $subject_title_data);
			$mail->Body = View::factory('cart/email/template')
				->set('title', Cart::message('email.customer_order.email_title' . ($donation_cart ? '_donation' : ''), $subject_title_data))
				->bind('body_html', $email_body_html);
			$mail->Send();
		}
	}

	public static function send_admin_order_email($order, $order_payment) {
		$enable_shipping = (bool) Kohana::$config->load('xm_cart.enable_shipping');
		$enable_tax = (bool) Kohana::$config->load('xm_cart.enable_tax');
		$donation_cart = (bool) Kohana::$config->load('xm_cart.donation_cart');
		$email_cart_view = ($donation_cart ? 'cart/email/cart_donation' : 'cart/email/cart');
		$administrator_email = Kohana::$config->load('xm_cart.administrator_email');

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
			->bind('enable_shipping', $enable_shipping)
			->bind('enable_tax', $enable_tax)
			->bind('donation_cart', $donation_cart);

		$subject_title_data = Cart::prefix_message_data($order->as_array());

		$mail->Subject = Cart::message('email.admin_order.subject' . ($donation_cart ? '_donation' : ''), $subject_title_data);
		$mail->Body = View::factory('cart/email/template')
			->set('title', Cart::message('email.admin_order.email_title' . ($donation_cart ? '_donation' : ''), $subject_title_data))
			->bind('body_html', $email_body_html);
		$mail->Send();
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