<?php defined('SYSPATH') or die ('No direct script access.');

class Controller_XM_Cart extends Controller_Public {
	public $no_auto_render_actions = array(
		// other actions
		'load_cart', 'add_product', 'remove_product', 'change_quantity', 'cart_empty',
		// checkout actions
		'save_shipping', 'save_billing', 'validate_payment', 'save_final', 'complete_order',
	);

	protected $continue_shopping_url;

	public function before() {
		parent::before();

		$this->continue_shopping_url = (string) Kohana::$config->load('xm_cart.continue_shopping_url');

		if ($this->auto_render) {
			$this->add_style('cart_public', 'xm_cart/css/public.css')
				->add_script('stripe_v2', 'https://js.stripe.com/v2/')
				->add_script('cart_base', 'xm_cart/js/base.min.js')
				->add_script('cart_public', 'xm_cart/js/public.min.js');
		}
	}

	public function action_load_cart() {
		$sub_total = $total = 0;

		$order = $this->retrieve_order();

		if ( ! empty($order) && is_object($order)) {
			$order_products = $order->cart_order_product->find_all();

			$order_product_array = array();
			foreach ($order_products as $order_product) {
				if ( ! $order_product->cart_product->loaded()) {
					continue;
				}

				$amount = $order_product->unit_price * $order_product->quantity;

				$order_product_array[] = array(
					'id' => $order_product->id,
					'cart_product_id' => $order_product->cart_product_id,
					'quantity' => $order_product->quantity,
					'unit_price' => $order_product->unit_price,
					'name' => $order_product->cart_product->name,
					'unit_price_formatted' => Cart::cf($order_product->unit_price),
					'amount_formatted' => Cart::cf($amount),
				);

				$sub_total += $amount;
			} // foreach

			$total = $sub_total; // plus tax + shipping + +++
		} else {
			$order_product_array = array();
		}

		AJAX_Status::echo_json(AJAX_Status::ajax(array(
			'products' => $order_product_array,
			'order' => array(
				'sub_total' => $sub_total,
				'sub_total_formatted' => Cart::cf($sub_total),
				'total' => $total,
				'total_formatted' => Cart::cf($total),
			)
		)));
	}

	public function action_add_product() {
		// retrieve the values out of the model array
		$cart_product_id = (int) $this->request->post('cart_product_id');
		$quantity = (int) $this->request->post('quantity');

		if (empty($cart_product_id)) {
			throw new Kohana_Exception('No cart_product_id was received');
		}

		// attempt to retrieve or create a new order
		$order = $this->retrieve_order(TRUE);

		// attempt to retrieve the existing product in the cart or create an empty object
		$order_product = ORM::factory('Cart_Order_Product', array(
			'cart_order_id' => $order->id,
			'cart_product_id' => $cart_product_id,
		));

		// make sure the product still exists (not expired)
		$product = ORM::factory('Cart_Product', $cart_product_id);
		if ( ! $product->loaded()) {
			// since the product has been expired, also remove the product from order (cart_order_product)
			if ($order_product->loaded()) {
				$order_product->delete();
			}

			// then throw and error because this is bad!
			throw new Kohana_Exception('The selected product is no longer available');
		}

		// everything seems successful, so save the cart_order_product record
		$order_product->values(array(
			'cart_order_id' => $order->id,
			'cart_product_id' => $cart_product_id,
			'quantity' => ($order_product->loaded() ? $order_product->quantity + $quantity : $quantity),
			'unit_price' => $product->cost,
		))->save();

		AJAX_Status::echo_json(AJAX_Status::success());
	} // function action_add_product

