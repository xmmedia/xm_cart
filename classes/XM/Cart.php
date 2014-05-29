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
		switch ($calculation_method) {
			case '%' :
				return $total * ($amount / 100);
				break;
			case '$' :
				return $amount;
				break;
			case 'f' :
				return 0;
				break;
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
	 * Deletes the user cookie.
	 *
	 * @return  boolean
	 */
	public static function user_cookie_delete() {
		return Cookie::delete('xm_cart_order');
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
	 * This is run after order is retrieved and verified that is can be edited in the checkout action.
	 * The returned order will be used within the checkout action.
	 * By default, it checks to make sure the cart_product still exists and the unit price is correct.
	 * An example of what could be done include making sure all the products are still active.
	 *
	 * @param   Model_Cart_Order  $order  The order model.
	 *
	 * @return  Model_Cart_Order
	 */
	public static function pre_checkout($order) {
		$order_products = $order->cart_order_product->find_all();
		foreach ($order_products as $order_product) {
			if ( ! $order_product->cart_product->loaded()) {
				$order->add_log('cleaned_product', array(
						'cart_order_product_id' => $order_product->id,
						'cart_product_id' => $order_product->cart_product_id,
					));

				$order_product->delete();
				continue;
			}

			if ($order_product->unit_price != $order_product->cart_product->cost) {
				$order_product->set('unit_price', $order_product->cart_product->cost)
					->save();

				$order->add_log('change_unit_price', array(
						'cart_order_product_id' => $order_product->pk(),
						'cart_product_id' => $cart_product->pk(),
						'quantity' => $order_product->quantity,
						'unit_price' => $order_product->unit_price,
						'name' => $cart_product->name,
					));
			}
		}

		return $order;
	}

	/**
	 * Loads the user's information into the order.
	 * Called while loading the checkout page.
	 * It should check if the fields are already filled out as it's possible the user has already started the checkout process.
	 *
	 * @param   Model_Cart_Order  $order  The order model.
	 *
	 * @return  Model_Cart_Order
	 */
	public static function load_user_address($order) {
		return $order;
	}

	/**
	 * Runs when an order is completed.
	 * By default sends emails to the customer and admin
	 * and sets the `xm_cart.last_order_id` in the session.
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

		Session::instance()->set_path('xm_cart.last_order_id', $order->pk());

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

		$donation_order_product = $order->product(Cart_Config::load('donation_product_id'));
		if ($donation_order_product->loaded()) {
			return TRUE;
		}

		return FALSE;
	}

	public static function expiry_months() {
		return array(
			'' => 'Month',
			1 => '01',
			2 => '02',
			3 => '03',
			4 => '04',
			5 => '05',
			6 => '06',
			7 => '07',
			8 => '08',
			9 => '09',
			10 => '10',
			11 => '11',
			12 => '12',
		);
	}

	public static function expiry_years() {
		$array = array(
			'' => 'Year',
		);
		for ($y = date('Y'); $y <= date('Y') + 10; $y ++) {
			$array[$y] = $y;
		}

		return $array;
	}

	/**
	 * Loads the Stripe config and classes and sets the secret key and API version based on the config.
	 * Returns the Stripe config.
	 * Uses the constant `STRIPE_CONFIG`.
	 *
	 * @return  array
	 */
	public static function load_stripe() {
		$stripe_config = (array) Cart_Config::load('payment_processor_config.stripe.' . STRIPE_CONFIG);
		if (empty($stripe_config['secret_key']) || empty($stripe_config['publishable_key'])) {
			throw new Kohana_Exception('Stripe has not been fully configured');
		}

		if ( ! Kohana::load(Kohana::find_file('vendor', 'stripe/Stripe'))) {
			throw new Kohana_Exception('Unable to load the Stripe libraries');
		}

		Stripe::setApiKey($stripe_config['secret_key']);
		Stripe::setApiVersion($stripe_config['api_version']);

		return $stripe_config;
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

	/**
	 * Returns the page titles used in the cart (PHP) views.
	 *
	 * @param   string  $title  The page title.
	 *
	 * @return  string
	 */
	public static function page_header($title) {
		return '<h1>' . $title . '</h1>';
	}

	/**
	 * Processes a Stripe exception.
	 * If the exception is not a Stripe exception, it will throw the error again, but will still add the payment error.
	 * If `$order` is not passed, it will call `Cart::stripe_error_order_log()`.
	 * If `$add_messages` is TRUE, messages specific to the error will be added to the session.
	 * Returns the type of error (key `type`). In the case of a card error, additional data is returned:
	 *
	 * Key     | Type     | Value
	 * --------|----------|---------------------
	 * `type`  | `string` | The type of error, in this case "card_error".
	 * `code`  | `string` | The error code from Stripe. They full list can be found here: https://stripe.com/docs/api#errors
	 * `error` | `array`  | The full array or the error data.
	 *
	 * @param   Exception    $e             The exception.
	 * @param   Model_Cart_Order  $order    The order model.
	 * @param   Model_Cart_Order_ayment  $order_payment  The order payment model.
	 * @param   boolean      $add_messages  If set to TRUE, it will add messages.
	 *
	 * @return  array
	 */
	public static function handle_stripe_exception($e, $order = NULL, $order_payment = NULL, $add_messages = TRUE) {
		try {
			throw $e;
		} catch(Stripe_CardError $e) {
			// Since it's a decline, Stripe_CardError will be caught
			Kohana::$log->add(Kohana_Log::ERROR, 'Stripe CardError')->write();
			Kohana_Exception::log($e);

			$error_data = (array) $e->getJsonBody();
			$error  = $error_data['error'];
			Kohana::$log->add(Kohana_Log::ERROR, 'Stripe JSON Data: ' . print_r($error_data, TRUE))->write();
			// error has type, code, param and message keys
			// can also retrieve the HTTP status code: $e->getHttpStatus()

			if ($add_messages) {
				switch ($error['code']) {
					case 'incorrect_zip' :
						Message::add(Cart::message('stripe.incorrect_zip'), Message::$error);
						break;
					case 'card_declined' :
						Message::add(Cart::message('stripe.card_declined'), Message::$error);
						break;
					default :
						Message::add($error['message'], Message::$error);
						break;
				}
			}

			if ($order) {
				Cart::stripe_error_order_log($order, $order_payment, $error_data, TRUE, CART_PAYMENT_STATUS_DENIED);
			}

			return array(
				'type' => 'card_error',
				'code' => $error['code'],
				'error' => $error,
			);

		} catch (Stripe_InvalidRequestError $e) {
			// Invalid parameters were supplied to Stripe's API
			Kohana::$log->add(Kohana_Log::ERROR, 'Invalid parameters were supplied to Stripe\'s API')->write();
			Kohana_Exception::log($e);
			if ($add_messages) {
				Message::add(Cart::message('stripe.error'), Message::$error);
			}

			if ($order) {
				$error_data = (array) $e->getJsonBody();
				Cart::stripe_error_order_log($order, $order_payment, $error_data);
			}

			return array(
				'type' => 'invalid_request',
			);

		} catch (Stripe_AuthenticationError $e) {
			// Authentication with Stripe's API failed
			// (maybe you changed API keys recently)
			Kohana::$log->add(Kohana_Log::ERROR, 'Authentication with Stripe\'s API failed')->write();
			Kohana_Exception::log($e);
			if ($add_messages) {
				Message::add(Cart::message('stripe.error'), Message::$error);
			}

			if ($order) {
				$error_data = (array) $e->getJsonBody();
				Cart::stripe_error_order_log($order, $order_payment, $error_data);
			}

			return array(
				'type' => 'authentication_error',
			);

		} catch (Stripe_ApiConnectionError $e) {
			// Network communication with Stripe failed
			Kohana::$log->add(Kohana_Log::ERROR, 'Network communication with Stripe failed')->write();
			Kohana_Exception::log($e);
			if ($add_messages) {
				Message::add(Cart::message('stripe.error'), Message::$error);
			}

			if ($order) {
				$error_data = (array) $e->getJsonBody();
				Cart::stripe_error_order_log($order, $order_payment, $error_data);
			}

			return array(
				'type' => 'api_connection_error',
			);

		} catch (Stripe_Error $e) {
			// Display a very generic error to the user, and maybe send
			// yourself an email
			Kohana::$log->add(Kohana_Log::ERROR, 'General Stripe error')->write();
			Kohana_Exception::log($e);
			if ($add_messages) {
				Message::add(Cart::message('stripe.error'), Message::$error);
			}

			if ($order) {
				$error_data = (array) $e->getJsonBody();
				Cart::stripe_error_order_log($order, $order_payment, $error_data);
			}

			return array(
				'type' => 'stripe_error',
			);

		} catch (Exception $e) {
			if ($add_messages) {
				Message::add(Cart::message('fail'), Message::$error);
			}

			if ($order) {
				$error_data = (array) $e->getJsonBody();
				Cart::stripe_error_order_log($order, $order_payment, $error_data, FALSE);
			}

			throw $e;
		}
	}

	/**
	 * Sets the order status to "submitted" and payment status to "payment error" and add an order log and payment log record.
	 *
	 * @param   Model_Cart_Order  $order       The order model.
	 * @param   Model_Cart_Order_ayment  $order_payment  The order payment model.
	 * @param   array        $error_data  The data regarding the error, typically the JSON body from Stripe.
	 *
	 * @return  void
	 */
	public static function stripe_error_order_log(Model_Cart_Order $order, Model_Cart_Order_Payment $order_payment, $error_data, $set_order_status = TRUE, $payment_status = CART_PAYMENT_STATUS_ERROR) {
		if ($set_order_status) {
			// set the status back to submitted because there was a problem with the payment
			$order->set_status(CART_ORDER_STATUS_SUBMITTED);
		}

		$order->add_log('payment_error', $error_data);
		$order_payment->values(array(
				'status' => CART_PAYMENT_STATUS_ERROR,
				'response' => $error_data,
			))
			->save()
			->add_log(CART_PAYMENT_STATUS_ERROR, $error_data);
	}

	/**
	 * Retrieves the last order based on the session key: `xm_cart.last_order_id`.
	 * Returns `NULL` if the order can't be loaded.
	 *
	 * @return  Model_Cart_Order
	 */
	public static function last_order() {
		$last_order_id = Session::instance()->path('xm_cart.last_order_id');
		if ( ! empty($last_order_id)) {
			$order = ORM::factory('Cart_Order', $last_order_id);
			if ($order->loaded()) {
				return $order;
			}
		}

		return NULL;
	}
}