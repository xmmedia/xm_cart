<?php defined('SYSPATH') or die ('No direct script access.');

/**
 *
 *
 * @package    XM Cart
 * @category   Controllers
 * @author     XM Media Inc.
 * @copyright  (c) 2014 XM Media Inc.
 */
class Controller_XM_Cart extends Controller_Public {
	public $no_auto_render_actions = array(
		// other actions
		'load_summary', 'load_cart', 'add_product', 'remove_product', 'change_quantity', 'cart_empty', 'set_shipping_country', 'set_shipping_state',
		// checkout actions
		'save_shipping', 'save_billing', 'validate_payment', 'save_final', 'complete_order',
		// product photo
		'product_photo',
	);

	public function before() {
		// loading the necessary cart functionality & checks to make sure it's setup correctly
		Cart::load();

		parent::before();

		if ($this->auto_render) {
			$this->add_style('cart_public', 'xm_cart/css/public.css')
				->add_script('stripe_v2', 'https://js.stripe.com/v2/')
				->add_script('cart_base', 'xm_cart/js/base.min.js')
				->add_script('cart_public', 'xm_cart/js/public.min.js');
		}
	}

	public function action_load_summary() {
		$product_count = 0;
		$donation_cart = FALSE;
		$total = 0;
		$total_formatted = '$0.00';

		$order = Cart::retrieve_user_order();

		if ( ! empty($order) && is_object($order)) {
			$product_count = count($order->cart_order_product->find_all());
			$total = $order->grand_total;
			$total_formatted = Cart::cf($order->grand_total);
			$donation_cart = (Cart_Config::donation_cart() && $order->donation_cart_flag);
		}

		AJAX_Status::echo_json(AJAX_Status::ajax(array(
			'product_count' => $product_count,
			'order' => array(
				'donation_cart' => (bool) $donation_cart,
			),
			'total' => floatval($total),
			'total_formatted' => $total_formatted,
		)));
	}

	public function action_load_cart() {
		$order_product_array = array();
		$show_location_select = FALSE;
		$shipping_country = '';
		$shipping_state = '';
		$donation_cart = FALSE;
		$total_rows = array();

		$order = Cart::retrieve_user_order();

		if ( ! empty($order) && is_object($order)) {
			$order_products = $order->cart_order_product->find_all();

			$deleted_product = FALSE;
			foreach ($order_products as $order_product) {
				if ( ! $order_product->cart_product->loaded()) {
					$order->add_log('cleaned_product', array(
							'cart_order_product_id' => $order_product->id,
							'cart_product_id' => $order_product->cart_product_id,
						));

					$order_product->delete();
					$deleted_product = TRUE;
					continue;
				}

				$amount = $order_product->unit_price * $order_product->quantity;

				$order_product_array[] = array(
					'id' => (int) $order_product->id,
					'cart_product_id' => (int) $order_product->cart_product_id,
					'part_number' => $order_product->cart_product->part_number,
					'name' => $order_product->cart_product->name(),
					'description' => $order_product->cart_product->description,
					'photo_uri' => URL::site($order_product->cart_product->photo_uri()),
					'view_url' => URL::site($order_product->cart_product->view_uri()),
					'quantity' => (int) $order_product->quantity,
					'inventory_available' => (int) $order_product->cart_product->inventory_available,
					'unit_price' => floatval($order_product->unit_price),
					'unit_price_formatted' => Cart::cf($order_product->unit_price),
					'amount' => $amount,
					'amount_formatted' => Cart::cf($amount),
				);
			} // foreach

			if ($deleted_product) {
				$order->calculate_totals();
			}

			// either shipping or tax functionality needs to be enabled to show the location select
			if (Cart_Config::enable_shipping() || Cart_Config::enable_tax()) {
				if (empty($order->shipping_country_id) && Model_Cart_Tax::show_country_select()) {
					$show_location_select = TRUE;
				} else if ( ! empty($order->shipping_country_id) && empty($order->shipping_state_id) && Model_Cart_Tax::show_state_select($order->shipping_country_id)) {
					$show_location_select = TRUE;
				}
			}
			if ( ! $show_location_select && Cart_Config::enable_shipping()) {
				$shipping_country = $order->shipping_country->name;
				$shipping_state = $order->shipping_state_select->name;
			}

			$donation_cart = (Cart_Config::donation_cart() && $order->donation_cart_flag);

			$total_rows = Cart::total_rows($order);
		}

		AJAX_Status::echo_json(AJAX_Status::ajax(array(
			'products' => $order_product_array,
			'order' => array(
				'show_location_select' => (int) $show_location_select,
				'shipping_country' => $shipping_country,
				'shipping_state' => $shipping_state,
				'donation_cart' => $donation_cart,
			),
			'total_rows' => $total_rows,
		)));
	}

