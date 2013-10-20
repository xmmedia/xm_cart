<?php defined('SYSPATH') or die ('No direct script access.');

class Controller_XM_Cart_Admin extends Controller_Private {
	public $page = 'cl4admin';

	public function before() {
		parent::before();

		$this->page_title_append = 'Cart Admin - ' . $this->page_title_append;

		if ($this->auto_render) {
			$this->add_style('cart_private', 'xm_cart/css/private.css')
				->add_script('cart_private', 'xm_cart/js/private.min.js');
		}

		$cart_admin_session = (array) Session::instance()->path('xm_cart.cart_admin');
		$cart_admin_session += array(
			'order_filters' => array(
				'status' => implode(',', array(CART_ORDER_STATUS_PAID, CART_ORDER_STATUS_RECEIVED)),
			),
		);
		Session::instance()->set_path('xm_cart.cart_admin', $cart_admin_session);
	}

	/**
	 * List recent orders, show current shipping and tax rates, available discounts.
	 */
	public function action_index() {
		$this->template->page_title = $this->page_title_append;
		$this->template->body_html = View::factory('cart_admin/index');
	}

	public function action_order() {
		$order_filters = (array) Session::instance()->path('xm_cart.cart_admin.order_filters');

		$received_order_filters = (array) $this->request->query('order_filters');
		if ( ! empty($received_order_filters)) {
			$received_order_filters += $order_filters;
			$order_filters = $received_order_filters;
			Session::instance()->set_path('xm_cart.cart_admin.order_filters', $received_order_filters);
		}

		$order_filters_html = array();
		$order_statuses = array(
			implode(',', array(CART_ORDER_STATUS_PAID, CART_ORDER_STATUS_RECEIVED)) => 'Paid & Not Shipped',
			'' => 'All Orders',
			implode(',', array(CART_ORDER_STATUS_NEW, CART_ORDER_STATUS_SUBMITTED, CART_ORDER_STATUS_PAYMENT)) => 'New & Not Paid',
			CART_ORDER_STATUS_NEW       => 'New Order / Unpaid',
			CART_ORDER_STATUS_SUBMITTED => 'Submitted / Waiting for Payment',
			CART_ORDER_STATUS_PAYMENT   => 'Payment in Progress',
			CART_ORDER_STATUS_PAID      => 'Paid',
			CART_ORDER_STATUS_RECEIVED  => 'Received',
			CART_ORDER_STATUS_SHIPPED   => 'Shipped',
			CART_ORDER_STATUS_REFUNDED  => 'Refunded',
			CART_ORDER_STATUS_CANCELLED => 'Cancelled',
		);
		$order_filters_html['status'] = Form::select('order_filters[status]', $order_statuses, $order_filters['status']);

		$order_query = ORM::factory('Cart_Order');
		if ( ! empty($order_filters['status'])) {
			$order_filter_statuses = explode(',', $order_filters['status']);
			$order_query->where('status', 'IN', $order_filter_statuses);
		}
		$orders = $order_query->find_all();

		$order_table = new HTMLTable(array(
			'heading' => array(
				'',
				'Status<br>Last Change',
				'Name',
				'Total',
				'Order #',
			),
		));

		foreach ($orders as $order) {
			$order->set_mode('view');

			$last_log = $order->cart_order_log->find();

			if ($order->shipping_first_name != $order->billing_first_name || $order->shipping_last_name != $order->billing_last_name || $order->shipping_email != $order->billing_email) {
				$name = '<span title="Shipping">'
						. HTML::chars($order->shipping_first_name . ' ' . $order->shipping_last_name) . ' '
						. HTML::mailto($order->shipping_email)
					. '</span><br>'
					. '<span title="Billing">'
						. HTML::chars($order->billing_first_name . ' ' . $order->billing_last_name)  . ' '
						. HTML::mailto($order->shipping_email)
					. '</span>';
			} else {
				$name = HTML::chars($order->shipping_first_name . ' ' . $order->shipping_last_name) . '<br>' . HTML::mailto($order->shipping_email);
			}

			$row = array(
				HTML::anchor(Route::get('cart_admin')->uri(array('action' => 'order_view', 'id' => $order->id)), HTML::icon('view')),
				$order->get_field('status') . '<br>' . $last_log->timestamp,
				$name,
				Cart::cf($order->grand_total),
				HTML::chars($order->order_num),
			);
			$order_table->add_row($row);
		}

		$uri = Route::get('cart_admin')->uri(array('action' => 'order'));

		$this->template->page_title = 'Orders - ' . $this->page_title_append;
		$this->template->body_html = View::factory('cart_admin/order')
			->set('form_open', Form::open($uri, array('method' => 'GET', 'class' => 'cart_form js_cart_order_filter_form')))
			->bind('order_filters_html', $order_filters_html)
			->set('order_html', $order_table->get_html());
	}

