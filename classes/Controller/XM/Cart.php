<?php defined('SYSPATH') or die ('No direct script access.');

class Controller_XM_Cart extends Controller_Public {
	public $no_auto_render_actions = array(
		// other actions
		'load_cart', 'add_product', 'remove_product', 'change_quantity', 'cart_empty',
		// checkout actions
		'save_shipping', 'save_billing', 'validate_payment',
	);

	protected $continue_shopping_url;

	public function before() {
		parent::before();

		$this->continue_shopping_url = (string) Kohana::$config->load('xm_cart.continue_shopping_url');

		if ($this->auto_render) {
			$this->add_style('cart_public', 'xm_cart/css/public.css')
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

	public function action_checkout() {
		$order = $this->retrieve_order();
		if ( ! is_object($order) || ! $order->loaded()) {
			Message::add('You don\'t have any products in your cart. Please browse our available products before checking out.', Message::$notice);
			$this->request->redirect($this->continue_shopping_url);
		}

		$order->for_user();

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
			->set('continue_shopping_url', $this->continue_shopping_url)
			->set('cart_prefix', (string) Kohana::$config->load('xm_cart.prefix'));
	} // function action_checkout

	public function action_save_shipping() {
		$order = $this->retrieve_order();
		if ( ! is_object($order) || ! $order->loaded()) {
			Message::add('You don\'t have any products in your cart. Please browse our available products before checking out.', Message::$notice);
			$this->request->redirect($this->continue_shopping_url);
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
			$this->request->redirect($this->continue_shopping_url);
		}

		$ajax_status = AJAX_Status::SUCCESSFUL;
		$billing_display = '';

		try {
			$order->for_user()
				->only_allow_billing()
				->save_values()
				->save();


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
		)));
	}

	public function action_validate_payment() {
		$order = $this->retrieve_order();
		if ( ! is_object($order) || ! $order->loaded()) {
			Message::add('You don\'t have any products in your cart. Please browse our available products before checking out.', Message::$notice);
			$this->request->redirect($this->continue_shopping_url);
		}

		$order->for_user();

		$ajax_status = AJAX_Status::SUCCESSFUL;

		$credit_card = (array) $this->request->post('credit_card');
		$validation_errors = array();

		if ( ! isset($credit_card['number']) || ! Valid::not_empty($credit_card['number']) || ! Valid::luhn($credit_card['number'])) {
			$validation_errors[] = 'Your credit card number does not appear to be valid.';
		}
		if ( ! isset($credit_card['security_code']) || ! Valid::min_length($credit_card['security_code'], 3)) {
			$validation_errors[] = 'The Security Code doesn\'t appear to be valid.';
		}
		if ( ! isset($credit_card['expiry_date']['year']) || ! isset($credit_card['expiry_date']['month']) || ! ($credit_card['expiry_date']['year'] > date('Y') || ($credit_card['expiry_date']['year'] == date('Y') && intval($credit_card['expiry_date']['month']) >= date('n')))) {
			$validation_errors[] = 'Your credit card appears to have already expired. Please verify the Expiry Date.';
		}

		$message_html = '';
		if ( ! empty($validation_errors)) {
			$message_html .= '<ul class="cl4_message"><li class="error"><ul class="cl4_message_validation">';
			foreach ($validation_errors as $validation_error) {
				$message_html .= '<li>' . HTML::chars($validation_error) . '</li>';
			}
			$message_html .= '</ul></li></ul>';

			$ajax_status = AJAX_Status::VALIDATION_ERROR;
			$billing_display = '';
		} else {
			$billing_display = View::factory('cart/billing_display')
				->set('billing_contact', Cart::address_html($order->billing_contact_formatted()))
				->set('billing_address', Cart::address_html($order->billing_address_formatted()))
				->set('cc_end', HTML::chars(substr($credit_card['number'], -4, 4)));
		}

		AJAX_Status::echo_json(AJAX_Status::ajax(array(
			'status' => $ajax_status,
			'message_html' => $message_html,
			'billing_display' => (string) $billing_display,
		)));
	}

	public function action_complete() {

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