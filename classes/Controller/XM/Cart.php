<?php defined('SYSPATH') or die ('No direct script access.');

class Controller_XM_Cart extends Controller_Public {
	public $no_auto_render_actions = array('load_products', 'save_products');

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
				);
			}
		} else {
			$order_product_array = array();
		}

		AJAX_Status::is_json();
		echo json_encode($order_product_array);
	}

	public function action_save_product() {
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
			'unit_price' => $product->cost
		))->save();

		// return the cart_order_product id and unit_price
		AJAX_Status::is_json();
		echo json_encode(array(
			'id' => $order_product->id,
			'unit_price' => $order_product->unit_price,
			'name' => $product->name,
		));
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
		}

		// no order found, just create a new one
		if ( ! isset($order) && $create) {
			$order = ORM::factory('Cart_Order')
				->save();
		}

		if (isset($order) && $order->loaded()) {
			Session::instance()->set_path('xm_cart.cart_order_id', $order->id);

			return $order;
		} else {
			return NULL;
		}
	}
}