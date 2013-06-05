<?php defined('SYSPATH') or die ('No direct script access.');

class Controller_XM_Cart extends Controller_Public {
	public $no_auto_render_actions = array('load_products', 'save_product', 'cart_empty');

	public function action_load_products() {
		$order = $this->retrieve_order();

		if ( ! empty($order) && is_object($order)) {
			$order_products = $order->cart_order_product->find_all();

			$order_product_array = array();
			foreach ($order_products as $order_product) {
				if ( ! $order_product->cart_product->loaded()) {
					continue;
				}

				$order_product_array[] = array(
					'id' => $order_product->id,
					'cart_product_id' => $order_product->cart_product_id,
					'quantity' => $order_product->quantity,
					'unit_price' => $order_product->unit_price,
					'name' => $order_product->cart_product->name,
					'cost_formatted' => $order_product->cart_product->cost_formatted(),
				);
			}
		} else {
			$order_product_array = array();
		}

		AJAX_Status::is_json();
		echo json_encode($order_product_array);
	}

	public function action_save_product() {
// Kohana::$log->add(Kohana_Log::DEBUG, print_r($_POST, TRUE))->write();
// Kohana::$log->add(Kohana_Log::DEBUG, print_r($_GET, TRUE))->write();
// Kohana::$log->add(Kohana_Log::DEBUG, print_r($_SERVER, TRUE))->write();

		$method = strtoupper($this->request->post('_method'));

		if ($method != 'DELETE') {
			// all data is in the model key in the post and sent as a json object
			$model = $this->request->post('model');
			if ( ! empty($model)) {
				$model = json_decode($model, TRUE);
			} else {
				throw new Kohana_Exception('No model data was received');
			}

			// retrieve the values out of the model array
			$cart_order_product_id = (int) Arr::get($model, 'id');
			$quantity = (int) Arr::get($model, 'quantity', 1);
			$cart_product_id = (int) Arr::get($model, 'cart_product_id');
			if (empty($cart_product_id)) {
				throw new Kohana_Exception('No cart_product_id was received');
			}

			// attempt to retrieve or create a new order
			$order = $this->retrieve_order(TRUE);

			// attempt to retrieve the existing product in the cart or create an empty object
			$existing_data = array(
				'cart_order_id' => $order->id,
				'cart_product_id' => $cart_product_id,
			);
			if ( ! empty($cart_order_product_id)) {
				$existing_data['id'] = $cart_order_product_id;
			}
			$order_product = ORM::factory('Cart_Order_Product', $existing_data);

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
				'quantity' => $quantity,
				'unit_price' => $product->cost,
			))->save();

			// return the cart_order_product id and unit_price
			AJAX_Status::is_json();
			echo json_encode(array(
				'id' => $order_product->id,
				'cart_product_id' => $product->id,
				'name' => $product->name,
				'quantity' => $order_product->quantity,
				'unit_price' => $order_product->unit_price,
				'cost_formatted' => $product->cost_formatted(),
			));

			return;

		// deleting
		} else {
			// for deletion, the id in a route param
			$cart_order_product_id = $this->request->param('id');
			if (empty($cart_order_product_id)) {
				throw new Kohana_Exception('The cart_order_product_id was not received');
			}

			// attempt to retrieve or create a new order
			$order = $this->retrieve_order(TRUE);

			// attempt to retrieve the existing product in the cart
			$order_product = ORM::factory('Cart_Order_Product', array(
				'id' => $cart_order_product_id,
				'cart_order_id' => $order->id,
			));
			if ($order_product->loaded()) {
				$order_product->delete();
			}

			AJAX_Status::is_json();
			echo json_encode(array());

			return;
		}
	} // function action_save_product

	public function action_cart_empty() {
		$order = $this->retrieve_order();

		if (is_object($order) && $order->loaded()) {
			$order->delete();
			Session::instance()->set_path('xm_cart.cart_order_id', NULL);
		}

		AJAX_Status::is_json();
		echo json_encode(array());
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