	public function action_order_view() {
		$order = ORM::factory('Cart_Order', (int) $this->request->param('id'));
		if ( ! $order->loaded()) {
			Message::add('The order could not be found.', Message::$error);
			$this->redirect($this->order_uri());
		}

		$order->set_mode('view');

		$order_products = $order->cart_order_product->find_all();

		$order_payment = $order->cart_order_payment
			->where('status', '=', CART_PAYMENT_STATUS_SUCCESSFUL)
			->order_by('date_completed', 'DESC')
			->find();
		$paid_with = array(
			'type' => $order_payment->response['card']['type'],
			'last_4' => $order_payment->response['card']['last4'],
		);

		$cart_html = View::factory('cart/cart')
			->bind('order_product_array', $order_products)
			->set('total_rows', Cart::total_rows($order));

		if ($this->auto_render) {
			$this->add_style('cart_public', 'xm_cart/css/public.css')
				/*->add_script('stripe_v2', 'https://js.stripe.com/v2/')
				->add_script('cart_base', 'xm_cart/js/base.min.js')
				->add_script('cart_public', 'xm_cart/js/public.min.js')*/;
		}

		$this->template->page_title = ( ! empty($order->order_num) ? $order->order_num . ' - ' : '') . 'Order View - ' . $this->page_title_append;
		$this->template->body_html = View::factory('cart_admin/order_view')
			->bind('order', $order)
			->bind('cart_html', $cart_html)
			->bind('paid_with', $paid_with);
	}

	public function action_shipping() {
		$shipping_rates = ORM::factory('Cart_Shipping')
			->where_active_dates()
			->find_all();

		$shipping_rate_html = array();
		foreach ($shipping_rates as $shipping_rate) {
			$html = '<strong>' . HTML::chars($shipping_rate->name) . '</strong>'
				. ($shipping_rate->name != $shipping_rate->display_name ? ' (' . HTML::chars($shipping_rate->display_name) . ')' : '')
				. '<br>';
			if ( ! Form::check_date_empty_value($shipping_rate->start)) {
				$html .= 'Starting ' . $shipping_rate->start;
			}
			if ( ! Form::check_date_empty_value($shipping_rate->end)) {
				$html .= ' Ending ' . $shipping_rate->end;
			}

			if ( ! empty($shipping_rate->data['reasons']) && is_array($shipping_rate->data['reasons'])) {
				foreach ($shipping_rate->data['reasons'] as $reason) {
					switch ($reason['reason']) {
						case 'flat_rate' :
							$html .= '<br>Flat Rate';
							break;
					} // switch reasons
				} // foreach reasons
			}

			$html .= '<br>' . Cart::calc_method_display($shipping_rate->calculation_method, $shipping_rate->amount);

			$html .= '<br>' . HTML::anchor(Route::get('cart_admin')->uri(array('action' => 'shipping_edit', 'id' => $shipping_rate->pk())), 'Edit')/* . ' | '
				. HTML::anchor(Route::get('cart_admin')->uri(array('action' => 'shipping_delete', 'id' => $shipping_rate->pk())), 'Delete')*/;

			$shipping_rate_html[] = $html;
		}

		$this->template->page_title = 'Shipping Rates - ' . $this->page_title_append;
		$this->template->body_html = View::factory('cart_admin/shipping')
			->bind('shipping_rate_html', $shipping_rate_html);
	}