	// not ajax!!
	public function action_view() {
		$this->template->page_title = Cart::message('page_titles.cart_view') . $this->page_title_append;
		$this->template->body_html = View::factory('cart/view')
			->set('countries', Cart::countries());
	}

	public function action_add_product() {
		// retrieve the values out of the model array
		$cart_product_id = (int) $this->request->post('cart_product_id');
		$quantity = (int) $this->request->post('quantity');

		if (empty($cart_product_id)) {
			throw new Kohana_Exception('No cart_product_id was received');
		}

		// don't allow adding products with quantity 0
		if ($quantity < 1) {
			$quantity = 1;
		}

		// attempt to retrieve or create a new order
		$order = Cart::retrieve_user_order(TRUE);

		// if the order is a donation and the product being added is not
		// then change the order to a regular order & add the product
		if ($order->donation_cart_flag && Cart_Config::load('donation_product_id') != $cart_product_id) {
			$order->set('donation_cart_flag', 0)
				->is_valid()
				->save()
				->reload();
		}

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
				$order->add_log('cleaned_product', array(
						'cart_order_product_id' => $order_product->id,
						'cart_product_id' => $order_product->cart_product_id,
					));

				$order_product->delete();
				$order->calculate_totals();
			}

			// then throw and error because this is bad!
			throw new Kohana_Exception('The selected product is no longer available');
		}

		$quantity = ($order_product->loaded() ? $order_product->quantity + $quantity : $quantity);
		$order->add_product($product, $quantity, NULL, $order_product);

		AJAX_Status::echo_json(AJAX_Status::success());
	} // function action_add_product

	public function action_remove_product() {
		// for deletion, the id in a route param
		$cart_order_product_id = (int) $this->request->post('cart_order_product_id');
		if (empty($cart_order_product_id)) {
			throw new Kohana_Exception('The cart_order_product_id was not received');
		}

		// attempt to retrieve the order
		$order = Cart::retrieve_user_order(FALSE);
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
		$order = Cart::retrieve_user_order(TRUE);

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
				$order->add_log('cleaned_product', array(
						'cart_order_product_id' => $order_product->id,
						'cart_product_id' => $order_product->cart_product_id,
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
				'part_number' => $product->part_number,
				'name' => $product->name,
			));

		AJAX_Status::echo_json(AJAX_Status::success());
	} // function action_change_quantity

	public function action_cart_empty() {
		$order = Cart::retrieve_user_order();

		if (is_object($order) && $order->loaded()) {
			Cart::empty_cart($order);
		}

		$is_ajax = (bool) Arr::get($_REQUEST, 'c_ajax', FALSE);
		if ($is_ajax) {
			AJAX_Status::echo_json(AJAX_Status::success());
		} else {
			$this->redirect(Cart_Config::continue_shopping_url());
		}
	}

	public function action_set_shipping_country() {
		if ( ! Cart_Config::enable_shipping()) {
			AJAX_Status::echo_json(AJAX_Status::success());
			return;
		}

		$show_state_select = FALSE;
		$states = array();

		// attempt to retrieve the order
		$order = Cart::retrieve_user_order(TRUE);

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
		if ( ! Cart_Config::enable_shipping()) {
			AJAX_Status::echo_json(AJAX_Status::success());
			return;
		}

		// attempt to retrieve the order
		$order = Cart::retrieve_user_order(TRUE);

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

	/**
	 * Displays a login form, register link/button & guest checkout link/button.
	 * If the functionality is disabled or they are already logged in, it will redirect to the checkout page.
	 *
	 * [!!] Not AJAX.
	 *
	 * @return  void
	 */
	public function action_login() {
		if ( ! Cart_Config::load('offer_login') || Auth::instance()->logged_in()) {
			$this->redirect(Route::get('cart_public')->uri(array('action' => 'checkout')));
		}

		$this->template->page_title = Cart::message('page_titles.login') . $this->page_title_append;
		$this->template->body_html = View::factory('cart/login')
			// used both for the login redirect & continue as guest "link"
			->set('checkout_uri', Route::get('cart_public')->uri(array('action' => 'checkout')))
			->set('login_uri', Route::get('login')->uri())
			->set('forgot_password_uri', Route::get('login')->uri(array('action' => 'forgot')))
			->set('register_uri', Cart_Config::load('register_uri'))
			->set('cart_view_url', Cart_Config::cart_view_url());
	}

	// not ajax!!
	public function action_checkout() {
		$order = Cart::retrieve_user_order();
		if ( ! is_object($order) || ! $order->loaded()) {
			Message::add(Cart::message('empty_cart'), Message::$notice);
			$this->redirect(Cart_Config::continue_shopping_url());
		}

		if ( ! Cart::allow_order_edit($order)) {
			Message::add(Cart::message('checkout_already_processing'), Message::$error);
			$this->redirect(Cart_Config::continue_shopping_url());
		}

		$this->checkout_https();

		// run any additional custom verification of the order before checking out
		$order = Cart::pre_checkout($order);

		// offers the customer the opportunity to login
		$this->offer_login();

		// will optionally load the address from the user's account
		$order = Cart::load_user_address($order);

		$order->calculate_totals()
			->for_user()
			->set_table_columns('same_as_shipping_flag', 'field_type', 'Hidden')
			->add_log('checkout');

		$order_product_array = $order->cart_order_product
			->find_all()
			->as_array();

		if (empty($order_product_array)) {
			Message::add(Cart::message('empty_cart'), Message::$notice);
			$this->redirect(Cart_Config::continue_shopping_url());
		}

		$is_donation_cart = (Cart_Config::donation_cart() && $order->donation_cart_flag);

		$cart_view = ($is_donation_cart ? 'cart/cart_donation' : 'cart/cart');
		$cart_html = View::factory($cart_view)
			->bind('order_product_array', $order_product_array)
			// the total rows are sent through JSON and rendered in JS
			->set('total_rows', array());

		$show_shipping_country = (bool) Cart_Config::load('show_shipping_country');
		$show_billing_country = (bool) Cart_Config::load('show_billing_country');
		if ( ! $show_shipping_country) {
			$shipping_country_name = ORM::factory('Country', Cart_Config::load('default_country_id'))
				->name;
		}
		if ( ! $show_billing_country) {
			$billing_country_name = ORM::factory('Country', Cart_Config::load('default_country_id'))
				->name;
		}

		$expiry_date_months = Cart::expiry_months();
		$expiry_date_years = Cart::expiry_years();

		if (KOHANA_ENVIRONMENT > Kohana::PRODUCTION) {
			$card_testing_select = Cart_Testing::card_testing_select();
		}

		$this->template->page_title = Cart::message('page_titles.checkout') . $this->page_title_append;
		$this->template->body_html = View::factory('cart/checkout')
			->bind('order', $order)
			->bind('cart_html', $cart_html)
			->set('total_rows', Cart::total_rows($order))
			->bind('expiry_date_months', $expiry_date_months)
			->bind('expiry_date_years', $expiry_date_years)
			->set('cart_view_url', Cart_Config::cart_view_url())
			->set('continue_shopping_url', Cart_Config::continue_shopping_url())
			->set('show_checkout_back_to_cart', Cart_Config::load('show_checkout_back_to_cart'))
			->set('enable_shipping', Cart_Config::enable_shipping())
			->set('show_shipping_country', $show_shipping_country)
			->bind('shipping_country_name', $shipping_country_name)
			->set('show_billing_country', $show_billing_country)
			->bind('billing_country_name', $billing_country_name)
			->set('show_billing_company', (bool) Cart_Config::load('show_billing_company'))
			->set('enable_tax', Cart_Config::enable_tax())
			->set('donation_cart', $is_donation_cart)
			->bind('card_testing_select', $card_testing_select)
			// used in the cart config view
			->set('countries', Cart::countries());
	} // function action_checkout

	public function action_save_shipping() {
		if ( ! Cart_Config::enable_shipping()) {
			AJAX_Status::echo_json(AJAX_Status::success());
			return;
		}

		$order = $this->check_valid_order();
		if ( ! $order) {
			return;
		}

		$ajax_status = AJAX_Status::SUCCESSFUL;
		$shipping_display = '';

		try {
			$order->for_user()
				->only_allow_shipping()
				->save_values();

			if ( ! Cart_Config::load('show_shipping_country')) {
				$order->shipping_country_id = Cart_Config::load('default_country_id');
			}

			$order->save()
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
				Message::message('xm_db_admin', 'values_not_valid', array(
					':validation_errors' => Message::add_validation_errors($e, 'Model_Cart_Order')
				), Message::$error);
			}
		}

		AJAX_Status::echo_json(AJAX_Status::ajax(array(
			'status' => $ajax_status,
			'message_html' => (string) Message::display(),
			'shipping_display' => (string) $shipping_display,
			'total_rows' => Cart::total_rows($order),
		)));
	}

	public function action_save_billing() {
		$order = $this->check_valid_order();
		if ( ! $order) {
			return;
		}

		$ajax_status = AJAX_Status::SUCCESSFUL;
		$billing_display = '';

		try {
			$order->for_user()
				->only_allow_billing()
				->save_values();

			if ( ! Cart_Config::load('show_billing_country')) {
				$order->billing_country_id = Cart_Config::load('default_country_id');
			}

			$order->save()
				->calculate_totals() // also saves
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
				Message::message('xm_db_admin', 'values_not_valid', array(
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
				'municipality' => $order->billing_municipality,
				'state' => $order->billing_state_select->name,
				'postal_code' => $order->billing_postal_code,
				'country' => $order->billing_country->name,
			),
			'total_rows' => Cart::total_rows($order),
		)));
	}

	public function action_save_final() {
		$order = $this->check_valid_order();
		if ( ! $order) {
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
				Message::message('xm_db_admin', 'values_not_valid', array(
					':validation_errors' => Message::add_validation_errors($e, 'Model_Cart_Order')
				), Message::$error);
			}
		}

		AJAX_Status::echo_json(AJAX_Status::ajax(array(
			'status' => $ajax_status,
			'message_html' => (string) Message::display(),
			'final_display' => (string) $final_display,
			'total_rows' => Cart::total_rows($order),
		)));
	}

	public function action_complete_order() {
		$payment_status = NULL;

		$order = $this->check_valid_order();
		if ( ! $order) {
			return;
		}

		$this->checkout_https();

		// set the status to submitted
		$order->set_status(CART_ORDER_STATUS_SUBMITTED)
			->generate_order_num()
			// calculate the totals just in case
			->calculate_totals()
			->add_log('complete_order');

		$currency = strtoupper((string) Cart_Config::load('default_currency'));

		// load and configure stripe
		$stripe_config = Cart::load_stripe();

		$stripe_token = $this->request->post('stripe_token');
		if (empty($stripe_token)) {
			throw new Kohana_Exception('No Stripe token was received');
		}

		$stripe_data = array(
			'amount' => Cart::total_cents($order->grand_total), // charged in cents, can't have decimals
			'currency' => $currency,
			'card' => $stripe_token, // obtained with Stripe.js
			'description' => $order->stripe_charge_description(),
			'capture' => FALSE,
		);

		// starting payment, so set as payment
		$order->set_status(CART_ORDER_STATUS_PAYMENT)
			->add_log('processing_payment', array(
				'stripe_data' => $stripe_data,
			));

		$order_payment = ORM::factory('Cart_Order_Transaction')
			->values(array(
				'cart_order_id' => $order->id,
				'date_attempted' => Date::formatted_time(),
				'user_id' => (Auth::instance()->logged_in() ? Auth::instance()->get_user()->pk() : 0),
				'ip_address' => Arr::get($_SERVER, 'REMOTE_ADDR'),
				'payment_processor' => (int) Cart_Config::load('payment_processor_ids.stripe'),
				'type' => CART_TRANSACTION_TYPE_CHARGE,
				'status' => CART_TRANSACTION_STATUS_IN_PROGRESS,
				'amount' => $order->grand_total,
				'data' => $stripe_data,
			))
			->save()
			->add_log(CART_TRANSACTION_STATUS_IN_PROGRESS, $stripe_data);

		try {
			// first we want to do an uncaptured charge to verify the credit and address information
			$charge_test = Stripe_Charge::create($stripe_data);
			$charge_id = $charge_test->id;

			$order_payment->set('transaction_id', $charge_id)
				->save()
				->add_log(CART_TRANSACTION_STATUS_IN_PROGRESS, $charge_test->__toArray(TRUE));

			// if the above didn't fail (throw exception), we want to complete the actual payment
			$charge = Stripe_Charge::retrieve($charge_id);
			$charge->capture();

			$order_payment->add_log(CART_TRANSACTION_STATUS_IN_PROGRESS, $charge->__toArray(TRUE));

			if ( ! $charge->paid) {
				throw new Kohana_Exception('The credit card was not charged/paid');
			}

			if ($charge->refunded) {
				throw new Kohana_Exception('It was a refund instead of a charge');
			}

			if ( ! $charge->captured) {
				throw new Kohana_Exception('The charge was not captured (completed immediately)');
			}

			if (Cart::total_cents($order->grand_total) != $charge->amount) {
				throw new Kohana_Exception('The amount charged does not match the grand total');
			}

			if ($currency != strtoupper($charge->currency)) {
				throw new Kohana_Exception('The received currency does not match the passed currency');
			}

			$order_payment->values(array(
					'date_completed' => Date::formatted_time(),
					'status' => CART_TRANSACTION_STATUS_SUCCESSFUL,
					'response' => $charge->__toArray(TRUE),
				))
				->save()
				->add_log(CART_TRANSACTION_STATUS_SUCCESSFUL, $charge->__toArray(TRUE));

			$payment_status = 'success';
			$order->set_status(CART_ORDER_STATUS_PAID)
				->add_log('paid', $charge->__toArray(TRUE));

			// sends emails and any additional processing
			Cart::complete_order($order, $order_payment);
		} catch (Exception $e) {
			try {
				$payment_status = 'error';
				$error_data = Cart::handle_stripe_exception($e, $order, $order_payment);

				if ($error_data['type'] == 'card_error') {
					switch ($error_data['code']) {
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
				}
			} catch (Exception $e) {
				Kohana_Exception::log($e);
				$payment_status = 'fail';
				Message::add(Cart::message('fail'), Message::$error);
			}
		}

		AJAX_Status::echo_json(AJAX_Status::ajax(array(
			'message_html' => (string) Message::display(),
			// success: order completed successfully
			// error: allow the user to try again, although we need to a new token
			// fail: there was a major problem and we don't know if the payment was already processed so don't allow them to try again
			'payment_status' => $payment_status,
			'error_field' => (isset($error_field) ? $error_field : NULL),
			'is_donation_cart' => (Cart_Config::donation_cart() && $order->donation_cart_flag),
		)));
	}

	public function action_completed() {
		$is_donation_cart = (bool) $this->request->query('is_donation_cart');
		$offer_registration = ( ! Auth::instance()->logged_in() &&Cart_Config::load('offer_register_after_completed'));

		$this->template->page_title = Cart::message('page_titles.checkout') . $this->page_title_append;
		$this->template->body_html = View::factory((Cart_Config::donation_cart() && $is_donation_cart ? 'cart/completed_donation' : 'cart/completed'))
			// used in the cart config view
			->set('countries', Cart::countries())
			->bind('offer_registration', $offer_registration)
			->set('register_uri', Cart_Config::load('register_uri'));
	}

	public function action_payment_failed() {
		$unique_id = Cart::user_cookie_value_retrieve();
		if (empty($unique_id)) {
			Message::add(Cart::message('empty_cart'), Message::$notice);
			$this->redirect(Cart_Config::continue_shopping_url());
		}

		$order = Cart::load_order($unique_id);
		if ( ! $order->loaded()) {
			throw new Kohana_Exception('The order in the cookie no longer exists');
		}

		$this->checkout_https();

		$order->add_log('payment_failed');

		if ((int) $order->status != CART_ORDER_STATUS_PAYMENT) {
			if (in_array((int) $order->status, array(CART_ORDER_STATUS_NEW, CART_ORDER_STATUS_SUBMITTED, TRUE))) {
				Message::add(Cart::message('please_checkout'), Message::$warning);
				$this->redirect(Cart_Config::continue_shopping_url());
			} else {
				Message::add(Cart::message('already_completed'), Message::$warning);
				$this->redirect(Cart_Config::continue_shopping_url());
			}
		}

		$this->template->body_html = View::factory('cart/payment_failed');
	}

	/**
	 * Displays the order using the unique link in the query.
	 * The order must be paid, received or shipped to be displayed.
	 * If the order is refunded or cancelled, the message `view_order.refunded` or `view_order.cancelled` will be displayed.
	 * If the order cannot be found, the message `view_order.not_found` will be displayed.
	 * If the order is any other status, the message `view_order.cant_view` will be displayed.
	 * If the user is not logged in (doesn't matter if their user ID matches the one on the order),
	 * they will only see the order status and order number.
	 * If logged in, they will see all the shipping and billing details.
	 *
	 * @return  void
	 */
	public function action_view_order() {
		$order_key = urldecode($this->request->query('order'));
		if (empty($order_key)) {
			$this->redirect(Cart_Config::continue_shopping_url());
		}

		// default page title
		$this->template->page_title = Cart::message('page_titles.view_order_none') . $this->page_title_append;

		$order = Cart::order_key_decrypt($order_key);
		if ( ! $order->loaded()) {
			$this->template->body_html = View::factory('cart/view_order_error')
				->set('msg', Cart::message('view_order.not_found'));
			return;
		}

		$donation_cart = (Cart_Config::donation_cart() && $order->donation_cart_flag);

		if ( ! Cart::allow_order_view($order)) {
			switch ($order->status) {
				case CART_ORDER_STATUS_REFUNDED :
					$msg = Cart::message('view_order.refunded');
					break;
				case CART_ORDER_STATUS_CANCELLED :
					$msg = Cart::message('view_order.cancelled');
					break;
				default :
					$msg = Cart::message('view_order.cant_view');
					break;
			}

			$this->template->body_html = View::factory('cart/view_order_error')
				->bind('msg', $msg);
			return;
		}

		$order->set_mode('view');
		$order_products = $order->cart_order_product->find_all();

		$show_private_info = Auth::instance()->logged_in();

		if ($show_private_info) {
			$payment_transaction = $order->payment();
			$paid_with = array(
				'type' => $payment_transaction->response['card']['type'],
				'last_4' => $payment_transaction->response['card']['last4'],
			);
		}

		$cart_view = ($donation_cart ? 'cart/cart_donation' : 'cart/cart');
		$cart_html = View::factory($cart_view)
			->bind('order_product_array', $order_products)
			->set('total_rows', Cart::total_rows($order));

		$this->template->page_title = Cart::message('page_titles.view_order', array(':order_num' => $order->order_num)) . $this->page_title_append;
		$this->template->body_html = View::factory('cart/view_order')
			->bind('order', $order)
			->bind('show_private_info', $show_private_info)
			->bind('cart_html', $cart_html)
			->bind('paid_with', $paid_with)
			->bind('donation_cart', $donation_cart);
	}

	/**
	 * Sends a resized photo (thumbnail) of the product.
	 *
	 * @return  void
	 */
	public function action_product_photo() {
		$product = ORM::factory('Cart_Product', $this->request->param('id'));
		if ( ! $product->loaded()) {
			throw new HTTP_Exception_404('The product photo could not be found');
		}

		$dir_path = Cart_Config::load('product_photo_scaled_path');
		$resize_image_path = $dir_path . pathinfo($product->photo_filename, PATHINFO_FILENAME) . '.jpg';

		if ( ! file_exists($resize_image_path)) {
			if ( ! is_dir($dir_path)) {
				mkdir($dir_path, 02777, TRUE);

				// Set permissions (must be manually set to fix umask issues)
				chmod($dir_path, 02777);
			}

			if ( ! file_exists($product->get_filename_with_path('photo_filename'))) {
				throw new Kohana_Exception('The original gallery photo is missing: :image', array(':image' => $product->get_filename_with_path('photo_filename')));
			} else {
				$image = Image::factory($product->get_filename_with_path('photo_filename'))
					->background('#fff');

				$resize = array(40, 35, Image::AUTO);

				// if both the width and the height are less than the resized one, don't resize it and just use the original
				if ($image->width <= $resize[0] && $image->height <= $resize[1]) {
					copy($image->file, $resize_image_path);
				} else {
					$image->resize($resize[0], $resize[1], $resize[2])
						->save($resize_image_path);
				}
			}
		} // if

		$this->response->send_file($resize_image_path, 'product_photo-' . $product->pk() . '.jpg', array('inline' => TRUE));
	}

	protected function check_valid_order() {
		$order = Cart::retrieve_user_order();
		if ( ! is_object($order) || ! $order->loaded()) {
			Message::add(Cart::message('empty_cart'), Message::$notice);
			AJAX_Status::echo_json(AJAX_Status::ajax(array(
				'status' => AJAX_Status::VALIDATION_ERROR,
				'redirect' => Cart_Config::continue_shopping_url(),
			)));
			return;
		}

		if ( ! Cart::allow_order_edit($order)) {
			Message::add(Cart::message('already_processing'), Message::$error);
			AJAX_Status::echo_json(AJAX_Status::ajax(array(
				'status' => AJAX_Status::VALIDATION_ERROR,
				'redirect' => Cart_Config::continue_shopping_url(),
			)));
			return;
		}

		return $order;
	}

	/**
	 * Redirect the user to https if checkout https is enabled
	 * and they are not already on https.
	 *
	 * @return  void
	 */
	protected function checkout_https() {
		// redirect to https if they are not already
		if (Cart_Config::load('checkout_https') && ! $this->request->secure()) {
			$this->redirect('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
		}
	}

	/**
	 * Determines if they should be offered the opportunity to login, register, or do a guest checkout.
	 * Based on if it's enabled and if they aren't already logged in.
	 * Also won't occur if they've already selected "checkout as guest".
	 * Redirects the user to the cart login page.
	 *
	 * @return  void
	 */
	protected function offer_login() {
		if ( ! $this->request->post('continue_as_guest') && Cart_Config::load('offer_login') && ! Auth::instance()->logged_in()) {
			$this->redirect(Route::get('cart_public')->uri(array('action' => 'login')));
		}
	}
}