	public function action_remove_product() {
		// for deletion, the id in a route param
		$cart_order_product_id = (int) $this->request->post('cart_order_product_id');
		if (empty($cart_order_product_id)) {
			throw new Kohana_Exception('The cart_order_product_id was not received');
		}

		// attempt to retrieve the order
		$order = $this->retrieve_order(FALSE);
		// if no order was found, just get out since we can't really do anything anyway
		if ( ! is_object($order) || ! $order->loaded()) {
			AJAX_Status::is_json();
			echo json_encode(array());
		}

		// attempt to retrieve the existing product in the cart
		$order_product = ORM::factory('Cart_Order_Product', $cart_order_product_id);
		if ($order_product->loaded()) {
			$order_product->delete();
		}

		AJAX_Status::echo_json(AJAX_Status::success());
	}

	public function action_change_quantity() {
		// retrieve the values out of the model array
		$cart_order_product_id = (int) $this->request->post('cart_order_product_id');
		$quantity = (int) $this->request->post('quantity');

		if (empty($cart_order_product_id)) {
			throw new Kohana_Exception('No cart_order_product_id was received');
		}

		// attempt to retrieve or create a new order
		$order = $this->retrieve_order(TRUE);

		// attempt to retrieve the existing product in the cart or create an empty object
		$order_product = ORM::factory('Cart_Order_Product', $cart_order_product_id);

		if ($quantity == 0) {
			if ($order_product->loaded()) {
				$order_product->delete();
			}

			AJAX_Status::is_json();
			echo json_encode(array());

			return;
		}

		// make sure the product still exists (not expired)
		$product = ORM::factory('Cart_Product', $order_product->cart_product_id);
		if ( ! $product->loaded()) {
			// since the product has been expired, also remove the product from order (cart_order_product)
			if ($order_product->loaded()) {
				$order_product->delete();
			}

			// then throw and error because this is bad!
			throw new Kohana_Exception('The selected product is no longer available');
		}

		// everything seems successful, so save the cart_order_product record
		$order_product->values(array(
			'quantity' => $quantity,
			'unit_price' => $product->cost,
		))->save();

		AJAX_Status::echo_json(AJAX_Status::success());
	} // function action_change_quantity

	public function action_cart_empty() {
		$order = $this->retrieve_order();

		if (is_object($order) && $order->loaded()) {
			$order->delete();
			Session::instance()->set_path('xm_cart.cart_order_id', NULL);
		}

		AJAX_Status::echo_json(AJAX_Status::success());
	}

