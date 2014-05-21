<?php defined('SYSPATH') or die ('No direct script access.');

/**
 *
 *
 * @package    XM Cart
 * @category   Controllers
 * @author     XM Media Inc.
 * @copyright  (c) 2014 XM Media Inc.
 */
class Controller_XM_Cart_Admin_Order extends Controller_Cart_Admin {
	public $page = 'cart_admin';

	public $secure_actions = array(
		'index' => 'cart/admin/order',
		'view' => 'cart/admin/order',
	);

	public function before() {
		parent::before();

		$this->page_title_append = 'Orders - ' . $this->page_title_append;

		if ($this->auto_render) {
			$this->add_script('cart_admin_order', 'xm_cart/js/admin/order.min.js');
		}

		$cart_admin_session = (array) Session::instance()->path('xm_cart.cart_admin');
		$cart_admin_session += array(
			'order_filters' => array(
				'status' => implode(',', array(CART_ORDER_STATUS_PAID, CART_ORDER_STATUS_RECEIVED)),
			),
		);
		Session::instance()->set_path('xm_cart.cart_admin', $cart_admin_session);
	}

	public function action_index() {
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
			CART_ORDER_STATUS_EMPTIED   => 'Emptied',
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
				HTML::anchor(Route::get('cart_admin_order')->uri(array('action' => 'view', 'id' => $order->id)), HTML::icon('search')),
				$order->get_field('status') . '<br>' . $last_log->timestamp,
				$name,
				Cart::cf($order->grand_total),
				HTML::chars($order->order_num),
			);
			$order_table->add_row($row);
		}

		$uri = Route::get('cart_admin_order')->uri();

		$this->template->page_title = 'Orders - ' . $this->page_title_append;
		$this->template->body_html = View::factory('cart_admin/order/index')
			->set('form_open', Form::open($uri, array('method' => 'GET', 'class' => 'cart_form js_cart_order_filter_form')))
			->bind('order_filters_html', $order_filters_html)
			->set('order_html', $order_table->get_html());
	}

	public function action_view() {
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
		$this->template->body_html = View::factory('cart_admin/order/view')
			->bind('order', $order)
			->bind('cart_html', $cart_html)
			->bind('paid_with', $paid_with);
	}
}