	public function action_shipping_edit() {
		$shipping_rate = ORM::factory('Cart_Shipping', (int) $this->request->param('id'));
		if ( ! $shipping_rate->loaded()) {
			Message::add('The shipping rate could not be found.', Message::$error);
			$this->redirect($this->shipping_uri());
		}

		if ( ! empty($_POST)) {
			try {
				$shipping_rate->save_values()
					->set('data', array('reasons' => (array) $this->request->post('reasons')))
					->save();

				Message::add('The shipping rate has been saved.', Message::$notice);
				$this->redirect($this->shipping_uri());

			} catch (ORM_Validation_Exception $e) {
				Message::add('Please fix the following errors: ' . Message::add_validation_errors($e, ''), Message::$error);
			}
		}

		$reasons = array(
			'flat_rate' => 'Flat Rate',
		);

		$uri = Route::get('cart_admin')->uri(array('action' => 'shipping_edit', 'id' => $shipping_rate->pk()));

		$this->template->page_title = 'Shipping Rate Edit - ' . $this->page_title_append;
		$this->template->body_html = View::factory('cart_admin/shipping_edit')
			->set('form_open', Form::open($uri, array('class' => 'cart_form')))
			->set('cancel_uri', URL::site($this->shipping_uri()))
			->bind('shipping_rate', $shipping_rate)
			->bind('reasons', $reasons);
	}



	public function action_tax() {
		$taxes = ORM::factory('Cart_Tax')
			->where_active_dates()
			->find_all();

		$taxes_html = array();
		foreach ($taxes as $tax) {
			$html = '<strong>' . HTML::chars($tax->name) . '</strong>'
				. ($tax->name != $tax->display_name ? ' (' . HTML::chars($tax->display_name) . ')' : '')
				. '<br>';
			if ( ! Form::check_date_empty_value($tax->start)) {
				$html .= 'Starting ' . $tax->start;
			}
			if ( ! Form::check_date_empty_value($tax->end)) {
				$html .= ' Ending ' . $tax->end;
			}

			$html .= '<br>' . Cart::calc_method_display($tax->calculation_method, $tax->amount);

			$html .= '<br>' . HTML::anchor(Route::get('cart_admin')->uri(array('action' => 'tax_edit', 'id' => $tax->pk())), 'Edit')/* . ' | '
				. HTML::anchor(Route::get('cart_admin')->uri(array('action' => 'tax_delete', 'id' => $tax->pk())), 'Delete')*/;

			$taxes_html[] = $html;
		}

		$this->template->page_title = 'Taxes - ' . $this->page_title_append;
		$this->template->body_html = View::factory('cart_admin/tax')
			->bind('taxes_html', $taxes_html);
	}

	public function action_tax_edit() {
		$tax = ORM::factory('Cart_Tax', (int) $this->request->param('id'));
		if ( ! $tax->loaded()) {
			Message::add('The tax could not be found.', Message::$error);
			$this->redirect($this->tax_uri());
		}

		if ( ! empty($_POST)) {
			try {
				$tax->save_values()
					->save();

				Message::add('The tax has been saved.', Message::$notice);
				$this->redirect($this->tax_uri());

			} catch (ORM_Validation_Exception $e) {
				Message::add('Please fix the following errors: ' . Message::add_validation_errors($e, ''), Message::$error);
			}
		}

		$uri = Route::get('cart_admin')->uri(array('action' => 'tax_edit', 'id' => $tax->pk()));

		$this->template->page_title = 'Tax Edit - ' . $this->page_title_append;
		$this->template->body_html = View::factory('cart_admin/tax_edit')
			->set('form_open', Form::open($uri, array('class' => 'cart_form')))
			->set('cancel_uri', URL::site($this->tax_uri()))
			->bind('tax', $tax);
	}

	protected function order_uri() {
		return Route::get('cart_admin')->uri(array('action' => 'order'));
	}

	protected function shipping_uri() {
		return Route::get('cart_admin')->uri(array('action' => 'shipping'));
	}

	protected function tax_uri() {
		return Route::get('cart_admin')->uri(array('action' => 'tax'));
	}
}