	// not ajax!!
	public function action_checkout() {
		$order = $this->retrieve_order();
		if ( ! is_object($order) || ! $order->loaded()) {
			Message::add('You don\'t have any products in your cart. Please browse our available products before checking out.', Message::$notice);
			$this->request->redirect($this->continue_shopping_url);
		}

		$order->for_user()
			->set_table_columns('same_as_shipping_flag', 'field_type', 'Hidden');

		$order_products = $order->cart_order_product->find_all();
		$total = $sub_total = 0;

		$order_product_array = array();
		foreach ($order_products as $order_product) {
			// make sure the product is still avaialble, otherwise remove it from the order
			if ( ! $order_product->cart_product->loaded()) {
				$order_product->delete();
				continue;
			}

			$order_product_array[] = $order_product;
			$sub_total += $order_product->unit_price * $order_product->quantity;
		} // foreach

		if (empty($order_product_array)) {
			Message::add('You don\'t have any products in your cart. Please browse our available products before checking out.', Message::$notice);
			$this->request->redirect($this->continue_shopping_url);
		}

		$total = $sub_total;

		$total_rows = array();
		$total_rows[] = array(
			'name' => 'Sub Total',
			'value' => $sub_total,
		);
		$total_rows[] = array(
			'name' => 'Total',
			'value' => $total,
			'class' => 'grand_total',
		);

		$cart_html = View::factory('cart/cart')
			->bind('order_product_array', $order_product_array)
			->bind('total_rows', $total_rows);

		$expiry_date_months = array(
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
		$expiry_date_years = array(
			'' => 'Year',
		);
		for ($y = date('Y'); $y <= date('Y') + 10; $y ++) {
			$expiry_date_years[$y] = $y;
		}

		$this->template->page_title = 'Checkout' . $this->page_title_append;
		$this->template->body_html = View::factory('cart/checkout')
			->bind('order', $order)
			->bind('cart_html', $cart_html)
			->bind('expiry_date_months', $expiry_date_months)
			->bind('expiry_date_years', $expiry_date_years)
			->set('continue_shopping_url', $this->continue_shopping_url);
	} // function action_checkout

	public function action_save_shipping() {
		$order = $this->retrieve_order();
		if ( ! is_object($order) || ! $order->loaded()) {
			Message::add('You don\'t have any products in your cart. Please browse our available products before checking out.', Message::$notice);
			AJAX_Status::echo_json(AJAX_Status::ajax(array(
				'status' => AJAX_Status::VALIDATION_ERROR,
				'redirect' => $this->continue_shopping_url,
			)));
			return;
		}

		$ajax_status = AJAX_Status::SUCCESSFUL;
		$shipping_display = '';

		try {
			$order->for_user()
				->only_allow_shipping()
				->save_values()
				->save();

			$shipping_display = View::factory('cart/shipping_display')
				->set('shipping_address', Cart::address_html($order->shipping_formatted()));
		} catch (ORM_Validation_Exception $e) {
			$ajax_status = AJAX_Status::VALIDATION_ERROR;

			// get the errors in the validation object
			$validation_msgs = $e->errors($order->table_name());

			// if there are still validation messages, display them
			if ( ! empty($validation_msgs)) {
				Message::message('cl4admin', 'values_not_valid', array(
					':validation_errors' => Message::add_validation_errors($e, 'Model_Cart_Order')
				), Message::$error);
			}
		}

		AJAX_Status::echo_json(AJAX_Status::ajax(array(
			'status' => $ajax_status,
			'message_html' => (string) Message::display(),
			'shipping_display' => (string) $shipping_display,
		)));
	}

	public function action_save_billing() {
		$order = $this->retrieve_order();
		if ( ! is_object($order) || ! $order->loaded()) {
			Message::add('You don\'t have any products in your cart. Please browse our available products before checking out.', Message::$notice);
			AJAX_Status::echo_json(AJAX_Status::ajax(array(
				'status' => AJAX_Status::VALIDATION_ERROR,
				'redirect' => $this->continue_shopping_url,
			)));
			return;
		}

		$ajax_status = AJAX_Status::SUCCESSFUL;
		$billing_display = '';

		try {
			$order->for_user()
				->only_allow_billing()
				->save_values()
				->save();

			$billing_display = View::factory('cart/billing_display')
				->set('billing_contact', Cart::address_html($order->billing_contact_formatted()))
				->set('billing_address', Cart::address_html($order->billing_address_formatted()));
		} catch (ORM_Validation_Exception $e) {
			$ajax_status = AJAX_Status::VALIDATION_ERROR;

			// get the errors in the validation object
			$validation_msgs = $e->errors($order->table_name());

			// if there are still validation messages, display them
			if ( ! empty($validation_msgs)) {
				Message::message('cl4admin', 'values_not_valid', array(
					':validation_errors' => Message::add_validation_errors($e, 'Model_Cart_Order')
				), Message::$error);
			}
		}

		AJAX_Status::echo_json(AJAX_Status::ajax(array(
			'status' => $ajax_status,
			'message_html' => (string) Message::display(),
			'billing_display' => (string) $billing_display,
			'billing_address' => array(
				'first_name' => $order->billing_first_name,
				'last_name' => $order->billing_last_name,
				'address_1' => $order->billing_address_1,
				'address_2' => $order->billing_address_2,
				'city' => $order->billing_city,
				'state' => $order->billing_state_select->name,
				'postal_code' => $order->billing_postal_code,
				'country' => $order->billing_country->name,
			),
		)));
	}

	public function action_save_final() {
		$order = $this->retrieve_order();
		if ( ! is_object($order) || ! $order->loaded()) {
			Message::add('You don\'t have any products in your cart. Please browse our available products before checking out.', Message::$notice);
			AJAX_Status::echo_json(AJAX_Status::ajax(array(
				'status' => AJAX_Status::VALIDATION_ERROR,
				'redirect' => $this->continue_shopping_url,
			)));
			return;
		}

		$ajax_status = AJAX_Status::SUCCESSFUL;
		$final_display = '';

		try {
			$order->for_user()
				->only_allow_final_step()
				->save_values()
				->save();

			$final_display = View::factory('cart/final_display')
				->bind('order', $order);
		} catch (ORM_Validation_Exception $e) {
			$ajax_status = AJAX_Status::VALIDATION_ERROR;

			// get the errors in the validation object
			$validation_msgs = $e->errors($order->table_name());

			// if there are still validation messages, display them
			if ( ! empty($validation_msgs)) {
				Message::message('cl4admin', 'values_not_valid', array(
					':validation_errors' => Message::add_validation_errors($e, 'Model_Cart_Order')
				), Message::$error);
			}
		}

		AJAX_Status::echo_json(AJAX_Status::ajax(array(
			'status' => $ajax_status,
			'message_html' => (string) Message::display(),
			'final_display' => (string) $final_display,
		)));
	}

	public function action_complete_order() {
		$payment_status = NULL;

		$order = $this->retrieve_order();
		if ( ! is_object($order) || ! $order->loaded()) {
			Message::add('You don\'t have any products in your cart. Please browse our available products before checking out.', Message::$notice);
			AJAX_Status::echo_json(AJAX_Status::ajax(array(
				'status' => AJAX_Status::VALIDATION_ERROR,
				'redirect' => $this->continue_shopping_url,
			)));
			return;
		}

		$currency = strtoupper((string) Kohana::$config->load('xm_cart.default_currency'));

		$stripe_config = (array) Kohana::$config->load('xm_cart.payment_processors.stripe.' . STRIPE_CONFIG);
		if (empty($stripe_config['secret_key']) || empty($stripe_config['publishable_key'])) {
			throw new Kohana_Exception('Stripe has not been fully configured');
		}

		if ( ! Kohana::load(Kohana::find_file('vendor', 'stripe/Stripe'))) {
			throw new Kohana_Exception('Unable to load the Stripe libraries');
		}

		$stripe_token = $this->request->post('stripe_token');
		if (empty($stripe_token)) {
			throw new Kohana_Exception('No Stripe token was received');
		}

		/*$order->set('status', CART_ORDER_STATUS_SUBMITTED)
			->is_valid()
			->save();*/
// $order->grand_total = 100;
		try {
			Stripe::setApiKey($stripe_config['secret_key']);
			Stripe::setApiVersion($stripe_config['api_version']);

			// first we want to do an uncaptured charge to verify the credit and address information
			$charge_test = Stripe_Charge::create(array(
				'amount' => $order->grand_total,
				'currency' => $currency,
				'card' => $stripe_token, // obtained with Stripe.js
				'description' => $stripe_config['charge_description'],
				'capture' => FALSE,
			));
			$charge_id = $charge_test->id;

			// if the above didn't fail (throw exception), we want to complete the actual payment
			$charge = Stripe_Charge::retrieve($charge_id);
			$charge->capture();
// Kohana::$log->add(Kohana_Log::DEBUG, print_r($charge, TRUE))->write();

			if ( ! $charge->paid) {
				throw new Kohana_Exception('The credit card was not charged/paid');
			}

			if ($charge->refunded) {
				throw new Kohana_Exception('It was a refund instead of a charge');
			}

			if ( ! $charge->captured) {
				throw new Kohana_Exception('The charge was not captured (completed immediately)');
			}

			if ($order->grand_total != $charge->amount) {
				throw new Kohana_Exception('The amount charged does not match the grand total');
			}

			if ($currency != strtoupper($charge->currency)) {
				throw new Kohana_Exception('The received currency does not match the passed currency');
			}

			$payment_status = 'success';

		} catch(Stripe_CardError $e) {
			// Since it's a decline, Stripe_CardError will be caught
			Kohana::$log->add(Kohana_Log::ERROR, 'Stripe CardError')->write();
			$error_body = $e->getJsonBody();
// Kohana::$log->add(Kohana_Log::DEBUG, print_r($error_body, TRUE))->write();
			$error  = $error_body['error'];
			// error has type, code, param and message keys
			// can also retrieve the HTTP status code: $e->getHttpStatus()

			$payment_status = 'error';

			switch ($error['code']) {
				case 'incorrect_zip' :
					Message::add('The Postal/Zip Code you supplied failed validation. Please verify before trying again.', Message::$error);
					break;
				case 'card_declined' :
					Message::add('Your card was declined. Please check that you\'ve entered it correctly before trying again.', Message::$error);
					break;
				default :
					Message::add($error['message'], Message::$error);
					break;
			}

			switch ($error['code']) {
				case 'incorrect_zip' :
					$error_field = 'billing_postal_code';
					break;
				case 'incorrect_cvc' :
					$error_field = 'security_code';
					break;
				case 'card_declined' :
					$error_field = 'credit_card_number';
					break;
			}

		} catch (Stripe_InvalidRequestError $e) {
			// Invalid parameters were supplied to Stripe's API
			Kohana::$log->add(Kohana_Log::ERROR, 'Invalid parameters were supplied to Stripe\'s API')->write();
			Kohana_Exception::handler_continue($e);

		} catch (Stripe_AuthenticationError $e) {
			// Authentication with Stripe's API failed
			// (maybe you changed API keys recently)
			Kohana::$log->add(Kohana_Log::ERROR, 'Authentication with Stripe\'s API failed')->write();
			Kohana_Exception::handler_continue($e);

		} catch (Stripe_ApiConnectionError $e) {
			// Network communication with Stripe failed
			Kohana::$log->add(Kohana_Log::ERROR, 'Network communication with Stripe failed')->write();
			Kohana_Exception::handler_continue($e);

		} catch (Stripe_Error $e) {
			// Display a very generic error to the user, and maybe send
			// yourself an email
			Kohana::$log->add(Kohana_Log::ERROR, 'General Stripe error')->write();
			Kohana_Exception::handler_continue($e);

		} catch (Kohana_Exception $e) {
			Kohana_Exception::handler_continue($e);

			$payment_status = 'fail';
			Message::add('There was a problem completing the payment. Please contact us to complete your order.', Message::$error);
		}

		AJAX_Status::echo_json(AJAX_Status::ajax(array(
			'message_html' => (string) Message::display(),
			'payment_status' => $payment_status,
			'error_field' => (isset($error_field) ? $error_field : NULL),
		)));
	}

	protected function retrieve_order($create = FALSE) {
		$order_id = Session::instance()->path('xm_cart.cart_order_id');

		// if there is an order in the session, attempt to retrieve it
		// if we can't, unset the $order var and we'll just create a new one
		if ( ! empty($order_id)) {
			$order = ORM::factory('Cart_Order', $order_id);
			if ( ! $order->loaded()) {
				unset($order);
			}

			// only allow access to new orders and those that have been submitted, but not paidec
			if (isset($order) && ! in_array((int) $order->status, array(CART_ORDER_STATUS_NEW, CART_ORDER_STATUS_SUBMITTED, TRUE))) {
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
						->save();
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
				->save();
		}

		if (isset($order) && $order->loaded()) {
			Session::instance()->set_path('xm_cart.cart_order_id', $order->id);

			return $order;
		} else {
			return NULL;
		}
	} // function retrieve_order
}