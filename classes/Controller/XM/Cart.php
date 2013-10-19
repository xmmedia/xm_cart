<?php defined('SYSPATH') or die ('No direct script access.');

class Controller_XM_Cart extends Controller_Public {
	public $no_auto_render_actions = array(
		// other actions
		'load_cart', 'add_product', 'remove_product', 'change_quantity', 'cart_empty', 'set_shipping_country', 'set_shipping_state',
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
		$sub_total = $grand_total = 0;
		$order_product_array = array();
		$taxes = array();
		$show_location_select = FALSE;
		$shipping_country = '';
		$shipping_state = '';
		$shipping_added = FALSE;
		$shipping_display_name = '';
		$shipping_amount = 0;

		$order = $this->retrieve_order();

		if ( ! empty($order) && is_object($order)) {
			$order_products = $order->cart_order_product->find_all();

			$order_product_array = array();
			$deleted_product = FALSE;
			foreach ($order_products as $order_product) {
				if ( ! $order_product->cart_product->loaded()) {
					$order_product->delete();
					$deleted_product = TRUE;
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
			} // foreach

			if ($deleted_product) {
				$order->calculate_totals();
			}

			$shipping = $order->cart_order_shipping->find();
			if ($shipping->loaded()) {
				$shipping_added = TRUE;
				$shipping_display_name = $shipping->display_name;
				$shipping_amount = $shipping->amount;
			}

			foreach ($order->cart_order_tax->find_all() as $tax) {
				$taxes[] = array(
					'name' => $tax->display_name,
					'amount', $tax->amount,
					'amount_formatted' => Cart::cf($tax->amount),
				);
			}

			if (empty($order->shipping_country_id) && Model_Cart_Tax::show_country_select()) {
				$show_location_select = TRUE;
			} else if ( ! empty($order->shipping_country_id) && empty($order->shipping_state_id) && Model_Cart_Tax::show_state_select($order->shipping_country_id)) {
				$show_location_select = TRUE;
			}
			if ( ! $show_location_select) {
				$shipping_country = $order->shipping_country->name;
				$shipping_state = $order->shipping_state_select->name;
			}

			$sub_total = $order->sub_total;
			$grand_total = $order->grand_total;
		}

		AJAX_Status::echo_json(AJAX_Status::ajax(array(
			'products' => $order_product_array,
			'order' => array(
				'show_location_select' => (int) $show_location_select,
				'shipping_country' => $shipping_country,
				'shipping_state' => $shipping_state,

				'shipping' => array(
					'added' => (int) $shipping_added,
					'display_name' => $shipping_display_name,
					'amount' => $shipping_amount,
					'amount_formatted' => Cart::cf($shipping_amount),
				),
				'taxes' => $taxes,
				'sub_total' => $sub_total,
				'sub_total_formatted' => Cart::cf($sub_total),
				'grand_total' => $grand_total,
				'grand_total_formatted' => Cart::cf($grand_total),
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
				$order->add_log('remove_product', array(
						'cart_order_product_id' => $order_product->id,
						'cart_product_id' => $order_product->cart_product_id,
						'unit_price' => $product->cost,
						'name' => $product->name,
					));

				$order_product->delete();
				$order->calculate_totals();
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
			))
			->save();

		$order->calculate_totals()
			->add_log('add_product', array(
				'cart_order_product_id' => $order_product->id,
				'cart_product_id' => $order_product->cart_product_id,
				'quantity' => $order_product->quantity,
				'unit_price' => $product->cost,
				'name' => $product->name,
			));

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
			AJAX_Status::echo_json(AJAX_Status::success());
		}

		// attempt to retrieve the existing product in the cart
		$order_product = ORM::factory('Cart_Order_Product', $cart_order_product_id);
		if ($order_product->loaded()) {
			$order->add_log('remove_product', array(
					'cart_order_product_id' => $order_product->id,
					'cart_product_id' => $order_product->cart_product_id,
				));

			$order_product->delete();
			$order->calculate_totals();
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
		if ( ! $order_product->loaded()) {
			throw new Kohana_Exception('The order product cannot be found');
		}

		if ($quantity == 0) {
			if ($order_product->loaded()) {
				$order->add_log('remove_product', array(
						'cart_order_product_id' => $order_product->id,
						'cart_product_id' => $order_product->cart_product_id,
					));

				$order_product->delete();
				$order->calculate_totals();
			}

			AJAX_Status::echo_json(AJAX_Status::success());
			return;
		}

		// make sure the product still exists (not expired)
		$product = ORM::factory('Cart_Product', $order_product->cart_product_id);
		if ( ! $product->loaded()) {
			// since the product has been expired, also remove the product from order (cart_order_product)
			if ($order_product->loaded()) {
				$order->add_log('remove_product', array(
						'cart_order_product_id' => $order_product->id,
						'cart_product_id' => $order_product->cart_product_id,
						'unit_price' => $product->cost,
						'name' => $product->name,
					));

				$order_product->delete();
				$order->calculate_totals();
			}

			// then throw and error because this is bad!
			throw new Kohana_Exception('The selected product is no longer available');
		}

		// everything seems successful, so save the cart_order_product record
		$order_product->values(array(
				'quantity' => $quantity,
				'unit_price' => $product->cost,
			))->save();

		$order->calculate_totals()
			->add_log('change_quantity', array(
				'cart_order_product_id' => $order_product->id,
				'cart_product_id' => $order_product->cart_product_id,
				'quantity' => $order_product->quantity,
				'unit_price' => $product->cost,
				'name' => $product->name,
			));

		AJAX_Status::echo_json(AJAX_Status::success());
	} // function action_change_quantity

	public function action_cart_empty() {
		$order = $this->retrieve_order();

		if (is_object($order) && $order->loaded()) {
			$order->add_log('empty_cart')
				->delete();
			Session::instance()->set_path('xm_cart.cart_order_id', NULL);
		}

		AJAX_Status::echo_json(AJAX_Status::success());
	}

	public function action_set_shipping_country() {
		$show_state_select = FALSE;
		$states = array();

		// attempt to retrieve the order
		$order = $this->retrieve_order(TRUE);

		$country_id = $this->request->post('country_id');
		if (empty($country_id)) {
			throw new Kohana_Exception('No country was received');
		}

		$country = ORM::factory('Country', $country_id);
		if ( ! $country->loaded()) {
			throw new Kohana_Exception('The country could not be found');
		}

		$order->clear_taxes()
			->values(array(
				'shipping_country_id' => $country_id,
				'shipping_state_id' => 0,
			))
			->save()
			->calculate_totals()
			->add_log('set_shipping_country', array(
				'shipping_country_id' => $country_id,
				'shipping_state_id' => 0,
			));

		$taxes_with_states_for_country = ORM::factory('Cart_Tax')
			->where('country_id', '=', $country_id)
			->where('state_id', '>', 0)
			->where_open()
				->or_where_open()
					->where('start', '<=', DB::expr("NOW()"))
					->where('end', '>=', DB::expr("NOW()"))
				->or_where_close()
				->or_where_open()
					->where('start', '<=', DB::expr("NOW()"))
					->where('end', '=', 0)
				->or_where_close()
				->or_where_open()
					->where('start', '=', 0)
					->where('end', '>=', DB::expr("NOW()"))
				->or_where_close()
				->or_where_open()
					->where('start', '=', 0)
					->where('end', '=', 0)
				->or_where_close()
			->where_close()
			->find_all();
		// if there are taxes for states within the selected country, then we need to display the state/province select for the customer
		if (count($taxes_with_states_for_country) > 0) {
			$show_state_select = TRUE;

			foreach ($country->state->find_all() as $state) {
				$states[] = array('id' => $state->id, 'name' => $state->name);
			}
		} else {
			$order->calculate_totals();
		}

		AJAX_Status::echo_json(AJAX_Status::ajax(array(
			'show_state_select' => (int) $show_state_select,
			'states' => $states,
		)));
	}

	public function action_set_shipping_state() {
		// attempt to retrieve the order
		$order = $this->retrieve_order(TRUE);

		$state_id = $this->request->post('state_id');
		if (empty($state_id)) {
			throw new Kohana_Exception('No state was received');
		}

		$state = ORM::factory('State', $state_id);
		if ( ! $state->loaded()) {
			throw new Kohana_Exception('The state could not be found');
		}
		if ( ! empty($order->shipping_country_id) && $state->country_id != $order->shipping_country_id) {
			$order->set('shipping_country_id', 0)
				->calculate_totals()
				->save()
				->add_log('unset_shipping_country', array(
					'details' => 'The selected shipping state is not in the shipping country.',
				));

			AJAX_Status::echo_json(AJAX_Status::success());
		}

		$order->set('shipping_state_id', $state_id)
			->calculate_totals()
			->save()
			->add_log('set_shipping_state', array(
				'shipping_state_id' => $state_id,
			));


		AJAX_Status::echo_json(AJAX_Status::success());
	}

	// not ajax!!
	public function action_checkout() {
		$order = $this->retrieve_order();
		if ( ! is_object($order) || ! $order->loaded()) {
			Message::add('You don\'t have any products in your cart. Please browse our available products before checking out.', Message::$notice);
			$this->redirect($this->continue_shopping_url);
		}

		$order->calculate_totals()
			->for_user()
			->set_table_columns('same_as_shipping_flag', 'field_type', 'Hidden')
			->add_log('checkout');

		$order_products = $order->cart_order_product->find_all();

		$order_product_array = array();
		foreach ($order_products as $order_product) {
			// make sure the product is still avaialble, otherwise remove it from the order
			if ( ! $order_product->cart_product->loaded()) {
				$order_product->delete();
				continue;
			}

			$order_product_array[] = $order_product;
		} // foreach

		if (empty($order_product_array)) {
			Message::add('You don\'t have any products in your cart. Please browse our available products before checking out.', Message::$notice);
			$this->redirect($this->continue_shopping_url);
		}

		$cart_html = View::factory('cart/cart')
			->bind('order_product_array', $order_product_array)
			// the total rows are sent through JSON and rendered in JS
			->set('total_rows', array());

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
			->set('total_rows', $this->total_rows($order))
			->bind('expiry_date_months', $expiry_date_months)
			->bind('expiry_date_years', $expiry_date_years)
			->set('continue_shopping_url', $this->continue_shopping_url)
			// used in the cart config view
			->set('countries', Cart::countries());
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
				->calculate_totals() // also saves
				->add_log('save_shipping');

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
			'total_rows' => $this->total_rows($order),
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
				->save()
				->add_log('save_billing');

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
			'total_rows' => $this->total_rows($order),
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
				->save()
				->add_log('save_final');

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
			'total_rows' => $this->total_rows($order),
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

		// set the status to submitted
		$order->set_status(CART_ORDER_STATUS_SUBMITTED)
			// calculate the totals just in case
			->calculate_totals()
			->add_log('complete_order');

		$currency = strtoupper((string) Kohana::$config->load('xm_cart.default_currency'));

		$stripe_config = (array) Kohana::$config->load('xm_cart.payment_processor_config.stripe.' . STRIPE_CONFIG);
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

		$stripe_data = array(
			'amount' => $order->grand_total * 100, // charged in cents
			'currency' => $currency,
			'card' => $stripe_token, // obtained with Stripe.js
			'description' => $stripe_config['charge_description'],
			'capture' => FALSE,
		);

		// starting payment, so set as payment
		$order->set_status(CART_ORDER_STATUS_PAYMENT)
			->add_log('processing_payment', array(
				'stripe_data' => $stripe_data,
			));

		$order_payment = ORM::factory('Cart_Order_Payment')
			->values(array(
				'cart_order_id' => $order->id,
				'date_attempted' => Date::formatted_time(),
				'ip_address' => (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : ''),
				'payment_processor' => (int) Kohana::$config->load('xm_cart.payment_processor_ids.stripe'),
				'status' => CART_PAYMENT_STATUS_IN_PROGRESS,
				'amount' => $order->grand_total,
				'data' => $stripe_data,
			))
			->save()
			->add_log(CART_PAYMENT_STATUS_IN_PROGRESS, $stripe_data);

		try {
			Stripe::setApiKey($stripe_config['secret_key']);
			Stripe::setApiVersion($stripe_config['api_version']);

			// first we want to do an uncaptured charge to verify the credit and address information
			$charge_test = Stripe_Charge::create($stripe_data);
			$charge_id = $charge_test->id;

			$order_payment->set('transaction_id', $charge_id)
				->save()
				->add_log(CART_PAYMENT_STATUS_IN_PROGRESS, $charge_test->__toArray(TRUE));

			// if the above didn't fail (throw exception), we want to complete the actual payment
			$charge = Stripe_Charge::retrieve($charge_id);
			$charge->capture();

			$order_payment->add_log(CART_PAYMENT_STATUS_IN_PROGRESS, $charge->__toArray(TRUE));

			if ( ! $charge->paid) {
				throw new Kohana_Exception('The credit card was not charged/paid');
			}

			if ($charge->refunded) {
				throw new Kohana_Exception('It was a refund instead of a charge');
			}

			if ( ! $charge->captured) {
				throw new Kohana_Exception('The charge was not captured (completed immediately)');
			}

			if (($order->grand_total * 100) != $charge->amount) {
				throw new Kohana_Exception('The amount charged does not match the grand total');
			}

			if ($currency != strtoupper($charge->currency)) {
				throw new Kohana_Exception('The received currency does not match the passed currency');
			}

			$order_payment->values(array(
					'date_completed' => Date::formatted_time(),
					'status' => CART_PAYMENT_STATUS_SUCCESSFUL,
					'response' => $charge->__toArray(TRUE),
				))
				->save()
				->add_log(CART_PAYMENT_STATUS_SUCCESSFUL, $charge->__toArray(TRUE));

			$payment_status = 'success';
			$order->set_status(CART_ORDER_STATUS_PAID)
				->add_log('paid', $charge->__toArray(TRUE));

			// send emails
			// first retrieve the necessary data
			$order_products = $order->cart_order_product->find_all();

			$order_product_array = array();
			foreach ($order_products as $order_product) {
				// make sure the product is still avaialble, otherwise remove it from the order
				if ( ! $order_product->cart_product->loaded()) {
					$order_product->delete();
					continue;
				}

				$order_product_array[] = $order_product;
			} // foreach

			$shipping = $order->cart_order_shipping->find();
			if ($shipping->loaded()) {
				$total_rows[] = array(
					'name' => $shipping->display_name,
					'value' => $shipping->amount,
				);
			}

			$total_rows[] = array(
				'name' => 'Sub Total',
				'value' => $order->sub_total,
			);

			foreach ($order->cart_order_tax->find_all() as $tax) {
				$total_rows[] = array(
					'name' => $tax->display_name,
					'value' => $tax->amount,
				);
			}

			$total_rows[] = array(
				'name' => 'Total',
				'value' => $order->grand_total,
				'is_grand_total' => TRUE,
			);

			$paid_with = array(
				'type' => $order_payment->response['card']['type'],
				'last_4' => $order_payment->response['card']['last4'],
			);

			// create the customer email
			$mail = new Mail();
			$mail->AddAddress($order->shipping_email, $order->shipping_first_name . ' ' . $order->shipping_last_name);
			if (UTF8::strtolower($order->shipping_email) != UTF8::strtolower($order->billing_email)) {
				$mail->AddAddress($order->billing_email, $order->billing_first_name . ' ' . $order->billing_last_name);
			}
			$mail->Subject = 'Your order from ' . LONG_NAME;
			$mail->IsHTML(TRUE);
			$email_body_html = View::factory('cart/email/customer_order')
				->bind('order', $order)
				->bind('order_product_array', $order_product_array)
				->bind('total_rows', $total_rows)
				->bind('paid_with', $paid_with);
			$mail->Body = View::factory('cart/email/template')
				->bind('body_html', $email_body_html);
			$mail->Send();

			// create the owner/administrator email
			$administrator_email = Kohana::$config->load('xm_cart.administrator_email');

			$mail = new Mail();
			$mail->AddAddress($administrator_email[0], $administrator_email[1]);
			$mail->Subject = 'Order Received – [invoice]';
			$mail->IsHTML(TRUE);
			$email_body_html = View::factory('cart/email/admin_order')
				->bind('order', $order)
				->bind('order_product_array', $order_product_array)
				->bind('total_rows', $total_rows)
				->bind('paid_with', $paid_with);
			$mail->Body = View::factory('cart/email/template')
				->bind('body_html', $email_body_html);
			$mail->Send();

			Session::instance()->set_path('xm_cart.cart_order_id', NULL);

		} catch(Stripe_CardError $e) {
			// Since it's a decline, Stripe_CardError will be caught
			Kohana::$log->add(Kohana_Log::ERROR, 'Stripe CardError')->write();
			Kohana_Exception::log($e);

			$error_body = $e->getJsonBody();
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

			// set the status back to submitted because there was a problem with the payment
			$order->set_status(CART_ORDER_STATUS_SUBMITTED)
				->add_log('payment_error', $error_body);
			$order_payment->values(array(
					'status' => CART_PAYMENT_STATUS_DENIED,
					'response' => $error_body,
				))
				->save()
				->add_log(CART_PAYMENT_STATUS_DENIED, $error_body);

		} catch (Stripe_InvalidRequestError $e) {
			// Invalid parameters were supplied to Stripe's API
			Kohana::$log->add(Kohana_Log::ERROR, 'Invalid parameters were supplied to Stripe\'s API')->write();
			Kohana_Exception::log($e);
			$payment_status = 'error';
			Message::add('There was a problem processing your payment. Please try again or contact us to complete your payment.', Message::$error);

			// set the status back to submitted because there was a problem with the payment
			$order->set_status(CART_ORDER_STATUS_SUBMITTED)
				->add_log('payment_error', (array) $e->getJsonBody());
			$order_payment->values(array(
					'status' => CART_PAYMENT_STATUS_ERROR,
					'response' => (array) $e->getJsonBody(),
				))
				->save()
				->add_log(CART_PAYMENT_STATUS_ERROR, (array) $e->getJsonBody());

		} catch (Stripe_AuthenticationError $e) {
			// Authentication with Stripe's API failed
			// (maybe you changed API keys recently)
			Kohana::$log->add(Kohana_Log::ERROR, 'Authentication with Stripe\'s API failed')->write();
			Kohana_Exception::log($e);
			$payment_status = 'error';
			Message::add('There was a problem processing your payment. Please try again or contact us to complete your payment.', Message::$error);

			// set the status back to submitted because there was a problem with the payment
			$order->set_status(CART_ORDER_STATUS_SUBMITTED)
				->add_log('payment_error', (array) $e->getJsonBody());
			$order_payment->values(array(
					'status' => CART_PAYMENT_STATUS_ERROR,
					'response' => (array) $e->getJsonBody(),
				))
				->save()
				->add_log(CART_PAYMENT_STATUS_ERROR, (array) $e->getJsonBody());

		} catch (Stripe_ApiConnectionError $e) {
			// Network communication with Stripe failed
			Kohana::$log->add(Kohana_Log::ERROR, 'Network communication with Stripe failed')->write();
			Kohana_Exception::log($e);
			$payment_status = 'error';
			Message::add('There was a problem processing your payment. Please try again or contact us to complete your payment.', Message::$error);

			// set the status back to submitted because there was a problem with the payment
			$order->set_status(CART_ORDER_STATUS_SUBMITTED)
				->add_log('payment_error', (array) $e->getJsonBody());
			$order_payment->values(array(
					'status' => CART_PAYMENT_STATUS_ERROR,
					'response' => (array) $e->getJsonBody(),
				))
				->save()
				->add_log(CART_PAYMENT_STATUS_ERROR, (array) $e->getJsonBody());

		} catch (Stripe_Error $e) {
			// Display a very generic error to the user, and maybe send
			// yourself an email
			Kohana::$log->add(Kohana_Log::ERROR, 'General Stripe error')->write();
			Kohana_Exception::log($e);
			$payment_status = 'error';
			Message::add('There was a problem processing your payment. Please try again or contact us to complete your payment.', Message::$error);

			// set the status back to submitted because there was a problem with the payment
			$order->set_status(CART_ORDER_STATUS_SUBMITTED)
				->add_log('payment_error', (array) $e->getJsonBody());
			$order_payment->values(array(
					'status' => CART_PAYMENT_STATUS_ERROR,
					'response' => (array) $e->getJsonBody(),
				))
				->save()
				->add_log(CART_PAYMENT_STATUS_ERROR, (array) $e->getJsonBody());

		} catch (Kohana_Exception $e) {
			Kohana_Exception::log($e);
			$payment_status = 'fail';
			Message::add('There was a problem processing your payment. Please contact us to complete your payment.', Message::$error);
			// we don't set the order status as there was a problem and it should stay in payment so it can't be attempted again

			$order->add_log('payment_error', (array) $e->getJsonBody());
			$order_payment->values(array(
					'status' => CART_PAYMENT_STATUS_ERROR,
					'response' => (isset($charge) ? $charge->__toArray(TRUE) : (isset($charge_test) ? $charge_test->__toArray(TRUE) : '')),
				))
				->save()
				->add_log(CART_PAYMENT_STATUS_ERROR, (array) $e->getJsonBody());
		}

		AJAX_Status::echo_json(AJAX_Status::ajax(array(
			'message_html' => (string) Message::display(),
			// success: order completed successfully
			// error: allow the user to try again, although we need to a new token
			// fail: there was a major problem and we don't know if the payment was already processed so don't allow them to try again
			'payment_status' => $payment_status,
			'error_field' => (isset($error_field) ? $error_field : NULL),
		)));
	}

	public function action_completed() {
		$this->template->body_html = View::factory('cart/completed');
	}

	public function action_payment_failed() {
		$order_id = Session::instance()->path('xm_cart.cart_order_id');
		if (empty($order_id)) {
			Message::add('You don\'t have any products in your cart. Please browse our available products before checking out.', Message::$notice);
			$this->redirect($this->continue_shopping_url);
		}

		$order = ORM::factory('Cart_Order', $order_id);
		if ( ! $order->loaded()) {
			throw new Kohana_Exception('The order in the session no longer exists');
		}

		$order->add_log('payment_failed');

		if ((int) $order->status != CART_ORDER_STATUS_PAYMENT) {
			if (in_array((int) $order->status, array(CART_ORDER_STATUS_NEW, CART_ORDER_STATUS_SUBMITTED, TRUE))) {
				Message::add('Your order has not been completed. Please checkout before continuing.', Message::$warning);
				$this->redirect($this->continue_shopping_url);
			} else {
				Session::instance()->set_path('xm_cart.cart_order_id', NULL);
				Message::add('Your order has already been completed.', Message::$warning);
				$this->redirect($this->continue_shopping_url);
			}
		}

		$this->template->body_html = View::factory('cart/payment_failed');
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
			Session::instance()->set_path('xm_cart.cart_order_id', $order->id);

			return $order;
		} else {
			return NULL;
		}
	} // function retrieve_order

	protected function total_rows($order) {
		$total_rows = array();

		$shipping = $order->cart_order_shipping->find();
		if ($shipping->loaded()) {
			$total_rows[] = array(
				'name' => $shipping->display_name,
				'value' => $shipping->amount,
				'value_formatted' => Cart::cf($shipping->amount),
			);
		}

		$total_rows[] = array(
			'name' => 'Sub Total',
			'value' => $order->sub_total,
			'value_formatted' => Cart::cf($order->sub_total),
		);

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
}